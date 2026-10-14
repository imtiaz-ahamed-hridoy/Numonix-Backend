<?php

namespace App\Repositories\Interfaces;

use App\Models\FirebaseToken;
use Illuminate\Database\Eloquent\Collection;

interface FirebaseTokenRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?FirebaseToken;
    public function findOrFail(int $id): FirebaseToken;
    public function findByDeviceId(string $deviceId): ?FirebaseToken;
    public function findByToken(string $token): ?FirebaseToken;
    public function findByDeviceIdOrToken(string $deviceId, string $token): ?FirebaseToken;
    public function create(array $data): FirebaseToken;
    public function update(FirebaseToken $model, array $data): bool;
    public function delete(FirebaseToken $model): ?bool;
}
