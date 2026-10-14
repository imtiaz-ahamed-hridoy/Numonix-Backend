<?php

namespace App\Repositories\Interfaces;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Activity Log data access.
 */
interface ActivityLogRepositoryInterface
{
    /**
     * Get all Activity Log records.
     *
     * @return Collection<int, Activity>
     */
    public function all(): Collection;

    /**
     * Find a Activity Log record by its ID.
     *
     * @param  int  $id
     * @return Activity|null
     */
    public function findById(int $id): ?Activity;

    /**
     * Find a Activity Log record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Activity
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Activity;

    /**
     * Create a new Activity Log record.
     *
     * @param  array  $data
     * @return Activity
     */
    public function create(array $data): Activity;

    /**
     * Update an existing Activity Log record.
     *
     * @param  Activity  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Activity $model, array $data): bool;

    /**
     * Delete a Activity Log record.
     *
     * @param  Activity  $model
     * @return bool|null
     */
    public function delete(Activity $model): ?bool;
}
