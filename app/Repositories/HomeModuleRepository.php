<?php

namespace App\Repositories;

use App\Models\HomeModule;
use App\Repositories\Interfaces\HomeModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Home Module data access.
 */
class HomeModuleRepository implements HomeModuleRepositoryInterface
{
    /**
     * The HomeModule model instance.
     *
     * @var HomeModule
     */
    protected HomeModule $model;

    /**
     * Create a new repository instance.
     *
     * @param  HomeModule  $model
     */
    public function __construct(HomeModule $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Home Module records.
     *
     * @return Collection<int, HomeModule>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Home Module record by its ID.
     *
     * @param  int  $id
     * @return HomeModule|null
     */
    public function findById(int $id): ?HomeModule
    {
        return $this->model->find($id);
    }

    /**
     * Find a Home Module record by ID or throw an exception.
     *
     * @param  int  $id
     * @return HomeModule
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): HomeModule
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Home Module record.
     *
     * @param  array  $data
     * @return HomeModule
     */
    public function create(array $data): HomeModule
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Home Module record.
     *
     * @param  HomeModule  $model
     * @param  array  $data
     * @return bool
     */
    public function update(HomeModule $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Home Module record.
     *
     * @param  HomeModule  $model
     * @return bool|null
     */
    public function delete(HomeModule $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Get active Home Module records ordered by sort order.
     *
     * @return Collection<int, HomeModule>
     */
    public function getActiveOrdered(): Collection
    {
        return $this->model->active()->ordered()->get();
    }

    /**
     * Get a new query builder instance for the HomeModule model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery();
    }
}
