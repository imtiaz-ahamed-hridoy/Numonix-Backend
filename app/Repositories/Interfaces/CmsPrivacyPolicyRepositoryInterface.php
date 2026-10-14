<?php

namespace App\Repositories\Interfaces;

use App\Models\Cms\PrivacyPolicy;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for CMS Privacy Policy data access.
 */
interface CmsPrivacyPolicyRepositoryInterface
{
    /**
     * Get all CMS Privacy Policy records.
     *
     * @return Collection<int, PrivacyPolicy>
     */
    public function all(): Collection;

    /**
     * Find a CMS Privacy Policy record by its ID.
     *
     * @param  int  $id
     * @return PrivacyPolicy|null
     */
    public function findById(int $id): ?PrivacyPolicy;

    /**
     * Find a CMS Privacy Policy record by ID or throw an exception.
     *
     * @param  int  $id
     * @return PrivacyPolicy
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): PrivacyPolicy;

    /**
     * Create a new CMS Privacy Policy record.
     *
     * @param  array  $data
     * @return PrivacyPolicy
     */
    public function create(array $data): PrivacyPolicy;

    /**
     * Update an existing CMS Privacy Policy record.
     *
     * @param  PrivacyPolicy  $model
     * @param  array  $data
     * @return bool
     */
    public function update(PrivacyPolicy $model, array $data): bool;

    /**
     * Delete a CMS Privacy Policy record.
     *
     * @param  PrivacyPolicy  $model
     * @return bool|null
     */
    public function delete(PrivacyPolicy $model): ?bool;
}
