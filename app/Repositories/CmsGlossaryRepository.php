<?php

namespace App\Repositories;

use App\Models\Cms\Glossary;
use App\Repositories\Interfaces\CmsGlossaryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for CMS Glossary data access.
 */
class CmsGlossaryRepository implements CmsGlossaryRepositoryInterface
{
    /**
     * The Glossary model instance.
     *
     * @var Glossary
     */
    protected Glossary $model;

    /**
     * Create a new repository instance.
     *
     * @param  Glossary  $model
     */
    public function __construct(Glossary $model)
    {
        $this->model = $model;
    }

    /**
     * Get all CMS Glossary records.
     *
     * @return Collection<int, Glossary>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a CMS Glossary record by its ID.
     *
     * @param  int  $id
     * @return Glossary|null
     */
    public function findById(int $id): ?Glossary
    {
        return $this->model->find($id);
    }

    /**
     * Find a CMS Glossary record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Glossary
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Glossary
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new CMS Glossary record.
     *
     * @param  array  $data
     * @return Glossary
     */
    public function create(array $data): Glossary
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing CMS Glossary record.
     *
     * @param  Glossary  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Glossary $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a CMS Glossary record.
     *
     * @param  Glossary  $model
     * @return bool|null
     */
    public function delete(Glossary $model): ?bool
    {
        return $model->delete();
    }
}
