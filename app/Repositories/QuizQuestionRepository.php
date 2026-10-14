<?php

namespace App\Repositories;

use App\Models\QuizQuestion;
use App\Repositories\Interfaces\QuizQuestionRepositoryInterface;

/**
 * Repository implementation for quiz question data access.
 */
class QuizQuestionRepository implements QuizQuestionRepositoryInterface
{
    public function findOrFail(int $id): QuizQuestion
    {
        return QuizQuestion::findOrFail($id);
    }
}
