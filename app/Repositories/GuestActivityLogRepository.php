<?php

namespace App\Repositories;

use App\Models\GuestActivityLog;
use App\Repositories\Interfaces\GuestActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Guest Activity Log data access.
 */
class GuestActivityLogRepository implements GuestActivityLogRepositoryInterface
{
    /**
     * The GuestActivityLog model instance.
     *
     * @var GuestActivityLog
     */
    protected GuestActivityLog $model;

    /**
     * Create a new repository instance.
     *
     * @param  GuestActivityLog  $model
     */
    public function __construct(GuestActivityLog $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Guest Activity Log records.
     *
     * @return Collection<int, GuestActivityLog>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Guest Activity Log record by its ID.
     *
     * @param  int  $id
     * @return GuestActivityLog|null
     */
    public function findById(int $id): ?GuestActivityLog
    {
        return $this->model->find($id);
    }

    /**
     * Find a Guest Activity Log record by ID or throw an exception.
     *
     * @param  int  $id
     * @return GuestActivityLog
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): GuestActivityLog
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Guest Activity Log record.
     *
     * @param  array  $data
     * @return GuestActivityLog
     */
    public function create(array $data): GuestActivityLog
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Guest Activity Log record.
     *
     * @param  GuestActivityLog  $model
     * @param  array  $data
     * @return bool
     */
    public function update(GuestActivityLog $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Guest Activity Log record.
     *
     * @param  GuestActivityLog  $model
     * @return bool|null
     */
    public function delete(GuestActivityLog $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Get a new query builder instance for the GuestActivityLog model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Get distinct guest session IDs count since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @return int
     */
    public function countDistinctGuestSessionsSince(\Illuminate\Support\Carbon $since): int
    {
        return $this->model->where('created_at', '>=', $since)
            ->where('is_authenticated', 0)
            ->distinct('session_id')
            ->count('session_id');
    }

    /**
     * Get daily session aggregation data since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $startDate
     * @return \Illuminate\Support\Collection
     */
    public function getDailySessionsSince(\Illuminate\Support\Carbon $startDate): \Illuminate\Support\Collection
    {
        return $this->model->where('created_at', '>=', $startDate)
            ->select(
                \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT CASE WHEN is_authenticated = 0 THEN session_id END) as guest_sessions'),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT CASE WHEN is_authenticated = 1 THEN session_id END) as auth_sessions'),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT session_id) as total_sessions')
            )
            ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');
    }

    /**
     * Get content engagement data since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @param  int  $limit
     * @return array
     */
    public function getContentEngagementSince(\Illuminate\Support\Carbon $since, int $limit = 15): array
    {
        return $this->model->where('created_at', '>=', $since)
            ->whereNotNull('content_type')
            ->whereNotNull('content_id')
            ->select(
                'content_type',
                'content_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_views'),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT session_id) as unique_viewers'),
                \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN is_authenticated = 0 THEN 1 ELSE 0 END) as guest_views'),
                \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN is_authenticated = 1 THEN 1 ELSE 0 END) as auth_views'),
                \Illuminate\Support\Facades\DB::raw('SUM(was_locked) as locked_hits')
            )
            ->groupBy('content_type', 'content_id')
            ->orderByDesc('total_views')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get top locked content since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @param  int  $limit
     * @return array
     */
    public function getTopLockedContentSince(\Illuminate\Support\Carbon $since, int $limit = 10): array
    {
        return $this->model->where('created_at', '>=', $since)
            ->where('was_locked', true)
            ->whereNotNull('content_type')
            ->whereNotNull('content_id')
            ->select(
                'content_type',
                'content_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as locked_attempts'),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT session_id) as unique_users')
            )
            ->groupBy('content_type', 'content_id')
            ->orderByDesc('locked_attempts')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get activity breakdown by subscription tier since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @return array
     */
    public function getActivityByTierSince(\Illuminate\Support\Carbon $since): array
    {
        return $this->model->where('created_at', '>=', $since)
            ->select(
                \Illuminate\Support\Facades\DB::raw("COALESCE(user_subscription_tier, 'guest') as tier"),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_requests'),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT session_id) as unique_sessions')
            )
            ->groupBy(\Illuminate\Support\Facades\DB::raw("COALESCE(user_subscription_tier, 'guest')"))
            ->orderByDesc('total_requests')
            ->get()
            ->toArray();
    }

    /**
     * Get overview aggregate statistics from guest activity logs.
     *
     * @param  \Illuminate\Support\Carbon  $today      Start of today
     * @param  \Illuminate\Support\Carbon  $weekStart  Start of this week
     * @param  \Illuminate\Support\Carbon  $monthStart Start of this month
     * @return array{guest_today: int, guest_week: int, guest_month: int, total_today: int, unique_today: int, guest_requests_month: int, locked_month: int}
     */
    public function countDistinctGuestSessionsToday(): int
    {
        return $this->model->whereDate('created_at', today())
            ->where('is_authenticated', 0)
            ->distinct('session_id')
            ->count('session_id');
    }

    public function getOverviewStats(
        \Illuminate\Support\Carbon $today,
        \Illuminate\Support\Carbon $weekStart,
        \Illuminate\Support\Carbon $monthStart
    ): array {
        $stats = $this->model->where('created_at', '>=', $monthStart)
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN created_at >= ? AND is_authenticated = 0 THEN session_id END) as guest_today,
                COUNT(DISTINCT CASE WHEN created_at >= ? AND is_authenticated = 0 THEN session_id END) as guest_week,
                COUNT(DISTINCT CASE WHEN created_at >= ? AND is_authenticated = 0 THEN session_id END) as guest_month,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as total_today,
                COUNT(DISTINCT CASE WHEN created_at >= ? THEN session_id END) as unique_today,
                COUNT(CASE WHEN created_at >= ? AND is_authenticated = 0 THEN 1 END) as guest_requests_month,
                COUNT(CASE WHEN created_at >= ? AND is_authenticated = 0 AND was_locked = 1 THEN 1 END) as locked_month
            ', [
                $today, $weekStart, $monthStart,
                $today, $today,
                $monthStart, $monthStart,
            ])
            ->first();

        if (! $stats) {
            return [
                'guest_today' => 0,
                'guest_week' => 0,
                'guest_month' => 0,
                'total_today' => 0,
                'unique_today' => 0,
                'guest_requests_month' => 0,
                'locked_month' => 0,
            ];
        }

        return [
            'guest_today' => (int) $stats->guest_today,
            'guest_week' => (int) $stats->guest_week,
            'guest_month' => (int) $stats->guest_month,
            'total_today' => (int) $stats->total_today,
            'unique_today' => (int) $stats->unique_today,
            'guest_requests_month' => (int) $stats->guest_requests_month,
            'locked_month' => (int) $stats->locked_month,
        ];
    }
}

