<?php

namespace App\Repositories\Interfaces;

use App\Models\UserPreference;
use Illuminate\Support\Collection;

/**
 * Repository interface for user preference data access.
 */
interface PreferencesRepositoryInterface
{
    public function getByUser(int $userId): Collection;

    public function upsert(int $userId, string $key, ?string $value): UserPreference;
}
