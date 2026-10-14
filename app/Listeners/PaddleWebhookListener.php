<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\EmailLog;
use App\Mail\FounderConversionWelcomeEmail;
use App\Services\PaddleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Paddle\Events\WebhookReceived;

class PaddleWebhookListener
{
    protected PaddleService $paddleService;

    public function __construct(PaddleService $paddleService)
    {
        $this->paddleService = $paddleService;
    }

    public function handle(WebhookReceived $event)
    {
        $payload = $event->payload;
        $eventType = $payload['event_type'] ?? null;

        Log::info("Paddle Webhook Received: {$eventType}", ['payload' => $payload]);

        if (!$eventType) return;

        switch ($eventType) {
            case 'subscription.created':
            case 'subscription.activated':
                $this->handleSubscriptionCreated($payload);
                break;
            case 'subscription.updated':
                $this->handleSubscriptionUpdated($payload);
                break;
            case 'subscription.canceled':
                $this->handleSubscriptionCancelled($payload);
                break;
            case 'subscription.past_due':
                $this->handleSubscriptionPastDue($payload);
                break;
            case 'transaction.completed':
                $this->handleTransactionCompleted($payload);
                break;
        }
    }

    /**
     * Extract next_billing_date from Paddle payload.
     * Paddle Billing sends this under data.next_billed_at (ISO8601 string).
     */
    protected function extractNextBillingDate(array $data): ?Carbon
    {
        $raw = $data['next_billed_at']
            ?? $data['current_billing_period']['ends_at']
            ?? $data['scheduled_change']['effective_at']
            ?? null;

        if ($raw) {
            try {
                return Carbon::parse($raw);
            } catch (\Exception $e) {
                Log::warning("Could not parse next_billing_date: {$raw}");
            }
        }

        return null;
    }

