<?php

namespace App\Repositories;

use App\Models\ContactSupport;
use App\Repositories\Interfaces\ContactSupportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ContactSupportRepository implements ContactSupportRepositoryInterface
{
    protected ContactSupport $model;

    public function __construct(ContactSupport $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?ContactSupport
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): ContactSupport
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): ContactSupport
    {
        return $this->model->create($data);
    }

    public function update(ContactSupport $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(ContactSupport $model): ?bool
    {
        return $model->delete();
    }
}
