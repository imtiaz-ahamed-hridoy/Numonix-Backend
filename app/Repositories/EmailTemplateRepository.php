<?php

namespace App\Repositories;

use App\Models\EmailTemplate;
use App\Repositories\Interfaces\EmailTemplateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Email Template data access.
 */
class EmailTemplateRepository implements EmailTemplateRepositoryInterface
{
    /**
     * The EmailTemplate model instance.
     *
     * @var EmailTemplate
     */
    protected EmailTemplate $model;

    /**
     * Create a new repository instance.
     *
     * @param  EmailTemplate  $model
     */
    public function __construct(EmailTemplate $model)
    {
        $this->model = $model;
    }

    /**
     * Get all Email Template records.
     *
     * @return Collection<int, EmailTemplate>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Email Template record by its ID.
     *
     * @param  int  $id
     * @return EmailTemplate|null
     */
    public function findById(int $id): ?EmailTemplate
    {
        return $this->model->find($id);
    }

    /**
     * Find a Email Template record by ID or throw an exception.
     *
     * @param  int  $id
     * @return EmailTemplate
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): EmailTemplate
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new Email Template record.
     *
     * @param  array  $data
     * @return EmailTemplate
     */
    public function create(array $data): EmailTemplate
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing Email Template record.
     *
     * @param  EmailTemplate  $model
     * @param  array  $data
     * @return bool
     */
    public function update(EmailTemplate $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a Email Template record.
     *
     * @param  EmailTemplate  $model
     * @return bool|null
     */
    public function delete(EmailTemplate $model): ?bool
    {
        return $model->delete();
    }
}
