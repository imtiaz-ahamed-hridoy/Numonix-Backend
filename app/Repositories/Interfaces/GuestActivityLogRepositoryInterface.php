<?php

namespace App\Repositories\Interfaces;

use App\Models\GuestActivityLog;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Guest Activity Log data access.
 */
interface GuestActivityLogRepositoryInterface
{
    /**
     * Get all Guest Activity Log records.
     *
     * @return Collection<int, GuestActivityLog>
     */
    public function all(): Collection;

    /**
     * Find a Guest Activity Log record by its ID.
     *
     * @param  int  $id
     * @return GuestActivityLog|null
     */
    public function findById(int $id): ?GuestActivityLog;

    /**
     * Find a Guest Activity Log record by ID or throw an exception.
     *
     * @param  int  $id
     * @return GuestActivityLog
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): GuestActivityLog;

    /**
     * Create a new Guest Activity Log record.
     *
     * @param  array  $data
     * @return GuestActivityLog
     */
    public function create(array $data): GuestActivityLog;

    /**
     * Update an existing Guest Activity Log record.
     *
     * @param  GuestActivityLog  $model
     * @param  array  $data
     * @return bool
     */
    public function update(GuestActivityLog $model, array $data): bool;

    /**
     * Delete a Guest Activity Log record.
     *
     * @param  GuestActivityLog  $model
     * @return bool|null
     */
    public function delete(GuestActivityLog $model): ?bool;

    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder;

    /**
     * Get distinct guest session IDs count since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @return int
     */
    public function countDistinctGuestSessionsSince(\Illuminate\Support\Carbon $since): int;

    /**
     * Get daily session aggregation data since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $startDate
     * @return \Illuminate\Support\Collection
     */
    public function getDailySessionsSince(\Illuminate\Support\Carbon $startDate): \Illuminate\Support\Collection;

    /**
     * Get content engagement data since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @param  int  $limit
     * @return array
     */
    public function getContentEngagementSince(\Illuminate\Support\Carbon $since, int $limit = 15): array;

    /**
     * Get top locked content since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @param  int  $limit
     * @return array
     */
    public function getTopLockedContentSince(\Illuminate\Support\Carbon $since, int $limit = 10): array;

    /**
     * Get activity breakdown by subscription tier since a given date.
     *
     * @param  \Illuminate\Support\Carbon  $since
     * @return array
     */
    public function getActivityByTierSince(\Illuminate\Support\Carbon $since): array;

    /**
     * Get overview aggregate statistics from guest activity logs.
     *
     * @param  \Illuminate\Support\Carbon  $today      Start of today
     * @param  \Illuminate\Support\Carbon  $weekStart  Start of this week
     * @param  \Illuminate\Support\Carbon  $monthStart Start of this month
     * @return array{guest_today: int, guest_week: int, guest_month: int, total_today: int, unique_today: int, guest_requests_month: int, locked_month: int}
     */
    public function getOverviewStats(
        \Illuminate\Support\Carbon $today,
        \Illuminate\Support\Carbon $weekStart,
        \Illuminate\Support\Carbon $monthStart
    ): array;

    /**
     * Get distinct guest session count for today.
     *
     * Equivalent to: GuestActivityLog::today()->guests()->distinct('session_id')->count('session_id')
     *
     * @return int
     */
    public function countDistinctGuestSessionsToday(): int;
}

