<?php

namespace App\Repositories\Interfaces;

use App\Models\EarTrainingQuestion;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for ear training question data access.
 */
interface EarTrainingQuestionRepositoryInterface
{
    public function findOrFail(int $id): EarTrainingQuestion;

    public function getByQuizId(int $quizId): Collection;
}
