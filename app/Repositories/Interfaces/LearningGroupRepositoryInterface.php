<?php

namespace App\Repositories\Interfaces;

use App\Models\LearningGroup;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Learning Group data access.
 */
interface LearningGroupRepositoryInterface
{
    /**
     * Get all Learning Group records.
     *
     * @return Collection<int, LearningGroup>
     */
    public function all(): Collection;

    /**
     * Find a Learning Group record by its ID.
     *
     * @param  int  $id
     * @return LearningGroup|null
     */
    public function findById(int $id): ?LearningGroup;

    /**
     * Find a Learning Group record by ID or throw an exception.
     *
     * @param  int  $id
     * @return LearningGroup
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): LearningGroup;

    /**
     * Create a new Learning Group record.
     *
     * @param  array  $data
     * @return LearningGroup
     */
    public function create(array $data): LearningGroup;

    /**
     * Update an existing Learning Group record.
     *
     * @param  LearningGroup  $model
     * @param  array  $data
     * @return bool
     */
    public function update(LearningGroup $model, array $data): bool;

    /**
     * Delete a Learning Group record.
     *
     * @param  LearningGroup  $model
     * @return bool|null
     */
    public function delete(LearningGroup $model): ?bool;
}
