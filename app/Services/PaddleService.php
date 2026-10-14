<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaddleService
{
    /**
     * Generate a unique per-founder custom checkout link at £24.99 via Paddle API.
     * Uses the Tier 2 Founder price ID (configured in config/paddle.php).
     */
    public function generateFounderCheckoutLink(User $user): string
    {
        // NOTE: Update the Price ID when provided by the client
        // This is a placeholder price ID for £24.99 Founder offer
        $priceId = config('cashier.founder_price_id');

        try {
            // Cashier provides checkout link generation
            // It allows creating a checkout session
            $checkout = $user->checkout($priceId)
                ->customData([
                    'is_founder_offer' => true,
                    'user_id' => $user->id,
                ])
                ->returnTo(route('subscription.success'));

            return $checkout->url();
        } catch (\Exception $e) {
            Log::error('Paddle checkout link generation failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Map Paddle subscription status string to internal tier string.
     *
     * @param string $status Paddle status (active, trialing, paused, canceled, past_due)
     * @return string (tier_1, tier_2, tier_3)
     */
    public function mapStatusToTier(string $status): string
    {
        switch ($status) {
            case 'active':
            case 'trialing':
            case 'past_due': // Grace period
                return 'tier_2'; // Assuming default tier_2 for active paddle sub unless logic dictates otherwise
            case 'paused':
            case 'canceled':
            default:
                return 'tier_1';
        }
    }
    /**
     * Generate a Paddle checkout link for any subscription price or product.
     * Defaults to founder price if not specified.
     */
    public function generateCheckoutLink(User $user, ?string $priceId = null, array $customData = []): string
    {
        $priceId = $priceId ?: config('cashier.founder_price_id');

        try {
            $checkout = $user->checkout($priceId)
                ->customData(array_merge([
                    'user_id' => $user->id,
                ], $customData))
                ->returnTo(route('subscription.success'));

            return $checkout->url();
        } catch (\Exception $e) {
            Log::error('Paddle checkout link generation failed: ' . $e->getMessage());
            return '';
        }
    }

}
