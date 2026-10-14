<?php

namespace App\Repositories\Interfaces;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for quiz data access.
 */
interface QuizRepositoryInterface
{
    public function findOrFail(int $id): Quiz;

    public function getPublishedByLearning(int $learningId): ?Quiz;

    public function getPublishedByLearningWithQuestions(int $learningId): ?Quiz;

    public function findWithQuestions(int $id): Quiz;

    public function getAllPublishedWithQuestions(): Collection;
}
