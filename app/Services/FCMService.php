<?php

namespace App\Services;

use App\Models\FirebaseToken;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;

/**
 * Service for sending Firebase Cloud Messaging push notifications.
 *
 * Provides batch multicast sending, single message sending,
 * and automatic pruning of stale or invalid device tokens.
 */
class FCMService
{
    /**
     * Send a push notification to a single device token.
     * Automatically prunes token if reported invalid or not found.
     *
     * @param  string  $token  Firebase device token
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body text
     * @param  array  $data  Optional custom data payload
     */
    public function sendMessage(string $token, string $title, string $body, array $data = []): bool
    {
        $firebaseCreds = FirebaseCredentialResolver::resolve();
        if (empty($firebaseCreds) || !file_exists($firebaseCreds)) {
            Log::warning("FCM notification skipped: Firebase service account credentials not configured or file not found.");
            return false;
        }

        $formattedData = collect($data)
            ->mapWithKeys(fn ($value, $key) => [(string) $key => is_scalar($value) ? (string) $value : json_encode($value)])
            ->merge(['click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
            ->all();

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $formattedData,
        ];

        try {
            Firebase::messaging()->send($message);
            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Log::warning("FCM send failure for token [{$token}]: {$msg}");

            if (str_contains($msg, 'NotFound') || 
                str_contains($msg, 'Unregistered') || 
                str_contains($msg, 'InvalidArgument') || 
                str_contains($msg, 'not a valid FCM registration token') ||
                str_contains($msg, 'registration-token-not-registered')) {
                $this->pruneTokens([$token]);
            }

            return false;
        }
    }

    /**
     * Send a push notification to multiple device tokens in batches of up to 500.
     * Automatically prunes unregistered or invalid tokens.
     *
     * @param  array  $tokens  Array of FCM device tokens
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body text
     * @param  array  $data  Optional custom data payload
     * @return array{sent: int, failed: int, pruned: int}
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return ['sent' => 0, 'failed' => 0, 'pruned' => 0];
        }

        $firebaseCreds = FirebaseCredentialResolver::resolve();
        if (empty($firebaseCreds) || !file_exists($firebaseCreds)) {
            Log::warning("FCM multicast skipped: Firebase credentials not configured.");
            return ['sent' => 0, 'failed' => count($tokens), 'pruned' => 0];
        }

        $formattedData = collect($data)
            ->mapWithKeys(fn ($value, $key) => [(string) $key => is_scalar($value) ? (string) $value : json_encode($value)])
            ->merge(['click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
            ->all();

        $totalSent = 0;
        $totalFailed = 0;
        $totalPruned = 0;

        $chunks = array_chunk($tokens, 500);

        foreach ($chunks as $chunk) {
            try {
                $cloudMessage = CloudMessage::new()
                    ->withNotification(Notification::create($title, $body))
                    ->withData($formattedData);

                $report = Firebase::messaging()->sendMulticast($cloudMessage, $chunk);
                $totalSent += $report->successes()->count();
                $totalFailed += $report->failures()->count();

                $deadTokens = [];
                if ($report->hasFailures()) {
                    foreach ($report->failures()->getItems() as $failure) {
                        $error = $failure->error();
                        $targetToken = $failure->target()->value();
                        if ($error) {
                            $errorMsg = $error->getMessage();
                            if (str_contains($errorMsg, 'NotFound') ||
                                str_contains($errorMsg, 'Unregistered') ||
                                str_contains($errorMsg, 'registration-token-not-registered') ||
                                str_contains($errorMsg, 'InvalidArgument')) {
                                $deadTokens[] = $targetToken;
                            }
                        }
                    }
                }

                if (!empty($deadTokens)) {
                    $this->pruneTokens($deadTokens);
                    $totalPruned += count($deadTokens);
                }
            } catch (\Throwable $e) {
                Log::error("FCM Multicast error: " . $e->getMessage());
                foreach ($chunk as $singleToken) {
                    $sent = $this->sendMessage($singleToken, $title, $body, $data);
                    if ($sent) {
                        $totalSent++;
                    } else {
                        $totalFailed++;
                    }
                }
            }
        }

        return ['sent' => $totalSent, 'failed' => $totalFailed, 'pruned' => $totalPruned];
    }

    /**
     * Prune invalid or expired tokens from the database.
     */
    protected function pruneTokens(array $tokens): void
    {
        try {
            FirebaseToken::whereIn('token', $tokens)->delete();
            Log::info("Pruned " . count($tokens) . " invalid FCM token(s) from database.");
        } catch (\Throwable $e) {
            Log::warning("Failed to prune invalid FCM tokens: " . $e->getMessage());
        }
    }
}
