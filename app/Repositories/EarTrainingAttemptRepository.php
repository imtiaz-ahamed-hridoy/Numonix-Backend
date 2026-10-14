<?php

namespace App\Repositories;

use App\Models\EarTrainingUserProgress;
use App\Repositories\Interfaces\EarTrainingAttemptRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Repository implementation for ear training quiz attempt data access.
 */
class EarTrainingAttemptRepository implements EarTrainingAttemptRepositoryInterface
{
    public function create(array $data): EarTrainingUserProgress
    {
        return EarTrainingUserProgress::create($data);
    }

    public function getAttemptsForUser(int $userId): Collection
    {
        return EarTrainingUserProgress::where('user_id', $userId)->get();
    }

    public function getAttemptsForGuest(int $guestDeviceId): Collection
    {
        return EarTrainingUserProgress::where('guest_device_id', $guestDeviceId)
            ->whereNull('user_id')
            ->get();
    }

    public function reassignByGuestDeviceId(int $guestDeviceId, int $userId): int
    {
        return EarTrainingUserProgress::where('guest_device_id', $guestDeviceId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }
}

