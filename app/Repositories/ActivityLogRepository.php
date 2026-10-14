<?php

namespace App\Repositories;

use Spatie\Activitylog\Models\Activity;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Activity Log data access.
 */
class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    /**
     * The Activity model instance.
     *
     * @var Activity
     */
    protected Activity $model;

    /**
     * Create a new repository instance.
     *
     * @param  Activity  $model
     */
    public function __construct(Activity $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Activity Log records.
     *
     * @return Collection<int, Activity>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Activity Log record by its ID.
     *
     * @param  int  $id
     * @return Activity|null
     */
    public function findById(int $id): ?Activity
    {
        return $this->model->find($id);
    }

    /**
     * Find a Activity Log record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Activity
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Activity
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Activity Log record.
     *
     * @param  array  $data
     * @return Activity
     */
    public function create(array $data): Activity
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Activity Log record.
     *
     * @param  Activity  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Activity $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Activity Log record.
     *
     * @param  Activity  $model
     * @return bool|null
     */
    public function delete(Activity $model): ?bool
    {
        return $model->delete();
    }
}
