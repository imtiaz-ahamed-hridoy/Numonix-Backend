<?php

namespace App\Repositories;

use App\Models\FirebaseToken;
use App\Repositories\Interfaces\FirebaseTokenRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FirebaseTokenRepository implements FirebaseTokenRepositoryInterface
{
    protected FirebaseToken $model;

    public function __construct(FirebaseToken $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?FirebaseToken
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): FirebaseToken
    {
        return $this->model->findOrFail($id);
    }

    public function findByDeviceId(string $deviceId): ?FirebaseToken
    {
        return $this->model->where('device_id', $deviceId)->first();
    }

    public function findByToken(string $token): ?FirebaseToken
    {
        return $this->model->where('token', $token)->first();
    }

    public function findByDeviceIdOrToken(string $deviceId, string $token): ?FirebaseToken
    {
        return $this->model->where('device_id', $deviceId)
            ->orWhere('token', $token)
            ->first();
    }

    public function create(array $data): FirebaseToken
    {
        return $this->model->create($data);
    }

    public function update(FirebaseToken $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(FirebaseToken $model): ?bool
    {
        return $model->delete();
    }
}
