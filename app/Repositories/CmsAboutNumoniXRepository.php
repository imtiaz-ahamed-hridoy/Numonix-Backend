<?php

namespace App\Repositories;

use App\Models\Cms\AboutNumoniX;
use App\Repositories\Interfaces\CmsAboutNumoniXRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for CMS About NumoniX data access.
 */
class CmsAboutNumoniXRepository implements CmsAboutNumoniXRepositoryInterface
{
    /**
     * The AboutNumoniX model instance.
     *
     * @var AboutNumoniX
     */
    protected AboutNumoniX $model;

    /**
     * Create a new repository instance.
     *
     * @param  AboutNumoniX  $model
     */
    public function __construct(AboutNumoniX $model)
    {
        $this->model = $model;
    }

    /**
     * Get all CMS About NumoniX records.
     *
     * @return Collection<int, AboutNumoniX>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a CMS About NumoniX record by its ID.
     *
     * @param  int  $id
     * @return AboutNumoniX|null
     */
    public function findById(int $id): ?AboutNumoniX
    {
        return $this->model->find($id);
    }

    /**
     * Find a CMS About NumoniX record by ID or throw an exception.
     *
     * @param  int  $id
     * @return AboutNumoniX
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): AboutNumoniX
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new CMS About NumoniX record.
     *
     * @param  array  $data
     * @return AboutNumoniX
     */
    public function create(array $data): AboutNumoniX
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing CMS About NumoniX record.
     *
     * @param  AboutNumoniX  $model
     * @param  array  $data
     * @return bool
     */
    public function update(AboutNumoniX $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a CMS About NumoniX record.
     *
     * @param  AboutNumoniX  $model
     * @return bool|null
     */
    public function delete(AboutNumoniX $model): ?bool
    {
        return $model->delete();
    }
}
