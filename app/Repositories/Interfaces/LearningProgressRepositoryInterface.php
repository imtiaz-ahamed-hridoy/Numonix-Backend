<?php

namespace App\Repositories\Interfaces;

use App\Models\LearningUserProgress;
use App\Models\LearningChecklistProgress;
use Illuminate\Support\Collection;

/**
 * Repository interface for learning progress data access.
 */
interface LearningProgressRepositoryInterface
{
    public function updateOrCreateLearningProgress(array $attributes, array $values): LearningUserProgress;

    public function updateOrCreateChecklistProgress(array $attributes, array $values): LearningChecklistProgress;

    public function getLearningProgressForUser(int $userId, array $learningIds): Collection;

    public function getLearningProgressForGuest(int $guestDeviceId, array $learningIds): Collection;

    public function getChecklistProgressForUser(int $userId, array $elementIds): Collection;

    public function getChecklistProgressForGuest(int $guestDeviceId, array $elementIds): Collection;

    /**
     * Reassign all unowned learning progress records from a guest device to a registered user.
     *
     * @return int Number of records updated.
     */
    public function reassignProgressByGuestDeviceId(int $guestDeviceId, int $userId): int;

    /**
     * Reassign all unowned checklist progress records from a guest device to a registered user.
     *
     * @return int Number of records updated.
     */
    public function reassignChecklistProgressByGuestDeviceId(int $guestDeviceId, int $userId): int;
}

