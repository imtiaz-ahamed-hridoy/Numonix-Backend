<?php

namespace App\Repositories;

use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Review data access.
 */
class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * The Review model instance.
     *
     * @var Review
     */
    protected Review $model;

    /**
     * Create a new repository instance.
     *
     * @param  Review  $model
     */
    public function __construct(Review $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Review records.
     *
     * @return Collection<int, Review>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find reviews by status.
     *
     * @param  string  $status
     * @return Collection<int, Review>
     */
    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Find a Review record by its ID.
     *
     * @param  int  $id
     * @return Review|null
     */
    public function findById(int $id): ?Review
    {
        return $this->model->find($id);
    }

    /**
     * Find a Review record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Review
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Review
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Review record.
     *
     * @param  array  $data
     * @return Review
     */
    public function create(array $data): Review
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Review record.
     *
     * @param  Review  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Review $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Review record.
     *
     * @param  Review  $model
     * @return bool|null
     */
    public function delete(Review $model): ?bool
    {
        return $model->delete();
    }
}
