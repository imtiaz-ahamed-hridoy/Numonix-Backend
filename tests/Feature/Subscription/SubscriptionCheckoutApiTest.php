<?php

namespace Tests\Feature\Subscription;

use App\Models\User;
use App\Services\PaddleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SubscriptionCheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_checkout_endpoint(): void
    {
        $response = $this->getJson('/api/subscription/checkout');
        $response->assertStatus(401);

        $responsePost = $this->postJson('/api/subscription/checkout');
        $responsePost->assertStatus(401);
    }

    public function test_authenticated_user_receives_checkout_url(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'tier_1',
            'is_subscribed'     => false,
            'is_founder'        => false,
        ]);

        $mockPaddle = Mockery::mock(PaddleService::class);
        $mockPaddle->shouldReceive('generateCheckoutLink')
            ->once()
            ->andReturn('https://buy.paddle.com/checkout/test-session-123');

        $this->app->instance(PaddleService::class, $mockPaddle);

        $response = $this->actingAs($user)->postJson('/api/subscription/checkout');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'checkout_url'     => 'https://buy.paddle.com/checkout/test-session-123',
                'is_founder_offer' => false,
            ],
        ]);
    }

    public function test_active_founder_gets_founder_checkout_offer(): void
    {
        $user = User::factory()->create([
            'subscription_tier'     => 'tier_2',
            'is_subscribed'         => true,
            'is_founder'            => true,
            'founder_trial_ends_at' => Carbon::now()->addDays(15),
            'founder_checkout_link' => null,
        ]);

        $mockPaddle = Mockery::mock(PaddleService::class);
        $mockPaddle->shouldReceive('generateCheckoutLink')
            ->once()
            ->andReturn('https://buy.paddle.com/checkout/founder-offer-456');

        $this->app->instance(PaddleService::class, $mockPaddle);

        $response = $this->actingAs($user)->getJson('/api/subscription/checkout');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'checkout_url'     => 'https://buy.paddle.com/checkout/founder-offer-456',
                'is_founder_offer' => true,
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'id'                    => $user->id,
            'founder_checkout_link' => 'https://buy.paddle.com/checkout/founder-offer-456',
        ]);
    }

    public function test_fallback_membership_url_when_paddle_api_returns_empty(): void
    {
        $user = User::factory()->create();

        $mockPaddle = Mockery::mock(PaddleService::class);
        $mockPaddle->shouldReceive('generateCheckoutLink')
            ->once()
            ->andReturn('');

        $this->app->instance(PaddleService::class, $mockPaddle);

        $response = $this->actingAs($user)->postJson('/api/subscription/checkout');

        $response->assertStatus(200);
        $this->assertEquals('https://example.com/membership', $response->json('data.checkout_url'));
    }
}
