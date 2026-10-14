<?php

namespace App\Repositories\Interfaces;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Email Log data access.
 */
interface EmailLogRepositoryInterface
{
    /**
     * Get all Email Log records.
     *
     * @return Collection<int, EmailLog>
     */
    public function all(): Collection;

    /**
     * Find a Email Log record by its ID.
     *
     * @param  int  $id
     * @return EmailLog|null
     */
    public function findById(int $id): ?EmailLog;

    /**
     * Find a Email Log record by ID or throw an exception.
     *
     * @param  int  $id
     * @return EmailLog
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): EmailLog;

    /**
     * Create a new Email Log record.
     *
     * @param  array  $data
     * @return EmailLog
     */
    public function create(array $data): EmailLog;

    /**
     * Update an existing Email Log record.
     *
     * @param  EmailLog  $model
     * @param  array  $data
     * @return bool
     */
    public function update(EmailLog $model, array $data): bool;

    /**
     * Delete a Email Log record.
     *
     * @param  EmailLog  $model
     * @return bool|null
     */
    public function delete(EmailLog $model): ?bool;
}
