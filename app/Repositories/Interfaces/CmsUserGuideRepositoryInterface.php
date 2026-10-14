<?php

namespace App\Repositories\Interfaces;

use App\Models\Cms\UserGuide;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for CMS User Guide data access.
 */
interface CmsUserGuideRepositoryInterface
{
    /**
     * Get all CMS User Guide records.
     *
     * @return Collection<int, UserGuide>
     */
    public function all(): Collection;

    /**
     * Find a CMS User Guide record by its ID.
     *
     * @param  int  $id
     * @return UserGuide|null
     */
    public function findById(int $id): ?UserGuide;

    /**
     * Find a CMS User Guide record by ID or throw an exception.
     *
     * @param  int  $id
     * @return UserGuide
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): UserGuide;

    /**
     * Create a new CMS User Guide record.
     *
     * @param  array  $data
     * @return UserGuide
     */
    public function create(array $data): UserGuide;

    /**
     * Update an existing CMS User Guide record.
     *
     * @param  UserGuide  $model
     * @param  array  $data
     * @return bool
     */
    public function update(UserGuide $model, array $data): bool;

    /**
     * Delete a CMS User Guide record.
     *
     * @param  UserGuide  $model
     * @return bool|null
     */
    public function delete(UserGuide $model): ?bool;
}
