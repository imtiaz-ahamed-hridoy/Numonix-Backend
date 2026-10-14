<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }


    /** @test */
    public function it_returns_registration_trend_for_single_day(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2026-07-15 12:00:00'));

        User::factory()->count(2)->create([
            'created_at' => now(),
            'role' => 'User',
        ]);

        // Act
        $trend = $this->repository->getRegistrationTrend(3);

        // Assert
        $this->assertEquals(2, $trend[now()->format('Y-m-d')] ?? 0);
    }

    /** @test */
    public function it_returns_registration_trend_excluding_admins(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2026-07-15 12:00:00'));

        User::factory()->count(3)->create([
            'created_at' => now(),
            'role' => 'User',
        ]);
        User::factory()->create([
            'created_at' => now(),
            'role' => 'Admin',
        ]);

        // Act
        $trend = $this->repository->getRegistrationTrend(7);

        // Assert
        $today = now()->format('Y-m-d');
        $this->assertEquals(3, $trend[$today] ?? 0);
    }

    /** @test */
    public function it_returns_empty_array_when_no_registrations_in_period(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2026-07-15 12:00:00'));

        User::factory()->create([
            'created_at' => now()->subDays(10),
            'role' => 'User',
        ]);

        // Act — only look at the last 3 days
        $trend = $this->repository->getRegistrationTrend(3);

        // Assert
        $this->assertEmpty($trend);
    }

    /** @test */
    public function it_returns_registration_trend_across_multiple_days(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2026-07-15 12:00:00'));

        User::factory()->create(['created_at' => now()->subDays(2)->startOfDay(), 'role' => 'User']);
        User::factory()->count(2)->create(['created_at' => now()->subDay()->startOfDay(), 'role' => 'User']);
        User::factory()->count(3)->create(['created_at' => now()->startOfDay(), 'role' => 'User']);

        // Act
        $trend = $this->repository->getRegistrationTrend(5);

        // Assert
        $dayBeforeYesterday = now()->subDays(2)->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $this->assertEquals(1, $trend[$dayBeforeYesterday] ?? 0);
        $this->assertEquals(2, $trend[$yesterday] ?? 0);
        $this->assertEquals(3, $trend[$today] ?? 0);
    }

    /** @test */
    public function it_returns_tier_distribution_with_no_users(): void
    {
        // Act
        $distribution = $this->repository->getUserTierDistribution();

        // Assert
        $this->assertEquals([
            'tier_1' => 0,
            'tier_2' => 0,
            'tier_3' => 0,
        ], $distribution);
    }

    /** @test */
    public function it_returns_tier_distribution_with_mixed_tiers(): void
    {
        // Arrange
        User::factory()->count(5)->create(['subscription_tier' => 'tier_1', 'role' => 'User']);
        User::factory()->count(3)->create(['subscription_tier' => 'tier_2', 'role' => 'User']);
        User::factory()->count(2)->create(['subscription_tier' => 'tier_3', 'role' => 'User']);

        // Act
        $distribution = $this->repository->getUserTierDistribution();

        // Assert
        $this->assertEquals([
            'tier_1' => 5,
            'tier_2' => 3,
            'tier_3' => 2,
        ], $distribution);
    }

    /** @test */
    public function it_returns_tier_distribution_excluding_admins(): void
    {
        // Arrange
        User::factory()->count(4)->create(['subscription_tier' => 'tier_1', 'role' => 'User']);
        User::factory()->create(['subscription_tier' => 'tier_1', 'role' => 'Admin']);

        // Act
        $distribution = $this->repository->getUserTierDistribution();

        // Assert
        $this->assertEquals([
            'tier_1' => 4,
            'tier_2' => 0,
            'tier_3' => 0,
        ], $distribution);
    }

    /** @test */
    public function it_counts_users_with_default_subscription_as_tier_1(): void
    {
        // Arrange — users with no subscription_tier set use the DB default 'tier_1'
        User::factory()->count(3)->create(['subscription_tier' => 'tier_1', 'role' => 'User']);
        User::factory()->count(2)->create(['role' => 'User']); // uses DB default 'tier_1'

        // Act
        $distribution = $this->repository->getUserTierDistribution();

        // Assert — all 5 users counted as tier_1
        $this->assertEquals([
            'tier_1' => 5,
            'tier_2' => 0,
            'tier_3' => 0,
        ], $distribution);
    }

    /** @test */
    public function it_counts_users_with_empty_string_subscription_as_tier_1(): void
    {
        // Arrange
        // The subscription_tier column is an ENUM('tier_1','tier_2','tier_3') which rejects
        // empty strings in MySQL strict mode. To test the $users[''] edge case in the
        // repository, we create users via the factory and then update the column via
        // raw SQL with strict mode temporarily disabled.
        User::factory()->count(3)->create(['subscription_tier' => 'tier_1', 'role' => 'User']);

        $users = User::factory()->count(2)->create(['role' => 'User']);

        // Save and restore the session sql_mode to safely insert '' into the ENUM column
        $originalMode = DB::selectOne('SELECT @@SESSION.sql_mode')->{'@@SESSION.sql_mode'};
        DB::statement("SET SESSION sql_mode = ''");
        try {
            User::whereIn('id', $users->pluck('id'))
                ->update(['subscription_tier' => '']);
        } finally {
            DB::statement("SET SESSION sql_mode = ?", [$originalMode]);
        }

        // Act
        $distribution = $this->repository->getUserTierDistribution();

        // Assert — all 5 users counted as tier_1 (empty string merged into tier_1)
        $this->assertEquals([
            'tier_1' => 5,
            'tier_2' => 0,
            'tier_3' => 0,
        ], $distribution);
    }

    /** @test */
    public function it_returns_tier_distribution_with_only_one_tier(): void
    {
        // Arrange
        User::factory()->count(7)->create(['subscription_tier' => 'tier_2', 'role' => 'User']);

        // Act
        $distribution = $this->repository->getUserTierDistribution();

        // Assert
        $this->assertEquals([
            'tier_1' => 0,
            'tier_2' => 7,
            'tier_3' => 0,
        ], $distribution);
    }
}
