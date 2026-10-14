<?php

namespace App\Repositories\Interfaces;

use App\Models\QuizQuestion;

/**
 * Repository interface for quiz question data access.
 */
interface QuizQuestionRepositoryInterface
{
    public function findOrFail(int $id): QuizQuestion;
}
