<?php

namespace Tests\Feature\Subscription;

use App\Models\User;
use App\Listeners\PaddleWebhookListener;
use App\Mail\FounderConversionWelcomeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Paddle\Events\WebhookReceived;
use Tests\TestCase;

class PaddleWebhookListenerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_subscription_created_updates_user_tier_and_status(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_12345',
            'subscription_tier' => 'tier_1',
            'is_subscribed' => false,
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'subscription.created',
            'data' => [
                'customer_id' => 'ctm_12345',
                'status' => 'active',
                'custom_data' => ['user_id' => $user->id],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertTrue($user->is_subscribed);
        $this->assertEquals('tier_2', $user->subscription_tier);
        $this->assertEquals('Tier 2', $user->plan_name);
    }

    public function test_subscription_created_extracts_next_billing_date_from_payload(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_bill01',
            'subscription_tier' => 'tier_1',
            'is_subscribed' => false,
        ]);

        $nextBilled = '2026-10-24T00:00:00Z';
        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'subscription.created',
            'data' => [
                'customer_id' => 'ctm_bill01',
                'status' => 'active',
                'next_billed_at' => $nextBilled,
                'custom_data' => ['user_id' => $user->id],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertTrue($user->is_subscribed);
        $this->assertNotNull($user->next_billing_date);
        $this->assertEquals('2026-10-24', $user->next_billing_date->format('Y-m-d'));
    }

    public function test_subscription_created_extracts_plan_name_from_price_items(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_plan01',
            'subscription_tier' => 'tier_1',
            'is_subscribed' => false,
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'subscription.created',
            'data' => [
                'customer_id' => 'ctm_plan01',
                'status' => 'active',
                'custom_data' => ['user_id' => $user->id],
                'items' => [
                    ['price' => ['id' => 'pri_abc', 'name' => 'Tier 2 Annual']],
                ],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertEquals('Tier 2 Annual', $user->plan_name);
    }

    public function test_subscription_activated_event_also_upgrades_user(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_activated01',
            'subscription_tier' => 'tier_1',
            'is_subscribed' => false,
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'subscription.activated',
            'data' => [
                'customer_id' => 'ctm_activated01',
                'status' => 'active',
                'custom_data' => ['user_id' => $user->id],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertTrue($user->is_subscribed);
        $this->assertEquals('tier_2', $user->subscription_tier);
    }

    public function test_subscription_updated_syncs_next_billing_date_and_plan_name(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_upd01',
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'subscription.updated',
            'data' => [
                'customer_id' => 'ctm_upd01',
                'status' => 'active',
                'next_billed_at' => '2026-11-24T00:00:00Z',
                'custom_data' => ['user_id' => $user->id],
                'items' => [
                    ['price' => ['id' => 'pri_abc', 'name' => 'Tier 2 Standard']],
                ],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertTrue($user->is_subscribed);
        $this->assertEquals('tier_3', $user->subscription_tier);
        $this->assertEquals('2026-11-24', $user->next_billing_date->format('Y-m-d'));
        $this->assertEquals('Tier 2 Standard', $user->plan_name);
    }

    public function test_subscription_updated_clears_billing_on_pause(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_paused01',
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
            'plan_name' => 'Tier 2 Annual',
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'subscription.updated',
            'data' => [
                'customer_id' => 'ctm_paused01',
                'status' => 'paused',
                'custom_data' => ['user_id' => $user->id],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertFalse($user->is_subscribed);
        $this->assertEquals('tier_1', $user->subscription_tier);
        $this->assertNull($user->plan_name);
        $this->assertNull($user->next_billing_date);
    }

    public function test_subscription_canceled_resets_user_to_tier_1(): void
    {
        $convertedAt = now()->subDays(10);
        $user = User::factory()->create([
            'paddle_id' => 'ctm_12345',
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
            'is_founder' => true,
            'founder_converted_at' => $convertedAt,
            'founder_checkout_link' => 'https://buy.paddle.com/some-link',
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'subscription.canceled',
            'data' => [
                'customer_id' => 'ctm_12345',
                'custom_data' => ['user_id' => $user->id],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertFalse($user->is_subscribed);
        $this->assertEquals('tier_1', $user->subscription_tier);
        $this->assertNull($user->founder_checkout_link);
        $this->assertNull($user->plan_name);
        $this->assertNull($user->next_billing_date);
        // Permanent record preserved
        $this->assertNotNull($user->founder_converted_at);
    }

    public function test_transaction_completed_converts_founder(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_99999',
            'is_founder' => true,
            'founder_converted_at' => null,
            'subscription_tier' => 'tier_2',
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'transaction.completed',
            'data' => [
                'customer_id' => 'ctm_99999',
                'custom_data' => [
                    'user_id' => $user->id,
                    'is_founder_offer' => true,
                ],
            ],
        ]);

        $listener->handle($event);

        $user->refresh();
        $this->assertNotNull($user->founder_converted_at);
        $this->assertEquals('tier_3', $user->subscription_tier);
        $this->assertTrue($user->is_subscribed);
        $this->assertEquals('Tier 2 Founder', $user->plan_name);
        // Lifetime deal — no recurring billing date
        $this->assertNull($user->next_billing_date);

        Mail::assertSent(FounderConversionWelcomeEmail::class, 1);
    }

    public function test_transaction_completed_idempotent_does_not_resend_email(): void
    {
        $user = User::factory()->create([
            'paddle_id' => 'ctm_already_converted',
            'is_founder' => true,
            'founder_converted_at' => now()->subDays(5),
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
        ]);

        $listener = app(PaddleWebhookListener::class);
        $event = new WebhookReceived([
            'event_type' => 'transaction.completed',
            'data' => [
                'customer_id' => 'ctm_already_converted',
                'custom_data' => [
                    'user_id' => $user->id,
                    'is_founder_offer' => true,
                ],
            ],
        ]);

        $listener->handle($event);

        // Email must NOT be re-sent for already-converted founder (idempotency)
        Mail::assertNotSent(FounderConversionWelcomeEmail::class);
    }
}
