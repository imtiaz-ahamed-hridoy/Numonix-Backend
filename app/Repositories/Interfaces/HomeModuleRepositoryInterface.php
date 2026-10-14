<?php

namespace App\Repositories\Interfaces;

use App\Models\HomeModule;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Home Module data access.
 */
interface HomeModuleRepositoryInterface
{
    /**
     * Get all Home Module records.
     *
     * @return Collection<int, HomeModule>
     */
    public function all(): Collection;

    /**
     * Find a Home Module record by its ID.
     *
     * @param  int  $id
     * @return HomeModule|null
     */
    public function findById(int $id): ?HomeModule;

    /**
     * Find a Home Module record by ID or throw an exception.
     *
     * @param  int  $id
     * @return HomeModule
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): HomeModule;

    /**
     * Create a new Home Module record.
     *
     * @param  array  $data
     * @return HomeModule
     */
    public function create(array $data): HomeModule;

    /**
     * Update an existing Home Module record.
     *
     * @param  HomeModule  $model
     * @param  array  $data
     * @return bool
     */
    public function update(HomeModule $model, array $data): bool;

    /**
     * Delete a Home Module record.
     *
     * @param  HomeModule  $model
     * @return bool|null
     */
    public function delete(HomeModule $model): ?bool;

    /**
     * Get active Home Module records ordered by sort order.
     *
     * @return Collection<int, HomeModule>
     */
    public function getActiveOrdered(): Collection;

    /**
     * Get a new query builder instance for the HomeModule model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder;
}
