<?php

namespace App\Services;

use App\Models\User;

/**
 * Service layer for subscription management.
 *
 * Provides subscription status retrieval for Paddle and Founder integrations.
 */
class SubscriptionService
{
    /**
     * Retrieve the current subscription status for a user.
     *
     * @param  User  $user
     * @return array
     */
    public function getStatus($user): array
    {
        return [
            'subscription_tier' => $user->getSubscriptionTier(),
            'is_subscribed' => $user->hasActiveSubscription(),
            'plan_name' => $user->plan_name,
            'next_billing_date' => $user->next_billing_date?->toIso8601String(),
            'is_founder' => $user->is_founder,
            'founder_trial_ends_at' => $user->founder_trial_ends_at?->toIso8601String(),
        ];
    }
}
