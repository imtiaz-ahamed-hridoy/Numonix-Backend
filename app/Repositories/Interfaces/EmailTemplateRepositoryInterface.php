<?php

namespace App\Repositories\Interfaces;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for Email Template data access.
 */
interface EmailTemplateRepositoryInterface
{
    /**
     * Get all Email Template records.
     *
     * @return Collection<int, EmailTemplate>
     */
    public function all(): Collection;

    /**
     * Find a Email Template record by its ID.
     *
     * @param  int  $id
     * @return EmailTemplate|null
     */
    public function findById(int $id): ?EmailTemplate;

    /**
     * Find a Email Template record by ID or throw an exception.
     *
     * @param  int  $id
     * @return EmailTemplate
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): EmailTemplate;

    /**
     * Create a new Email Template record.
     *
     * @param  array  $data
     * @return EmailTemplate
     */
    public function create(array $data): EmailTemplate;

    /**
     * Update an existing Email Template record.
     *
     * @param  EmailTemplate  $model
     * @param  array  $data
     * @return bool
     */
    public function update(EmailTemplate $model, array $data): bool;

    /**
     * Delete a Email Template record.
     *
     * @param  EmailTemplate  $model
     * @return bool|null
     */
    public function delete(EmailTemplate $model): ?bool;
}
