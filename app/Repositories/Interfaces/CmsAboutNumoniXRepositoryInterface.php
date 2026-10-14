<?php

namespace App\Repositories\Interfaces;

use App\Models\Cms\AboutNumoniX;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for CMS About NumoniX data access.
 */
interface CmsAboutNumoniXRepositoryInterface
{
    /**
     * Get all CMS About NumoniX records.
     *
     * @return Collection<int, AboutNumoniX>
     */
    public function all(): Collection;

    /**
     * Find a CMS About NumoniX record by its ID.
     *
     * @param  int  $id
     * @return AboutNumoniX|null
     */
    public function findById(int $id): ?AboutNumoniX;

    /**
     * Find a CMS About NumoniX record by ID or throw an exception.
     *
     * @param  int  $id
     * @return AboutNumoniX
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): AboutNumoniX;

    /**
     * Create a new CMS About NumoniX record.
     *
     * @param  array  $data
     * @return AboutNumoniX
     */
    public function create(array $data): AboutNumoniX;

    /**
     * Update an existing CMS About NumoniX record.
     *
     * @param  AboutNumoniX  $model
     * @param  array  $data
     * @return bool
     */
    public function update(AboutNumoniX $model, array $data): bool;

    /**
     * Delete a CMS About NumoniX record.
     *
     * @param  AboutNumoniX  $model
     * @return bool|null
     */
    public function delete(AboutNumoniX $model): ?bool;
}
