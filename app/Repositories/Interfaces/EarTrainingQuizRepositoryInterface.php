<?php

namespace App\Repositories\Interfaces;

use App\Models\EarTrainingQuiz;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for ear training quiz data access.
 */
interface EarTrainingQuizRepositoryInterface
{
    public function findOrFail(int $id): EarTrainingQuiz;

    public function findWithLevelAndQuestions(int $id): EarTrainingQuiz;

    public function getPublishedByLevel(int $levelId): Collection;

    public function getNextQuiz(int $currentQuizId, int $levelId): ?EarTrainingQuiz;
}
