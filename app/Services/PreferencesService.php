<?php

namespace App\Services;

use App\Repositories\Interfaces\PreferencesRepositoryInterface;

/**
 * Service layer for user preference key-value storage.
 */
class PreferencesService
{
    public function __construct(
        private PreferencesRepositoryInterface $preferencesRepository,
    ) {}

    /**
     * Retrieve all preferences for a given user as a key-value map.
     *
     * @return array<string, string>
     */
    public function getPreferences(int $userId): array
    {
        return $this->preferencesRepository->getByUser($userId)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Update multiple preferences for a user (upsert per key).
     *
     * @param  array<string, string>  $preferences
     * @return array<string, string>
     */
    public function updatePreferences(int $userId, array $preferences): array
    {
        foreach ($preferences as $key => $value) {
            if (! is_string($key) || empty($key)) {
                continue;
            }
            $this->preferencesRepository->upsert($userId, $key, $value);
        }

        return $this->getPreferences($userId);
    }
}
