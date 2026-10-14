<?php

namespace App\Repositories\Interfaces;

use App\Models\Cms\CompleteGlossary;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for CMS Complete Glossary data access.
 */
interface CmsCompleteGlossaryRepositoryInterface
{
    /**
     * Get all CMS Complete Glossary records.
     *
     * @return Collection<int, CompleteGlossary>
     */
    public function all(): Collection;

    /**
     * Find a CMS Complete Glossary record by its ID.
     *
     * @param  int  $id
     * @return CompleteGlossary|null
     */
    public function findById(int $id): ?CompleteGlossary;

    /**
     * Find a CMS Complete Glossary record by ID or throw an exception.
     *
     * @param  int  $id
     * @return CompleteGlossary
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): CompleteGlossary;

    /**
     * Create a new CMS Complete Glossary record.
     *
     * @param  array  $data
     * @return CompleteGlossary
     */
    public function create(array $data): CompleteGlossary;

    /**
     * Update an existing CMS Complete Glossary record.
     *
     * @param  CompleteGlossary  $model
     * @param  array  $data
     * @return bool
     */
    public function update(CompleteGlossary $model, array $data): bool;

    /**
     * Delete a CMS Complete Glossary record.
     *
     * @param  CompleteGlossary  $model
     * @return bool|null
     */
    public function delete(CompleteGlossary $model): ?bool;
}
