<?php

namespace App\Repositories;

use App\Models\LearningUserProgress;
use App\Models\EarTrainingUserProgress;
use App\Repositories\Interfaces\UserProgressRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Repository implementation for user progress data access.
 */
class UserProgressRepository implements UserProgressRepositoryInterface
{
    public function getLearningProgressForUser(int $userId): Collection
    {
        return LearningUserProgress::where('user_id', $userId)
            ->get()
            ->keyBy('learning_id');
    }

    public function getLearningProgressForGuest(int $guestDeviceId): Collection
    {
        return LearningUserProgress::where('guest_device_id', $guestDeviceId)
            ->get()
            ->keyBy('learning_id');
    }

    public function getEarTrainingAttemptsForUser(int $userId): Collection
    {
        return EarTrainingUserProgress::where('user_id', $userId)->get();
    }

    public function getEarTrainingAttemptsForGuest(int $guestDeviceId): Collection
    {
        return EarTrainingUserProgress::where('guest_device_id', $guestDeviceId)->get();
    }
}
