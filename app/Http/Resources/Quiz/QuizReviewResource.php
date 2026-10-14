<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for a quiz review response.
 *
 * Wraps the array returned by QuizService::getReview()
 * into the exact JSON structure clients expect.
 */
class QuizReviewResource extends JsonResource
{
    /**
     * Transform the quiz review data into an API response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->resource['title'] ?? '',
            'learning_id' => $this->resource['learning_id'] ?? null,
            'learning_title' => $this->resource['learning_title'] ?? null,
            'score' => $this->resource['score'] ?? 0,
            'correct_answers' => $this->resource['correct_answers'] ?? 0,
            'total_questions' => $this->resource['total_questions'] ?? 0,
            'items' => $this->resource['items'] ?? [],
            'actions' => $this->resource['actions'] ?? [],
        ];
    }
}
