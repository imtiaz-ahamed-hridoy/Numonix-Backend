<?php

namespace App\Repositories;

use App\Models\SystemSetting;
use App\Repositories\Interfaces\SystemSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for System Setting data access.
 */
class SystemSettingRepository implements SystemSettingRepositoryInterface
{
    /**
     * The SystemSetting model instance.
     *
     * @var SystemSetting
     */
    protected SystemSetting $model;

    /**
     * Create a new repository instance.
     *
     * @param  SystemSetting  $model
     */
    public function __construct(SystemSetting $model)
    {
        $this->model = $model;
    }

    /**
     * Get all System Setting records.
     *
     * @return Collection<int, SystemSetting>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a System Setting record by its ID.
     *
     * @param  int  $id
     * @return SystemSetting|null
     */
    public function findById(int $id): ?SystemSetting
    {
        return $this->model->find($id);
    }

    /**
     * Find a System Setting record by ID or throw an exception.
     *
     * @param  int  $id
     * @return SystemSetting
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): SystemSetting
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new System Setting record.
     *
     * @param  array  $data
     * @return SystemSetting
     */
    public function create(array $data): SystemSetting
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing System Setting record.
     *
     * @param  SystemSetting  $model
     * @param  array  $data
     * @return bool
     */
    public function update(SystemSetting $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a System Setting record.
     *
     * @param  SystemSetting  $model
     * @return bool|null
     */
    public function delete(SystemSetting $model): ?bool
    {
        return $model->delete();
    }
}
