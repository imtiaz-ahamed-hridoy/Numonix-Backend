<?php

namespace App\Repositories;

use App\Models\QuizAttempt;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for quiz attempt data access.
 */
class QuizAttemptRepository implements QuizAttemptRepositoryInterface
{
    public function create(array $data): QuizAttempt
    {
        return QuizAttempt::create($data);
    }

    public function findByQuizForUser(int $quizId, int $userId): Collection
    {
        return QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->get();
    }

    public function findByQuizForGuest(int $quizId, int $guestDeviceId): Collection
    {
        return QuizAttempt::where('quiz_id', $quizId)
            ->where('guest_device_id', $guestDeviceId)
            ->get();
    }

    public function findAttemptByIdForUser(int $attemptId, int $userId): ?QuizAttempt
    {
        return QuizAttempt::where('attempt_id', $attemptId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findAttemptByIdForGuest(int $attemptId, int $guestDeviceId): ?QuizAttempt
    {
        return QuizAttempt::where('attempt_id', $attemptId)
            ->where('guest_device_id', $guestDeviceId)
            ->first();
    }

    public function findLatestByQuizForUser(int $quizId, int $userId): ?QuizAttempt
    {
        return QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->orderBy('attempt_id', 'desc')
            ->first();
    }

    public function findLatestByQuizForGuest(int $quizId, int $guestDeviceId): ?QuizAttempt
    {
        return QuizAttempt::where('quiz_id', $quizId)
            ->where('guest_device_id', $guestDeviceId)
            ->orderBy('attempt_id', 'desc')
            ->first();
    }

    public function deleteForUser(int $quizId, int $userId): void
    {
        QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function deleteForGuest(int $quizId, int $guestDeviceId): void
    {
        QuizAttempt::where('quiz_id', $quizId)
            ->where('guest_device_id', $guestDeviceId)
            ->delete();
    }

    public function reassignByGuestDeviceId(int $guestDeviceId, int $userId): int
    {
        return QuizAttempt::where('guest_device_id', $guestDeviceId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }
}

