<?php

namespace App\Repositories;

use App\Models\Cms\CompleteGlossary;
use App\Repositories\Interfaces\CmsCompleteGlossaryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for CMS Complete Glossary data access.
 */
class CmsCompleteGlossaryRepository implements CmsCompleteGlossaryRepositoryInterface
{
    /**
     * The CompleteGlossary model instance.
     *
     * @var CompleteGlossary
     */
    protected CompleteGlossary $model;

    /**
     * Create a new repository instance.
     *
     * @param  CompleteGlossary  $model
     */
    public function __construct(CompleteGlossary $model)
    {
        $this->model = $model;
    }

    /**
     * Get all CMS Complete Glossary records.
     *
     * @return Collection<int, CompleteGlossary>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a CMS Complete Glossary record by its ID.
     *
     * @param  int  $id
     * @return CompleteGlossary|null
     */
    public function findById(int $id): ?CompleteGlossary
    {
        return $this->model->find($id);
    }

    /**
     * Find a CMS Complete Glossary record by ID or throw an exception.
     *
     * @param  int  $id
     * @return CompleteGlossary
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): CompleteGlossary
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new CMS Complete Glossary record.
     *
     * @param  array  $data
     * @return CompleteGlossary
     */
    public function create(array $data): CompleteGlossary
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing CMS Complete Glossary record.
     *
     * @param  CompleteGlossary  $model
     * @param  array  $data
     * @return bool
     */
    public function update(CompleteGlossary $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a CMS Complete Glossary record.
     *
     * @param  CompleteGlossary  $model
     * @return bool|null
     */
    public function delete(CompleteGlossary $model): ?bool
    {
        return $model->delete();
    }
}
