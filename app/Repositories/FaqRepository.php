<?php

namespace App\Repositories;

use App\Models\Faq;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for FAQ data access.
 */
class FaqRepository implements FaqRepositoryInterface
{
    /**
     * The Faq model instance.
     *
     * @var Faq
     */
    protected Faq $model;

    /**
     * Create a new repository instance.
     *
     * @param  Faq  $model
     */
    public function __construct(Faq $model)
    {
        $this->model = $model;
    }

    /**
     * Get all FAQ records.
     *
     * @return Collection<int, Faq>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get active FAQs sorted by section and position.
     *
     * @return Collection<int, Faq>
     */
    public function findActiveSorted(): Collection
    {
        return $this->model->where('status', 'Active')
            ->orderBy('section')
            ->orderBy('id')
            ->get();
    }

    /**
     * Find a FAQ record by its ID.
     *
     * @param  int  $id
     * @return Faq|null
     */
    public function findById(int $id): ?Faq
    {
        return $this->model->find($id);
    }

    /**
     * Find a FAQ record by ID or throw an exception.
     *
     * @param  int  $id
     * @return Faq
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Faq
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new FAQ record.
     *
     * @param  array  $data
     * @return Faq
     */
    public function create(array $data): Faq
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing FAQ record.
     *
     * @param  Faq  $model
     * @param  array  $data
     * @return bool
     */
    public function update(Faq $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a FAQ record.
     *
     * @param  Faq  $model
     * @return bool|null
     */
    public function delete(Faq $model): ?bool
    {
        return $model->delete();
    }
}
