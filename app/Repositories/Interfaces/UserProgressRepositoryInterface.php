<?php

namespace App\Repositories\Interfaces;

use App\Models\LearningUserProgress;
use App\Models\EarTrainingQuizAttempt;
use Illuminate\Support\Collection;

/**
 * Repository interface for user progress data access.
 */
interface UserProgressRepositoryInterface
{
    public function getLearningProgressForUser(int $userId): Collection;

    public function getLearningProgressForGuest(int $guestDeviceId): Collection;

    public function getEarTrainingAttemptsForUser(int $userId): Collection;

    public function getEarTrainingAttemptsForGuest(int $guestDeviceId): Collection;
}
