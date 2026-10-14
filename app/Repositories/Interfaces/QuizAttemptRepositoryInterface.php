<?php

namespace App\Repositories\Interfaces;

use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for quiz attempt data access.
 */
interface QuizAttemptRepositoryInterface
{
    public function create(array $data): QuizAttempt;

    public function findByQuizForUser(int $quizId, int $userId): Collection;

    public function findByQuizForGuest(int $quizId, int $guestDeviceId): Collection;

    public function findAttemptByIdForUser(int $attemptId, int $userId): ?QuizAttempt;

    public function findAttemptByIdForGuest(int $attemptId, int $guestDeviceId): ?QuizAttempt;

    public function findLatestByQuizForUser(int $quizId, int $userId): ?QuizAttempt;

    public function findLatestByQuizForGuest(int $quizId, int $guestDeviceId): ?QuizAttempt;

    public function deleteForUser(int $quizId, int $userId): void;

    public function deleteForGuest(int $quizId, int $guestDeviceId): void;

    /**
     * Reassign all unowned quiz attempts from a guest device to a registered user.
     *
     * @return int Number of records updated.
     */
    public function reassignByGuestDeviceId(int $guestDeviceId, int $userId): int;
}

