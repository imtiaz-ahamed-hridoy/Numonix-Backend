<?php

namespace App\Repositories\Interfaces;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for System Setting data access.
 */
interface SystemSettingRepositoryInterface
{
    /**
     * Get all System Setting records.
     *
     * @return Collection<int, SystemSetting>
     */
    public function all(): Collection;

    /**
     * Find a System Setting record by its ID.
     *
     * @param  int  $id
     * @return SystemSetting|null
     */
    public function findById(int $id): ?SystemSetting;

    /**
     * Find a System Setting record by ID or throw an exception.
     *
     * @param  int  $id
     * @return SystemSetting
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): SystemSetting;

    /**
     * Create a new System Setting record.
     *
     * @param  array  $data
     * @return SystemSetting
     */
    public function create(array $data): SystemSetting;

    /**
     * Update an existing System Setting record.
     *
     * @param  SystemSetting  $model
     * @param  array  $data
     * @return bool
     */
    public function update(SystemSetting $model, array $data): bool;

    /**
     * Delete a System Setting record.
     *
     * @param  SystemSetting  $model
     * @return bool|null
     */
    public function delete(SystemSetting $model): ?bool;
}
