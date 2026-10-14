<?php

namespace App\Repositories;

use App\Models\LearningGroup;
use App\Repositories\Interfaces\LearningGroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Learning Group data access.
 */
class LearningGroupRepository implements LearningGroupRepositoryInterface
{
    /**
     * The LearningGroup model instance.
     *
     * @var LearningGroup
     */
    protected LearningGroup $model;

    /**
     * Create a new repository instance.
     *
     * @param  LearningGroup  $model
     */
    public function __construct(LearningGroup $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Learning Group records.
     *
     * @return Collection<int, LearningGroup>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Learning Group record by its ID.
     *
     * @param  int  $id
     * @return LearningGroup|null
     */
    public function findById(int $id): ?LearningGroup
    {
        return $this->model->find($id);
    }

    /**
     * Find a Learning Group record by ID or throw an exception.
     *
     * @param  int  $id
     * @return LearningGroup
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): LearningGroup
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Learning Group record.
     *
     * @param  array  $data
     * @return LearningGroup
     */
    public function create(array $data): LearningGroup
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Learning Group record.
     *
     * @param  LearningGroup  $model
     * @param  array  $data
     * @return bool
     */
    public function update(LearningGroup $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Learning Group record.
     *
     * @param  LearningGroup  $model
     * @return bool|null
     */
    public function delete(LearningGroup $model): ?bool
    {
        return $model->delete();
    }
}
