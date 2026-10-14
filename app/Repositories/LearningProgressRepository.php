<?php

namespace App\Repositories;

use App\Models\LearningUserProgress;
use App\Models\LearningChecklistProgress;
use App\Repositories\Interfaces\LearningProgressRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Repository implementation for learning progress data access.
 */
class LearningProgressRepository implements LearningProgressRepositoryInterface
{
    public function updateOrCreateLearningProgress(array $attributes, array $values): LearningUserProgress
    {
        return LearningUserProgress::updateOrCreate($attributes, $values);
    }

    public function updateOrCreateChecklistProgress(array $attributes, array $values): LearningChecklistProgress
    {
        return LearningChecklistProgress::updateOrCreate($attributes, $values);
    }

    public function getLearningProgressForUser(int $userId, array $learningIds): Collection
    {
        if (empty($learningIds)) {
            return collect();
        }

        return LearningUserProgress::where('user_id', $userId)
            ->whereIn('learning_id', $learningIds)
            ->get()
            ->keyBy('learning_id');
    }

    public function getLearningProgressForGuest(int $guestDeviceId, array $learningIds): Collection
    {
        if (empty($learningIds)) {
            return collect();
        }

        return LearningUserProgress::where('guest_device_id', $guestDeviceId)
            ->whereIn('learning_id', $learningIds)
            ->get()
            ->keyBy('learning_id');
    }

    public function getChecklistProgressForUser(int $userId, array $elementIds): Collection
    {
        if (empty($elementIds)) {
            return collect();
        }

        return LearningChecklistProgress::where('user_id', $userId)
            ->whereIn('element_id', $elementIds)
            ->get()
            ->keyBy('element_id');
    }

    public function getChecklistProgressForGuest(int $guestDeviceId, array $elementIds): Collection
    {
        if (empty($elementIds)) {
            return collect();
        }

        return LearningChecklistProgress::where('guest_device_id', $guestDeviceId)
            ->whereIn('element_id', $elementIds)
            ->get()
            ->keyBy('element_id');
    }

    public function reassignProgressByGuestDeviceId(int $guestDeviceId, int $userId): int
    {
        return LearningUserProgress::where('guest_device_id', $guestDeviceId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }

    public function reassignChecklistProgressByGuestDeviceId(int $guestDeviceId, int $userId): int
    {
        return LearningChecklistProgress::where('guest_device_id', $guestDeviceId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }
}

