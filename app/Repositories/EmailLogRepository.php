<?php

namespace App\Repositories;

use App\Models\EmailLog;
use App\Repositories\Interfaces\EmailLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Email Log data access.
 */
class EmailLogRepository implements EmailLogRepositoryInterface
{
    /**
     * The EmailLog model instance.
     *
     * @var EmailLog
     */
    protected EmailLog $model;

    /**
     * Create a new repository instance.
     *
     * @param  EmailLog  $model
     */
    public function __construct(EmailLog $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Email Log records.
     *
     * @return Collection<int, EmailLog>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Email Log record by its ID.
     *
     * @param  int  $id
     * @return EmailLog|null
     */
    public function findById(int $id): ?EmailLog
    {
        return $this->model->find($id);
    }

    /**
     * Find a Email Log record by ID or throw an exception.
     *
     * @param  int  $id
     * @return EmailLog
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): EmailLog
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Email Log record.
     *
     * @param  array  $data
     * @return EmailLog
     */
    public function create(array $data): EmailLog
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Email Log record.
     *
     * @param  EmailLog  $model
     * @param  array  $data
     * @return bool
     */
    public function update(EmailLog $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Email Log record.
     *
     * @param  EmailLog  $model
     * @return bool|null
     */
    public function delete(EmailLog $model): ?bool
    {
        return $model->delete();
    }
}
