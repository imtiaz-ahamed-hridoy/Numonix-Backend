<?php

namespace App\Repositories\Interfaces;

use App\Models\GuestAccessSetting;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Guest Access Setting data access.
 */
interface GuestAccessSettingRepositoryInterface
{
    /**
     * Get all Guest Access Setting records.
     *
     * @return Collection<int, GuestAccessSetting>
     */
    public function all(): Collection;

    /**
     * Find a Guest Access Setting record by its ID.
     *
     * @param  int  $id
     * @return GuestAccessSetting|null
     */
    public function findById(int $id): ?GuestAccessSetting;

    /**
     * Find a Guest Access Setting record by ID or throw an exception.
     *
     * @param  int  $id
     * @return GuestAccessSetting
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): GuestAccessSetting;

    /**
     * Create a new Guest Access Setting record.
     *
     * @param  array  $data
     * @return GuestAccessSetting
     */
    public function create(array $data): GuestAccessSetting;

    /**
     * Update an existing Guest Access Setting record.
     *
     * @param  GuestAccessSetting  $model
     * @param  array  $data
     * @return bool
     */
    public function update(GuestAccessSetting $model, array $data): bool;

    /**
     * Delete a Guest Access Setting record.
     *
     * @param  GuestAccessSetting  $model
     * @return bool|null
     */
    public function delete(GuestAccessSetting $model): ?bool;

    /**
     * Retrieve a setting value as an integer.
     *
     * @param  string  $key
     * @param  int     $default
     * @return int
     */
    public function getInt(string $key, int $default = 0): int;

    /**
     * Retrieve a JSON setting value as an array.
     *
     * @param  string  $key
     * @param  array   $default
     * @return array
     */
    public function getArray(string $key, array $default = []): array;

    /**
     * Set a setting value (and clear cache).
     *
     * @param  string       $key
     * @param  mixed        $value
     * @param  string|null  $description
     * @return GuestAccessSetting
     */
    public function set(string $key, $value, ?string $description = null): GuestAccessSetting;

    /**
     * Clear cache for a specific key or all settings.
     *
     * @param  string|null  $key
     * @return void
     */
    public function clearCache(?string $key = null): void;
}
