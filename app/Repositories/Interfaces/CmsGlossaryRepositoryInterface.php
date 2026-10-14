<?php

namespace App\Repositories\Interfaces;

use App\Models\Cms\Glossary;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for CMS Glossary data access.
 */
interface CmsGlossaryRepositoryInterface
{
    /**
     * Get all CMS Glossary records.
     *
     * @return Collection<int, Glossary>
     */
    public function all(): Collection;

    /**
     * Find a CMS Glossary record by its ID.
     *
     * @param  int  $id
     * @return Glossary|null
     */
    public function findById(int $id): ?Glossary;

    /**
     * Find a CMS Glossary record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Glossary
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Glossary;

    /**
     * Create a new CMS Glossary record.
     *
     * @param  array  $data
     * @return Glossary
     */
    public function create(array $data): Glossary;

    /**
     * Update an existing CMS Glossary record.
     *
     * @param  Glossary  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Glossary $model, array $data): bool;

    /**
     * Delete a CMS Glossary record.
     *
     * @param  Glossary  $model
     * @return bool|null
     */
    public function delete(Glossary $model): ?bool;
}
