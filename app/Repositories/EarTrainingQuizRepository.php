<?php

namespace App\Repositories;

use App\Models\EarTrainingQuiz;
use App\Repositories\Interfaces\EarTrainingQuizRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for ear training quiz data access.
 */
class EarTrainingQuizRepository implements EarTrainingQuizRepositoryInterface
{
    public function findOrFail(int $id): EarTrainingQuiz
    {
        return EarTrainingQuiz::where('status', 'published')->findOrFail($id);
    }

    public function findWithLevelAndQuestions(int $id): EarTrainingQuiz
    {
        return EarTrainingQuiz::with(['level', 'questions'])
            ->where('status', 'published')
            ->findOrFail($id);
    }

    public function getPublishedByLevel(int $levelId): Collection
    {
        return EarTrainingQuiz::where('ear_training_level_id', $levelId)
            ->where('status', 'published')
            ->withCount('questions')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function getNextQuiz(int $currentQuizId, int $levelId): ?EarTrainingQuiz
    {
        return EarTrainingQuiz::where('ear_training_level_id', $levelId)
            ->where('status', 'published')
            ->where('id', '>', $currentQuizId)
            ->orderBy('id', 'ASC')
            ->first();
    }
}
