<?php

namespace App\Repositories;

use App\Models\Cms\UserGuide;
use App\Repositories\Interfaces\CmsUserGuideRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for CMS User Guide data access.
 */
class CmsUserGuideRepository implements CmsUserGuideRepositoryInterface
{
    /**
     * The UserGuide model instance.
     *
     * @var UserGuide
     */
    protected UserGuide $model;

    /**
     * Create a new repository instance.
     *
     * @param  UserGuide  $model
     */
    public function __construct(UserGuide $model)
    {
        $this->model = $model;
    }

    /**
     * Get all CMS User Guide records.
     *
     * @return Collection<int, UserGuide>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a CMS User Guide record by its ID.
     *
     * @param  int  $id
     * @return UserGuide|null
     */
    public function findById(int $id): ?UserGuide
    {
        return $this->model->find($id);
    }

    /**
     * Find a CMS User Guide record by ID or throw an exception.
     *
     * @param  int  $id
     * @return UserGuide
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): UserGuide
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new CMS User Guide record.
     *
     * @param  array  $data
     * @return UserGuide
     */
    public function create(array $data): UserGuide
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing CMS User Guide record.
     *
     * @param  UserGuide  $model
     * @param  array  $data
     * @return bool
     */
    public function update(UserGuide $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a CMS User Guide record.
     *
     * @param  UserGuide  $model
     * @return bool|null
     */
    public function delete(UserGuide $model): ?bool
    {
        return $model->delete();
    }
}
