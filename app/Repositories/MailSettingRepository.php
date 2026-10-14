<?php

namespace App\Repositories;

use App\Models\MailSetting;
use App\Repositories\Interfaces\MailSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Mail Setting data access.
 */
class MailSettingRepository implements MailSettingRepositoryInterface
{
    /**
     * The MailSetting model instance.
     *
     * @var MailSetting
     */
    protected MailSetting $model;

    /**
     * Create a new repository instance.
     *
     * @param  MailSetting  $model
     */
    public function __construct(MailSetting $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Mail Setting records.
     *
     * @return Collection<int, MailSetting>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Mail Setting record by its ID.
     *
     * @param  int  $id
     * @return MailSetting|null
     */
    public function findById(int $id): ?MailSetting
    {
        return $this->model->find($id);
    }

    /**
     * Find a Mail Setting record by ID or throw an exception.
     *
     * @param  int  $id
     * @return MailSetting
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): MailSetting
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Mail Setting record.
     *
     * @param  array  $data
     * @return MailSetting
     */
    public function create(array $data): MailSetting
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Mail Setting record.
     *
     * @param  MailSetting  $model
     * @param  array  $data
     * @return bool
     */
    public function update(MailSetting $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Mail Setting record.
     *
     * @param  MailSetting  $model
     * @return bool|null
     */
    public function delete(MailSetting $model): ?bool
    {
        return $model->delete();
    }
}
