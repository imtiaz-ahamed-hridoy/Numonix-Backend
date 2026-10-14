<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for a quiz attempt with scores and results.
 */
class QuizAttemptResource extends JsonResource
{
    /**
     * Transform the quiz attempt into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attempt_id' => $this->attempt_id ?? $this->id,
            'quiz_id' => $this->quiz_id,
            'total_questions' => $this->total_questions,
            'attempted_questions' => $this->attempted_questions,
            'correct_answers' => $this->correct_answers,
            'score' => $this->score,
            'answers_data' => $this->answers_data,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'completed_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
