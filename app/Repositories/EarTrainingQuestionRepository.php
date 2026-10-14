<?php

namespace App\Repositories;

use App\Models\EarTrainingQuestion;
use App\Repositories\Interfaces\EarTrainingQuestionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for ear training question data access.
 */
class EarTrainingQuestionRepository implements EarTrainingQuestionRepositoryInterface
{
    public function findOrFail(int $id): EarTrainingQuestion
    {
        return EarTrainingQuestion::findOrFail($id);
    }

    public function getByQuizId(int $quizId): Collection
    {
        return EarTrainingQuestion::where('ear_training_quiz_id', $quizId)
            ->orderBy('id', 'ASC')
            ->get();
    }
}
