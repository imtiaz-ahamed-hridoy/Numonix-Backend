<?php

namespace App\Repositories\Interfaces;

use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Review data access.
 */
interface ReviewRepositoryInterface
{
    /**
     * Get all Review records.
     *
     * @return Collection<int, Review>
     */
    public function all(): Collection;

    /**
     * Find reviews by status.
     *
     * @return Collection<int, Review>
     */
    public function findByStatus(string $status): Collection;

    /**
     * Find a Review record by its ID.
     *
     * @param  int  $id
     * @return Review|null
     */
    public function findById(int $id): ?Review;

    /**
     * Find a Review record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Review
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Review;

    /**
     * Create a new Review record.
     *
     * @param  array  $data
     * @return Review
     */
    public function create(array $data): Review;

    /**
     * Update an existing Review record.
     *
     * @param  Review  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Review $model, array $data): bool;

    /**
     * Delete a Review record.
     *
     * @param  Review  $model
     * @return bool|null
     */
    public function delete(Review $model): ?bool;
}
