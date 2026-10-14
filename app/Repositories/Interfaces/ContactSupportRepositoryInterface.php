<?php

namespace App\Repositories\Interfaces;

use App\Models\ContactSupport;
use Illuminate\Database\Eloquent\Collection;

interface ContactSupportRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?ContactSupport;
    public function findOrFail(int $id): ContactSupport;
    public function create(array $data): ContactSupport;
    public function update(ContactSupport $model, array $data): bool;
    public function delete(ContactSupport $model): ?bool;
}
