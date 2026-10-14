<?php

namespace App\Repositories;

use App\Models\Cms\TermsConditions;
use App\Repositories\Interfaces\CmsTermsConditionsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for CMS Terms & Conditions data access.
 */
class CmsTermsConditionsRepository implements CmsTermsConditionsRepositoryInterface
{
    /**
     * The TermsConditions model instance.
     *
     * @var TermsConditions
     */
    protected TermsConditions $model;

    /**
     * Create a new repository instance.
     *
     * @param  TermsConditions  $model
     */
    public function __construct(TermsConditions $model)
    {
        $this->model = $model;
    }

    /**
     * Get all CMS Terms & Conditions records.
     *
     * @return Collection<int, TermsConditions>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a CMS Terms & Conditions record by its ID.
     *
     * @param  int  $id
     * @return TermsConditions|null
     */
    public function findById(int $id): ?TermsConditions
    {
        return $this->model->find($id);
    }

    /**
     * Find a CMS Terms & Conditions record by ID or throw an exception.
     *
     * @param  int  $id
     * @return TermsConditions
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): TermsConditions
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new CMS Terms & Conditions record.
     *
     * @param  array  $data
     * @return TermsConditions
     */
    public function create(array $data): TermsConditions
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing CMS Terms & Conditions record.
     *
     * @param  TermsConditions  $model
     * @param  array  $data
     * @return bool
     */
    public function update(TermsConditions $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a CMS Terms & Conditions record.
     *
     * @param  TermsConditions  $model
     * @return bool|null
     */
    public function delete(TermsConditions $model): ?bool
    {
        return $model->delete();
    }
}
