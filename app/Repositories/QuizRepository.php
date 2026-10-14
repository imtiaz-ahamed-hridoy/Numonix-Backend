<?php

namespace App\Repositories;

use App\Models\Quiz;
use App\Repositories\Interfaces\QuizRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for quiz data access.
 */
class QuizRepository implements QuizRepositoryInterface
{
    public function findOrFail(int $id): Quiz
    {
        return Quiz::findOrFail($id);
    }

    public function getPublishedByLearning(int $learningId): ?Quiz
    {
        return Quiz::where('learning_id', $learningId)
            ->where('status', 'published')
            ->first();
    }

    public function getPublishedByLearningWithQuestions(int $learningId): ?Quiz
    {
        return Quiz::with(['questions' => function ($q) {
            $q->orderBy('id');
        }])
            ->where('learning_id', $learningId)
            ->where('status', 'published')
            ->first();
    }

    public function findWithQuestions(int $id): Quiz
    {
        return Quiz::with(['questions' => function ($q) {
            $q->orderBy('id');
        }])->findOrFail($id);
    }

    public function getAllPublishedWithQuestions(): Collection
    {
        return Quiz::with(['questions' => function ($q) {
            $q->orderBy('id');
        }, 'learning:id,title'])
            ->where('status', 'published')
            ->get();
    }
}
