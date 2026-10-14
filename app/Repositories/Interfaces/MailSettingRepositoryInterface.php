<?php

namespace App\Repositories\Interfaces;

use App\Models\MailSetting;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Mail Setting data access.
 */
interface MailSettingRepositoryInterface
{
    /**
     * Get all Mail Setting records.
     *
     * @return Collection<int, MailSetting>
     */
    public function all(): Collection;

    /**
     * Find a Mail Setting record by its ID.
     *
     * @param  int  $id
     * @return MailSetting|null
     */
    public function findById(int $id): ?MailSetting;

    /**
     * Find a Mail Setting record by ID or throw an exception.
     *
     * @param  int  $id
     * @return MailSetting
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): MailSetting;

    /**
     * Create a new Mail Setting record.
     *
     * @param  array  $data
     * @return MailSetting
     */
    public function create(array $data): MailSetting;

    /**
     * Update an existing Mail Setting record.
     *
     * @param  MailSetting  $model
     * @param  array  $data
     * @return bool
     */
    public function update(MailSetting $model, array $data): bool;

    /**
     * Delete a Mail Setting record.
     *
     * @param  MailSetting  $model
     * @return bool|null
     */
    public function delete(MailSetting $model): ?bool;
}
