<?php

namespace Tests\Feature\Subscription;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionStatusApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_subscription_status(): void
    {
        $response = $this->getJson('/api/subscription/status');

        $response->assertStatus(401);
    }

    public function test_free_user_tier1_subscription_status(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'tier_1',
            'is_subscribed'     => false,
            'is_founder'        => false,
            'plan_name'         => null,
            'next_billing_date' => null,
        ]);

        $response = $this->actingAs($user)->getJson('/api/subscription/status');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'subscription_tier' => 'tier_1',
                'is_subscribed'     => false,
                'is_founder'        => false,
                'plan_name'         => null,
                'next_billing_date' => null,
            ],
        ]);
    }

    public function test_active_founder_trial_subscription_status(): void
    {
        $trialEnd = Carbon::now()->addDays(20);
        $user = User::factory()->create([
            'subscription_tier'    => 'tier_3',
            'is_subscribed'        => true,
            'is_founder'           => true,
            'founder_trial_ends_at'=> $trialEnd,
            'plan_name'            => 'Founder Trial',
            'next_billing_date'    => null,
        ]);

        $response = $this->actingAs($user)->getJson('/api/subscription/status');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'subscription_tier'    => 'tier_3',
                'is_subscribed'        => true,
                'is_founder'           => true,
                'plan_name'            => 'Founder Trial',
                'founder_trial_ends_at'=> $trialEnd->toIso8601String(),
                'next_billing_date'    => null,
            ],
        ]);
    }

    public function test_expired_founder_trial_drops_to_tier1(): void
    {
        // Trial ended 5 days ago — user should now be tier_1 (spec 3.15)
        $trialEnd = Carbon::now()->subDays(5);
        $user = User::factory()->create([
            'subscription_tier'    => 'tier_2',
            'is_subscribed'        => false,
            'is_founder'           => true,
            'founder_trial_ends_at'=> $trialEnd,
            'founder_converted_at' => null,
            'plan_name'            => null,
        ]);

        $response = $this->actingAs($user)->getJson('/api/subscription/status');

        $response->assertStatus(200);
        // getSubscriptionTier() returns tier_1 when founder trial is expired & not converted
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'subscription_tier' => 'tier_1',
                'is_subscribed'     => false,
                'is_founder'        => true,
            ],
        ]);
    }

    public function test_standard_tier2_subscriber_status(): void
    {
        $nextBill = Carbon::now()->addMonth();
        $user = User::factory()->create([
            'subscription_tier' => 'tier_2',
            'is_subscribed'     => true,
            'is_founder'        => false,
            'plan_name'         => 'Tier 2 Annual',
            'next_billing_date' => $nextBill,
        ]);

        $response = $this->actingAs($user)->getJson('/api/subscription/status');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'subscription_tier' => 'tier_2',
                'is_subscribed'     => true,
                'is_founder'        => false,
                'plan_name'         => 'Tier 2 Annual',
            ],
        ]);
        // Ensure next_billing_date is present and ISO8601 formatted
        $responseData = $response->json('data');
        $this->assertNotNull($responseData['next_billing_date']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $responseData['next_billing_date']);
    }

    public function test_converted_founder_permanent_paid_is_tier2(): void
    {
        // Founder paid the £24.99 lifetime deal — permanently tier_3 (Marketing Tier 2),
        // even though founder_trial_ends_at is in the past.
        $user = User::factory()->create([
            'subscription_tier'    => 'tier_3',
            'is_subscribed'        => true,
            'is_founder'           => true,
            'founder_trial_ends_at'=> Carbon::now()->subDays(10), // trial expired
            'founder_converted_at' => Carbon::now()->subDays(5),  // but they paid
            'plan_name'            => 'Tier 2 Founder',
            'next_billing_date'    => null, // lifetime — no recurring billing
        ]);

        $response = $this->actingAs($user)->getJson('/api/subscription/status');

        $response->assertStatus(200);
        // hasActiveSubscription() returns true because founder_converted_at is set
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'subscription_tier' => 'tier_3',
                'is_subscribed'     => true,
                'is_founder'        => true,
                'plan_name'         => 'Tier 2 Founder',
                'next_billing_date' => null,
            ],
        ]);
    }

    public function test_null_plan_name_fallback_is_null_not_empty_string(): void
    {
        // Ensures plan_name: null in the JSON (not "" or missing)
        // Flutter's displayPlanName getter must handle null gracefully.
        $user = User::factory()->create([
            'subscription_tier' => 'tier_1',
            'is_subscribed'     => false,
            'is_founder'        => false,
            'plan_name'         => null,
        ]);

        $response = $this->actingAs($user)->getJson('/api/subscription/status');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertArrayHasKey('plan_name', $responseData);
        $this->assertNull($responseData['plan_name']);
    }
}
