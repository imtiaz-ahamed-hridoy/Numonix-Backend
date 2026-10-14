<?php

namespace App\Repositories;

use App\Models\Cms\PrivacyPolicy;
use App\Repositories\Interfaces\CmsPrivacyPolicyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for CMS Privacy Policy data access.
 */
class CmsPrivacyPolicyRepository implements CmsPrivacyPolicyRepositoryInterface
{
    /**
     * The PrivacyPolicy model instance.
     *
     * @var PrivacyPolicy
     */
    protected PrivacyPolicy $model;

    /**
     * Create a new repository instance.
     *
     * @param  PrivacyPolicy  $model
     */
    public function __construct(PrivacyPolicy $model)
    {
        $this->model = $model;
    }

    /**
     * Get all CMS Privacy Policy records.
     *
     * @return Collection<int, PrivacyPolicy>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a CMS Privacy Policy record by its ID.
     *
     * @param  int  $id
     * @return PrivacyPolicy|null
     */
    public function findById(int $id): ?PrivacyPolicy
    {
        return $this->model->find($id);
    }

    /**
     * Find a CMS Privacy Policy record by ID or throw an exception.
     *
     * @param  int  $id
     * @return PrivacyPolicy
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): PrivacyPolicy
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new CMS Privacy Policy record.
     *
     * @param  array  $data
     * @return PrivacyPolicy
     */
    public function create(array $data): PrivacyPolicy
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing CMS Privacy Policy record.
     *
     * @param  PrivacyPolicy  $model
     * @param  array  $data
     * @return bool
     */
    public function update(PrivacyPolicy $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a CMS Privacy Policy record.
     *
     * @param  PrivacyPolicy  $model
     * @return bool|null
     */
    public function delete(PrivacyPolicy $model): ?bool
    {
        return $model->delete();
    }
}
