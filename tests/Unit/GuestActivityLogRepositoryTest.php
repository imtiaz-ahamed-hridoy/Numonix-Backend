<?php

namespace Tests\Unit;

use App\Models\GuestActivityLog;
use App\Models\Learning;
use App\Repositories\GuestActivityLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GuestActivityLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GuestActivityLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(GuestActivityLogRepository::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    /** @test */
    public function it_counts_distinct_guest_sessions_since_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00'));

        // Create guest activity logs — 3 distinct sessions within range, 1 outside
        GuestActivityLog::factory()->create([
            'session_id' => 'session_a',
            'is_authenticated' => 0,
            'created_at' => now()->subDays(2),
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'session_a',
            'is_authenticated' => 0,
            'created_at' => now()->subDays(1),
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'session_b',
            'is_authenticated' => 0,
            'created_at' => now()->subDays(1),
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'session_c',
            'is_authenticated' => 0,
            'created_at' => now()->subDays(3),
        ]);
        // Authenticated session — should be excluded
        GuestActivityLog::factory()->create([
            'session_id' => 'session_auth',
            'is_authenticated' => 1,
            'created_at' => now()->subDays(1),
        ]);
        // Outside range
        GuestActivityLog::factory()->create([
            'session_id' => 'session_old',
            'is_authenticated' => 0,
            'created_at' => now()->subDays(40),
        ]);

        $count = $this->repository->countDistinctGuestSessionsSince(now()->subDays(30));

        // Only session_a, session_b, session_c are guest sessions within range
        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_returns_zero_when_no_guest_sessions(): void
    {
        $count = $this->repository->countDistinctGuestSessionsSince(now()->subDays(30));

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_returns_daily_sessions_aggregation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00'));

        // Day 1: 2 guest sessions, 1 auth session
        GuestActivityLog::factory()->create([
            'session_id' => 'g1', 'is_authenticated' => 0,
            'created_at' => now()->subDays(1)->startOfDay(),
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'g2', 'is_authenticated' => 0,
            'created_at' => now()->subDays(1)->startOfDay(),
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'a1', 'is_authenticated' => 1,
            'created_at' => now()->subDays(1)->startOfDay(),
        ]);

        // Day 2: 1 guest session
        GuestActivityLog::factory()->create([
            'session_id' => 'g3', 'is_authenticated' => 0,
            'created_at' => now()->startOfDay(),
        ]);

        $result = $this->repository->getDailySessionsSince(now()->subDays(30));

        $this->assertCount(2, $result);

        $day1 = now()->subDays(1)->startOfDay()->format('Y-m-d');
        $day2 = now()->startOfDay()->format('Y-m-d');

        $this->assertEquals(2, (int) $result[$day1]->guest_sessions);
        $this->assertEquals(1, (int) $result[$day1]->auth_sessions);
        $this->assertEquals(3, (int) $result[$day1]->total_sessions);
        $this->assertEquals(1, (int) $result[$day2]->guest_sessions);
    }

    /** @test */
    public function it_returns_empty_daily_sessions_when_no_data(): void
    {
        $result = $this->repository->getDailySessionsSince(now()->subDays(7));

        $this->assertCount(0, $result);
    }

    /** @test */
    public function it_returns_content_engagement_since_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00'));

        $learning = Learning::create([
            'title' => 'Test Learning',
            'slug' => 'test-' . uniqid(),
            'category' => 'lesson',
            'is_published' => true,
            'status' => 'active',
        ]);

        // 3 views of content A (2 guest, 1 auth, 1 locked)
        GuestActivityLog::factory()->count(2)->create([
            'content_type' => 'learning',
            'content_id' => (string) $learning->id,
            'is_authenticated' => 0,
            'was_locked' => 0,
            'session_id' => 'g1',
            'created_at' => now()->subDay(),
        ]);
        GuestActivityLog::factory()->create([
            'content_type' => 'learning',
            'content_id' => (string) $learning->id,
            'is_authenticated' => 1,
            'was_locked' => 1,
            'session_id' => 'a1',
            'created_at' => now()->subDay(),
        ]);

        // 1 view of content B
        GuestActivityLog::factory()->create([
            'content_type' => 'learning',
            'content_id' => '999',
            'is_authenticated' => 0,
            'was_locked' => 0,
            'session_id' => 'g2',
            'created_at' => now()->subDay(),
        ]);

        // Record outside range — should be excluded
        GuestActivityLog::factory()->create([
            'content_type' => 'learning',
            'content_id' => '888',
            'is_authenticated' => 0,
            'was_locked' => 0,
            'session_id' => 'g3',
            'created_at' => now()->subDays(40),
        ]);

        // Record with null content_type — should be excluded
        GuestActivityLog::factory()->create([
            'content_type' => null,
            'content_id' => null,
            'is_authenticated' => 0,
            'was_locked' => 0,
            'session_id' => 'g4',
            'created_at' => now()->subDay(),
        ]);

        $result = $this->repository->getContentEngagementSince(now()->subDays(30), 15);

        $this->assertCount(2, $result);

        // Content A should have 3 views, 2 unique viewers, 2 guest, 1 auth, 1 locked
        $contentA = collect($result)->firstWhere('content_id', (string) $learning->id);
        $this->assertNotNull($contentA);
        $this->assertEquals(3, (int) $contentA['total_views']);
        $this->assertEquals(2, (int) $contentA['unique_viewers']);
        $this->assertEquals(2, (int) $contentA['guest_views']);
        $this->assertEquals(1, (int) $contentA['auth_views']);
        $this->assertEquals(1, (int) $contentA['locked_hits']);
    }

    /** @test */
    public function it_returns_top_locked_content_since_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00'));

        // Content A: 3 locked attempts (2 unique session)
        GuestActivityLog::factory()->count(2)->create([
            'content_type' => 'learning',
            'content_id' => '123',
            'was_locked' => true,
            'session_id' => 'g1',
            'created_at' => now()->subDay(),
        ]);
        GuestActivityLog::factory()->create([
            'content_type' => 'learning',
            'content_id' => '123',
            'was_locked' => true,
            'session_id' => 'g2',
            'created_at' => now()->subDay(),
        ]);

        // Content B: 1 locked attempt
        GuestActivityLog::factory()->create([
            'content_type' => 'learning',
            'content_id' => '456',
            'was_locked' => true,
            'session_id' => 'g3',
            'created_at' => now()->subDay(),
        ]);

        // Unlocked record — should be excluded
        GuestActivityLog::factory()->create([
            'content_type' => 'learning',
            'content_id' => '789',
            'was_locked' => false,
            'session_id' => 'g4',
            'created_at' => now()->subDay(),
        ]);

        // Outside range
        GuestActivityLog::factory()->create([
            'content_type' => 'learning',
            'content_id' => '999',
            'was_locked' => true,
            'session_id' => 'g5',
            'created_at' => now()->subDays(40),
        ]);

        $result = $this->repository->getTopLockedContentSince(now()->subDays(30), 10);

        $this->assertCount(2, $result);

        $contentA = collect($result)->firstWhere('content_id', '123');
        $contentB = collect($result)->firstWhere('content_id', '456');

        $this->assertNotNull($contentA);
        $this->assertEquals(3, (int) $contentA['locked_attempts']);
        $this->assertEquals(2, (int) $contentA['unique_users']);

        $this->assertNotNull($contentB);
        $this->assertEquals(1, (int) $contentB['locked_attempts']);
    }

    /** @test */
    public function it_returns_activity_by_tier_since_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00'));

        // Guest activity (no subscription tier)
        GuestActivityLog::factory()->count(3)->create([
            'user_subscription_tier' => null,
            'session_id' => 'g1',
            'created_at' => now()->subDay(),
        ]);

        // Tier 1 activity
        GuestActivityLog::factory()->count(2)->create([
            'user_subscription_tier' => 'tier_1',
            'session_id' => 't1',
            'created_at' => now()->subDay(),
        ]);

        // Tier 3 activity
        GuestActivityLog::factory()->create([
            'user_subscription_tier' => 'tier_3',
            'session_id' => 't3',
            'created_at' => now()->subDay(),
        ]);

        // Outside range
        GuestActivityLog::factory()->create([
            'user_subscription_tier' => 'tier_1',
            'session_id' => 'old',
            'created_at' => now()->subDays(40),
        ]);

        $result = $this->repository->getActivityByTierSince(now()->subDays(30));

        $tiers = collect($result)->keyBy('tier');

        $this->assertEquals(3, (int) ($tiers['guest']->total_requests ?? $tiers['guest']['total_requests']));
        $this->assertEquals(2, (int) ($tiers['tier_1']->total_requests ?? $tiers['tier_1']['total_requests']));
        $this->assertEquals(1, (int) ($tiers['tier_3']->total_requests ?? $tiers['tier_3']['total_requests']));
    }

    /** @test */
    public function it_returns_overview_stats(): void
    {
        // Use a Wednesday so $weekStart (Mon) is clearly before $today (Wed)
        // ensuring the "this week" record falls between $weekStart and $today.
        Carbon::setTestNow(Carbon::parse('2026-07-15 12:00:00'));

        $today = now()->startOfDay();      // Wed July 15
        $weekStart = now()->startOfWeek(); // Mon July 13
        $monthStart = now()->startOfMonth(); // Wed July 1

        // Today: 2 guest sessions (3 requests), 1 auth session
        GuestActivityLog::factory()->create([
            'session_id' => 'g_today_1', 'is_authenticated' => 0, 'was_locked' => 0,
            'created_at' => $today,
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'g_today_1', 'is_authenticated' => 0, 'was_locked' => 1,
            'created_at' => $today,
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'g_today_2', 'is_authenticated' => 0, 'was_locked' => 0,
            'created_at' => $today,
        ]);
        GuestActivityLog::factory()->create([
            'session_id' => 'a_today', 'is_authenticated' => 1, 'was_locked' => 0,
            'created_at' => $today,
        ]);

        // This week but before today: 1 guest session (Tue July 14)
        GuestActivityLog::factory()->create([
            'session_id' => 'g_week', 'is_authenticated' => 0, 'was_locked' => 0,
            'created_at' => $weekStart->copy()->addDays(1), // Tue July 14
        ]);

        // This month but before this week: 1 guest session (before Mon July 13)
        GuestActivityLog::factory()->create([
            'session_id' => 'g_month', 'is_authenticated' => 0, 'was_locked' => 0,
            'created_at' => $monthStart->copy()->addDays(5), // Sat July 5
        ]);

        // Outside month range: should be excluded
        GuestActivityLog::factory()->create([
            'session_id' => 'g_old', 'is_authenticated' => 0, 'was_locked' => 0,
            'created_at' => now()->subMonths(2),
        ]);

        $stats = $this->repository->getOverviewStats($today, $weekStart, $monthStart);

        $this->assertEquals(2, $stats['guest_today']);   // 2 unique guest sessions today (g_today_1, g_today_2)
        $this->assertEquals(3, $stats['guest_week']);    // + g_week (Tue)
        $this->assertEquals(4, $stats['guest_month']);   // + g_month (Sat July 5)
        $this->assertEquals(4, $stats['total_today']);   // 4 records on July 15 (3 guest + 1 auth)
        $this->assertEquals(3, $stats['unique_today']);  // g_today_1, g_today_2, a_today
        $this->assertEquals(5, $stats['guest_requests_month']); // 3 (July 15) + 1 (July 14) + 1 (July 5)
        $this->assertEquals(1, $stats['locked_month']);  // 1 locked record (g_today_1, July 15)
    }

    /** @test */
    public function it_returns_zero_overview_stats_when_no_data(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00'));

        $stats = $this->repository->getOverviewStats(
            now()->startOfDay(),
            now()->startOfWeek(),
            now()->startOfMonth()
        );

        $this->assertEquals(0, $stats['guest_today']);
        $this->assertEquals(0, $stats['guest_week']);
        $this->assertEquals(0, $stats['guest_month']);
        $this->assertEquals(0, $stats['total_today']);
        $this->assertEquals(0, $stats['unique_today']);
        $this->assertEquals(0, $stats['guest_requests_month']);
        $this->assertEquals(0, $stats['locked_month']);
    }
}
