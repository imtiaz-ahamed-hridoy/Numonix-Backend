<?php

namespace App\Repositories;

use App\Models\UserPreference;
use App\Repositories\Interfaces\PreferencesRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Repository implementation for user preference data access.
 */
class PreferencesRepository implements PreferencesRepositoryInterface
{
    public function getByUser(int $userId): Collection
    {
        return UserPreference::where('user_id', $userId)->get();
    }

    public function upsert(int $userId, string $key, ?string $value): UserPreference
    {
        return UserPreference::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }
}
