<?php

namespace Tests\Feature\Founder;

use App\Models\User;
use App\Mail\FounderDay25ReminderEmail;
use App\Mail\FounderCheckoutEmail;
use App\Services\PaddleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class ProcessFounderTrialsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_day_25_reminder_is_sent_to_founders_within_5_days_of_expiry(): void
    {
        $user = User::factory()->create([
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->addDays(3),
            'founder_day25_sent_at' => null,
            'founder_converted_at' => null,
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
        ]);

        $this->artisan('founders:process-trials');

        $user->refresh();
        $this->assertNotNull($user->founder_day25_sent_at);
        Mail::assertSent(FounderDay25ReminderEmail::class, 1);
    }

    public function test_day_30_checkout_email_is_sent_with_paddle_link_when_trial_ends(): void
    {
        $mockPaddle = Mockery::mock(PaddleService::class);
        $mockPaddle->shouldReceive('generateFounderCheckoutLink')
            ->once()
            ->andReturn('https://buy.paddle.com/test-link');
        $this->app->instance(PaddleService::class, $mockPaddle);

        $user = User::factory()->create([
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->subMinute(),
            'founder_day25_sent_at' => Carbon::now()->subDays(5),
            'founder_day30_sent_at' => null,
            'founder_converted_at' => null,
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
        ]);

        $this->artisan('founders:process-trials');

        $user->refresh();
        $this->assertNotNull($user->founder_day30_sent_at);
        $this->assertEquals('https://buy.paddle.com/test-link', $user->founder_checkout_link);
        Mail::assertSent(FounderCheckoutEmail::class, 1);
    }

    public function test_expired_unconverted_founder_is_downgraded_after_grace_period(): void
    {
        $user = User::factory()->create([
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->subDays(2), // 2 days past trial (past 1-day grace)
            'founder_converted_at' => null,
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
        ]);

        $this->artisan('founders:process-trials');

        $user->refresh();
        $this->assertEquals('tier_1', $user->subscription_tier);
        $this->assertFalse($user->is_subscribed);
    }

    public function test_converted_founder_is_not_downgraded(): void
    {
        $user = User::factory()->create([
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->subDays(5),
            'founder_converted_at' => Carbon::now()->subDays(2),
            'subscription_tier' => 'tier_2',
            'is_subscribed' => true,
        ]);

        $this->artisan('founders:process-trials');

        $user->refresh();
        $this->assertEquals('tier_2', $user->subscription_tier);
        $this->assertTrue($user->is_subscribed);
    }
}