    /**
     * Extract plan name from Paddle subscription payload items.
     * Falls back to 'Tier 2' if not determinable.
     */
    protected function extractPlanName(array $data): string
    {
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $name = $item['price']['name']
                ?? $item['price']['description']
                ?? null;
            if ($name) {
                return $name;
            }
        }
        return 'Tier 2';
    }

        /**
     * Extract resolved subscription tier (tier_1, tier_2, tier_3) from Paddle payload.
     */
    protected function extractTier(array $data): string
    {
        $status = $data['status'] ?? 'active';
        if (in_array($status, ['canceled', 'paused'])) {
            return 'tier_1';
        }

        $items = $data['items'] ?? [];
        $tier3Price = config('cashier.tier_3_price_id');
        $founderPrice = config('cashier.founder_price_id');
        foreach ($items as $item) {
            $priceId = $item['price']['id'] ?? '';
            $name = strtolower($item['price']['name'] ?? $item['price']['description'] ?? '');
            if (($tier3Price && $priceId === $tier3Price)
                || ($founderPrice && $priceId === $founderPrice)
                || str_contains($name, 'tier 2')
                || str_contains($name, 'tier_2')
                || str_contains($name, 'tier 3')
                || str_contains($name, 'tier_3')
                || str_contains($name, 'founder')
                || str_contains($name, 'pro')) {
                return 'tier_3';
            }
            if (str_contains($name, 'tier 1') || str_contains($name, 'tier_1')) {
                return 'tier_2';
            }
        }

        return 'tier_2';
    }

    protected function handleSubscriptionCreated(array $payload)
    {
        $data = $payload['data'] ?? [];
        $customerId = $data['customer_id'] ?? null;
        $customData = $data['custom_data'] ?? [];
        $userId = $customData['user_id'] ?? null;

        $user = null;
        if ($customerId) {
            $user = User::where('paddle_id', $customerId)->first();
        }
        if (!$user && $userId) {
            $user = User::find($userId);
            if ($user && $customerId) {
                $user->paddle_id = $customerId;
            }
        }

        if ($user) {
            $nextBillingDate = $this->extractNextBillingDate($data);
            $planName = $this->extractPlanName($data);

            $user->is_subscribed = true;
            $user->subscription_tier = $this->extractTier($data);
            $user->plan_name = $planName;
            if ($nextBillingDate) {
                $user->next_billing_date = $nextBillingDate;
            }
            $user->save();
        }
    }

    protected function handleSubscriptionUpdated(array $payload)
    {
        $data = $payload['data'] ?? [];
        $customerId = $data['customer_id'] ?? null;
        $status = $data['status'] ?? null;
        $customData = $data['custom_data'] ?? [];
        $userId = $customData['user_id'] ?? null;

        if (!$status) return;

        $user = null;
        if ($customerId) {
            $user = User::where('paddle_id', $customerId)->first();
        }
        if (!$user && $userId) {
            $user = User::find($userId);
        }

        if ($user) {
            $newTier = in_array($status, ['active', 'trialing', 'past_due']) ? $this->extractTier($data) : 'tier_1';
            $user->subscription_tier = $newTier;

            // Sync is_subscribed flag
            $user->is_subscribed = in_array($status, ['active', 'trialing', 'past_due']);

            // Update billing date and plan name if the subscription is still active
            if ($user->is_subscribed) {
                $nextBillingDate = $this->extractNextBillingDate($data);
                if ($nextBillingDate) {
                    $user->next_billing_date = $nextBillingDate;
                }
                $planName = $this->extractPlanName($data);
                if ($planName) {
                    $user->plan_name = $planName;
                }
            } else {
                // Subscription no longer active - clear billing fields
                $user->next_billing_date = null;
                $user->plan_name = null;
            }

            $user->save();
        }
    }

    protected function handleSubscriptionCancelled(array $payload)
    {
        $data = $payload['data'] ?? [];
        $customerId = $data['customer_id'] ?? null;
        $customData = $data['custom_data'] ?? [];
        $userId = $customData['user_id'] ?? null;

        $user = null;
        if ($customerId) {
            $user = User::where('paddle_id', $customerId)->first();
        }
        if (!$user && $userId) {
            $user = User::find($userId);
        }

        if ($user) {
            $user->is_subscribed = false;
            $user->subscription_tier = 'tier_1';
            $user->plan_name = null;
            $user->next_billing_date = null;
            // Per spec 3.15: Cancellation drops user to Free tier. The £24.99 checkout
            // link is cleared so it cannot be reused. We do NOT clear founder_converted_at
            // - that is a permanent historical record of when the founder paid.
            $user->founder_checkout_link = null;
            $user->save();
        }
    }

    protected function handleSubscriptionPastDue(array $payload)
    {
        $customerId = $payload['data']['customer_id'] ?? null;
        if (!$customerId) return;

        Log::warning("Subscription past due for customer: {$customerId}");
    }

    protected function handleTransactionCompleted(array $payload)
    {
        $data = $payload['data'] ?? [];
        $customerId = $data['customer_id'] ?? null;
        $customData = $data['custom_data'] ?? [];
        $userId = $customData['user_id'] ?? null;

        // Check if custom_data flags it as founder offer, or if price id matches
        $isFounderOffer = !empty($customData['is_founder_offer']);

        if (!$isFounderOffer) {
            $items = $data['items'] ?? [];
            $founderPriceId = config('cashier.founder_price_id');
            foreach ($items as $item) {
                if (isset($item['price']['id']) && $item['price']['id'] === $founderPriceId) {
                    $isFounderOffer = true;
                    break;
                }
            }
        }

        $user = null;
        if ($customerId) {
            $user = User::where('paddle_id', $customerId)->first();
        }
        if (!$user && $userId) {
            $user = User::find($userId);
            if ($user && $customerId) {
                $user->paddle_id = $customerId;
            }
        }

        if ($user && $isFounderOffer) {
            $alreadyConverted = $user->founder_converted_at !== null;

            $user->founder_converted_at = $user->founder_converted_at ?: now();
            $user->subscription_tier = 'tier_3';
            $user->is_subscribed = true;
            $user->plan_name = 'Tier 2 Founder';
            // Founder lifetime deal - no recurring billing date
            $user->next_billing_date = null;
            $user->save();

            // Send founder conversion welcome email only once (idempotent)
            if (!$alreadyConverted) {
                try {
                    $mailable = new FounderConversionWelcomeEmail($user);
                    Mail::to($user->email)->send($mailable);

                    EmailLog::create([
                        'user_id' => $user->id,
                        'type' => 'founder_conversion_welcome',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'subject' => 'Welcome to the NumoniX Founder\'s Club!',
                        'body' => $mailable->render(),
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed sending FounderConversionWelcomeEmail to {$user->email}: " . $e->getMessage());
                }
            }
        }
    }
}
