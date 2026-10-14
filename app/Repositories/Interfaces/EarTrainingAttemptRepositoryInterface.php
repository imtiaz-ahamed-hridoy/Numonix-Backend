<?php

namespace App\Repositories\Interfaces;

use App\Models\EarTrainingUserProgress;
use Illuminate\Support\Collection;

/**
 * Repository interface for ear training quiz attempt data access.
 */
interface EarTrainingAttemptRepositoryInterface
{
    public function create(array $data): EarTrainingUserProgress;

    public function getAttemptsForUser(int $userId): Collection;

    public function getAttemptsForGuest(int $guestDeviceId): Collection;

    /**
     * Reassign all unowned attempts from a guest device to a registered user.
     *
     * @return int Number of records updated.
     */
    public function reassignByGuestDeviceId(int $guestDeviceId, int $userId): int;
}

