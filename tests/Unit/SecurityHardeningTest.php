<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\PaddleService;
use Carbon\Carbon;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    /**
     * Test that critical privileged fields cannot be mass assigned.
     */
    public function test_privileged_fields_cannot_be_mass_assigned(): void
    {
        $user = new User([
            'name' => 'Alice Attacker',
            'email' => 'alice@example.com',
            'role' => 'Admin',
            'subscription_tier' => 'tier_3',
            'is_founder' => true,
            'is_subscribed' => true,
        ]);

        $this->assertEquals('Alice Attacker', $user->name);
        $this->assertEquals('alice@example.com', $user->email);

        // Privileged fields MUST NOT be populated via mass assignment
        $this->assertNull($user->role);
        $this->assertNull($user->subscription_tier);
        $this->assertFalse((bool) $user->is_founder);
        $this->assertFalse((bool) $user->is_subscribed);
    }

    /**
     * Test founder rolling activation logic (spec 7.4).
     */
    public function test_founder_rolling_activation_lifecycle(): void
    {
        $user = new User();
        $user->name = 'Founder Bob';
        $user->email = 'bob@example.com';
        $user->is_founder = true;
        $user->imported_at = Carbon::now();
        $user->activated_at = null;
        $user->founder_trial_ends_at = null;
        $user->activation_token = 'single-use-token-123';

        // Before activation: trial is NOT active
        $this->assertFalse($user->isFounderTrialActive());
        $this->assertEquals('tier_1', $user->getSubscriptionTier());

        // Upon activation:
        $user->activated_at = Carbon::now();
        $user->founder_trial_ends_at = Carbon::now()->addDays(30);
        $user->subscription_tier = 'tier_3';
        $user->is_subscribed = true;
        $user->activation_token = null;

        $this->assertTrue($user->isFounderTrialActive());
        $this->assertEquals('tier_3', $user->getSubscriptionTier());
        $this->assertTrue($user->hasActiveSubscription());
    }

    /**
     * Test Paddle subscription status mapping drops canceled/paused users to Free (tier_1).
     */
    public function test_paddle_service_drops_canceled_subscriptions_to_free_tier(): void
    {
        $paddleService = new PaddleService();

        $this->assertEquals('tier_2', $paddleService->mapStatusToTier('active'));
        $this->assertEquals('tier_2', $paddleService->mapStatusToTier('trialing'));
        $this->assertEquals('tier_1', $paddleService->mapStatusToTier('canceled'));
        $this->assertEquals('tier_1', $paddleService->mapStatusToTier('paused'));
        $this->assertEquals('tier_1', $paddleService->mapStatusToTier('unknown'));
    }
}