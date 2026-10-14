<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tracks API activity for both guest and authenticated users.
 *
 * Uses the terminate() method to log AFTER the response is sent,
 * so it does not add latency to API responses.
 *
 * Uses the middleware-validated GuestDevice from $request->_guest_device
 * (set by ValidateGuestHeaders middleware) instead of re-querying the database.
 */
class TrackGuestActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Perform logging after the response has been sent to the client.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $user = auth('sanctum')->user();

            // Use the middleware-validated guest device (set by ValidateGuestHeaders)
            $guestDevice = $request->input('_guest_device');

            // If not authenticated and no validated guest device, skip tracking
            if (!$user && !$guestDevice) {
                return;
            }

            // For authenticated users, use user ID as session identifier
            // For guests, use the validated device's fingerprint
            $sessionId = $user ? 'auth:' . $user->id : $guestDevice->guest_fingerprint;

            // Touch last activity timestamp on the device
            if ($guestDevice) {
                $guestDevice->markActivity();
            }

            // Detect content type and ID from the request path
            $path = $request->path();
            [$contentType, $contentId] = $this->parseContentInfo($path);

            // Determine if the content was locked (from response status code)
            $wasLocked = $response->getStatusCode() === 403;

            DB::table('guest_activity_logs')->insert([
                'guest_device_id'        => $guestDevice?->id,
                'session_id'             => $sessionId,
                'ip_address'             => $request->ip(),
                'user_agent'             => substr((string) $request->userAgent(), 0, 500),
                'endpoint'               => substr($path, 0, 255),
                'content_type'           => $contentType,
                'content_id'             => $contentId,
                'was_locked'             => $wasLocked,
                'is_authenticated'       => $user !== null,
                'user_subscription_tier' => $user?->getSubscriptionTier(),
                'user_id'                => $user?->id,
                'created_at'             => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently fail - tracking should never break the API
            report($e);
        }
    }

    /**
     * Parse content type and ID from the API path.
     *
     * @return array{0: string|null, 1: int|null}
     */
    private function parseContentInfo(string $path): array
    {
        // Match: api/learnings/show/{id}
        if (preg_match('#learnings/show/(\d+)#', $path, $m)) {
            return ['learning', (int) $m[1]];
        }

        // Match: api/learnings/summary/{id}
        if (preg_match('#learnings/summary/(\d+)#', $path, $m)) {
            return ['learning', (int) $m[1]];
        }

        // Match: api/learnings/{category} listing endpoints
        if (preg_match('#learnings/(lessons|core-theory|advanced-insights|case-studies|fnh-architecture)#', $path)) {
            return ['learning', null];
        }

        // Match: api/learnings (all categories)
        if (preg_match('#api/learnings$#', $path)) {
            return ['learning', null];
        }

        // Match: api/ear-training/stages/{stageId}/levels
        if (preg_match('#ear-training/stages/(\d+)/levels#', $path, $m)) {
            return ['ear_training', (int) $m[1]];
        }

        // Match: api/ear-training/stages
        if (preg_match('#ear-training/stages#', $path)) {
            return ['ear_training', null];
        }

        // Match: api/quiz/by-learning/{learning_id}
        if (preg_match('#quiz/by-learning/(\d+)#', $path, $m)) {
            return ['quiz', (int) $m[1]];
        }

        // Match: api/quiz/check-answer
        if (preg_match('#quiz/check-answer#', $path)) {
            return ['quiz', null];
        }

        // Match: api/ear-training/submit/{quizId}
        if (preg_match('#ear-training/submit/(\d+)#', $path, $m)) {
            return ['ear_training', (int) $m[1]];
        }

        // Match: api/quiz/review/{attempt_id}
        if (preg_match('#quiz/review/(\d+)#', $path, $m)) {
            return ['quiz', (int) $m[1]];
        }

        // Match: api/quiz/review-by-learning/{learning_id}
        if (preg_match('#quiz/review-by-learning/(\d+)#', $path, $m)) {
            return ['quiz', (int) $m[1]];
        }

        // Match: api/quiz/attempt-details/{id}
        if (preg_match('#quiz/attempt-details/(\d+)#', $path, $m)) {
            return ['quiz', (int) $m[1]];
        }

        // Match: api/quiz/retry/{learning_id}
        if (preg_match('#quiz/retry/(\d+)#', $path, $m)) {
            return ['quiz', (int) $m[1]];
        }

        // Match: api/quiz/submit/{learning_id}
        if (preg_match('#quiz/submit/(\d+)#', $path, $m)) {
            return ['quiz', (int) $m[1]];
        }

        return [null, null];
    }
}
