<?php

namespace App\Services;

use App\Repositories\Interfaces\GuestActivityLogRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Carbon;

/**
 * Provides analytics data for the admin dashboard.
 * Covers guest users, Tier 1 users, and subscribed users.
 */
class GuestAnalyticsService
{
    protected UserRepositoryInterface $userRepository;
    protected GuestActivityLogRepositoryInterface $activityLogRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        GuestActivityLogRepositoryInterface $activityLogRepository
    ) {
        $this->userRepository = $userRepository;
        $this->activityLogRepository = $activityLogRepository;
    }

    /* ------------------------------------------------------------------
     * Overview Stats
     * ----------------------------------------------------------------*/

    /**
     * Get high-level overview statistics.
     */
    public function getOverviewStats(): array
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        // Delegate to repository
        $stats = $this->activityLogRepository->getOverviewStats($today, $weekStart, $monthStart);

        $avgPagesPerSession = $stats['unique_today'] > 0
            ? round($stats['total_today'] / $stats['unique_today'], 1)
            : 0;

        $lockHitRate = $stats['guest_requests_month'] > 0
            ? round(($stats['locked_month'] / $stats['guest_requests_month']) * 100, 1)
            : 0;

        return [
            'guest_sessions_today' => $stats['guest_today'],
            'guest_sessions_week' => $stats['guest_week'],
            'guest_sessions_month' => $stats['guest_month'],
            'unique_visitors_today' => $stats['unique_today'],
            'avg_pages_per_session' => $avgPagesPerSession,
            'lock_hit_rate' => $lockHitRate,
            'total_requests_today' => $stats['total_today'],
        ];
    }

    /* ------------------------------------------------------------------
     * User Tier Distribution
     * ----------------------------------------------------------------*/

    /**
     * Get user counts grouped by subscription tier.
     */
    public function getUserTierDistribution(): array
    {
        return $this->userRepository->getUserTierDistribution();
    }

    /* ------------------------------------------------------------------
     * Daily Sessions Chart (30-day trend)
     * ----------------------------------------------------------------*/

    /**
     * Get daily unique session counts for the last N days.
     *
     * @return array{dates: string[], guest_sessions: int[], auth_sessions: int[], total_sessions: int[]}
     */
    public function getDailySessionsChart(int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $rawData = $this->activityLogRepository->getDailySessionsSince($startDate);

        $dates = [];
        $guestSessions = [];
        $authSessions = [];
        $totalSessions = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dates[] = $date;
            $guestSessions[] = (int) ($rawData[$date]->guest_sessions ?? 0);
            $authSessions[] = (int) ($rawData[$date]->auth_sessions ?? 0);
            $totalSessions[] = (int) ($rawData[$date]->total_sessions ?? 0);
        }

        return [
            'dates' => $dates,
            'guest_sessions' => $guestSessions,
            'auth_sessions' => $authSessions,
            'total_sessions' => $totalSessions,
        ];
    }

    /* ------------------------------------------------------------------
     * Conversion Funnel
     * ----------------------------------------------------------------*/

    /**
     * Get conversion funnel: Guests -> Registered Tier 1 -> Subscribed.
     */
    public function getConversionFunnel(): array
    {
        // Unique guest sessions in the last 30 days
        $guestSessions = $this->activityLogRepository->countDistinctGuestSessionsSince(now()->subDays(30));

        // Delegate to repository
        $userStats = $this->userRepository->getConversionFunnelStats(now()->subDays(30));

        $totalUsers = $userStats['total_users'];
        $tier1Users = $userStats['tier1_users'];
        $subscribedUsers = $userStats['subscribed_users'];
        $newRegistrations = $userStats['new_registrations'];

        // Conversion rate
        $conversionRate = $guestSessions > 0
            ? round(($newRegistrations / $guestSessions) * 100, 1)
            : 0;

        return [
            'guest_sessions' => $guestSessions,
            'total_registered' => $totalUsers,
            'tier1_users' => $tier1Users,
            'subscribed_users' => $subscribedUsers,
            'new_registrations' => $newRegistrations,
            'conversion_rate' => $conversionRate,
        ];
    }

    /* ------------------------------------------------------------------
     * Registration Trend (30-day)
     * ----------------------------------------------------------------*/

    /**
     * Get daily new registration counts.
     */
    public function getRegistrationTrend(int $days = 30): array
    {
        $rawData = $this->userRepository->getRegistrationTrend($days);

        $startDate = Carbon::today()->subDays($days - 1);

        $dates = [];
        $counts = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dates[] = $date;
            $counts[] = (int) ($rawData[$date] ?? 0);
        }

        return [
            'dates' => $dates,
            'counts' => $counts,
        ];
    }

    /* ------------------------------------------------------------------
     * Content Engagement
     * ----------------------------------------------------------------*/

    /**
     * Get most-viewed content by all users (guests + authenticated).
     */
    public function getContentEngagement(int $days = 30, int $limit = 15): array
    {
        return $this->activityLogRepository->getContentEngagementSince(now()->subDays($days), $limit);
    }

    /**
     * Get top locked content - content guests tried to access but were locked out of.
     */
    public function getTopLockedContent(int $days = 30, int $limit = 10): array
    {
        return $this->activityLogRepository->getTopLockedContentSince(now()->subDays($days), $limit);
    }

    /* ------------------------------------------------------------------
     * User Activity Breakdown
     * ----------------------------------------------------------------*/

    /**
     * Get activity breakdown by subscription tier.
     */
    public function getActivityByTier(int $days = 30): array
    {
        return $this->activityLogRepository->getActivityByTierSince(now()->subDays($days));
    }
}
