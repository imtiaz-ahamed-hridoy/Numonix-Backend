<?php

namespace App\Repositories;

use App\Models\GuestAccessSetting;
use App\Repositories\Interfaces\GuestAccessSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Repository implementation for Guest Access Setting data access.
 */
class GuestAccessSettingRepository implements GuestAccessSettingRepositoryInterface
{
    /**
     * The GuestAccessSetting model instance.
     *
     * @var GuestAccessSetting
     */
    protected GuestAccessSetting $model;

    /**
     * Create a new repository instance.
     *
     * @param  GuestAccessSetting  $model
     */
    public function __construct(GuestAccessSetting $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Guest Access Setting records.
     *
     * @return Collection<int, GuestAccessSetting>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Guest Access Setting record by its ID.
     *
     * @param  int  $id
     * @return GuestAccessSetting|null
     */
    public function findById(int $id): ?GuestAccessSetting
    {
        return $this->model->find($id);
    }

    /**
     * Find a Guest Access Setting record by ID or throw an exception.
     *
     * @param  int  $id
     * @return GuestAccessSetting
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): GuestAccessSetting
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Guest Access Setting record.
     *
     * @param  array  $data
     * @return GuestAccessSetting
     */
    public function create(array $data): GuestAccessSetting
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Guest Access Setting record.
     *
     * @param  GuestAccessSetting  $model
     * @param  array  $data
     * @return bool
     */
    public function update(GuestAccessSetting $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Guest Access Setting record.
     *
     * @param  GuestAccessSetting  $model
     * @return bool|null
     */
    public function delete(GuestAccessSetting $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieve a setting value as an integer.
     */
    public function getInt(string $key, int $default = 0): int
    {
        return GuestAccessSetting::getInt($key, $default);
    }

    /**
     * Retrieve a JSON setting value as an array.
     */
    public function getArray(string $key, array $default = []): array
    {
        return GuestAccessSetting::getArray($key, $default);
    }

    /**
     * Set a setting value (and clear cache).
     */
    public function set(string $key, $value, ?string $description = null): GuestAccessSetting
    {
        return GuestAccessSetting::set($key, $value, $description);
    }

    /**
     * Clear cache for a specific key or all settings.
     */
    public function clearCache(?string $key = null): void
    {
        GuestAccessSetting::clearCache($key);
    }
}
