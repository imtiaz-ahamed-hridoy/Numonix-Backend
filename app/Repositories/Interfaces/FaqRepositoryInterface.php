<?php

namespace App\Repositories\Interfaces;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for FAQ data access.
 */
interface FaqRepositoryInterface
{
    /**
     * Get all FAQ records.
     *
     * @return Collection<int, Faq>
     */
    public function all(): Collection;

    /**
     * Get active FAQs sorted by section and position.
     *
     * @return Collection<int, Faq>
     */
    public function findActiveSorted(): Collection;

    /**
     * Find a FAQ record by its ID.
     *
     * @param  int  $id
     * @return Faq|null
     */
    public function findById(int $id): ?Faq;

    /**
     * Find a FAQ record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Faq
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Faq;

    /**
     * Create a new FAQ record.
     *
     * @param  array  $data
     * @return Faq
     */
    public function create(array $data): Faq;

    /**
     * Update an existing FAQ record.
     *
     * @param  Faq  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Faq $model, array $data): bool;

    /**
     * Delete a FAQ record.
     *
     * @param  Faq  $model
     * @return bool|null
     */
    public function delete(Faq $model): ?bool;
}
