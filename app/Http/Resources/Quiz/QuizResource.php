<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for quiz data.
 */
class QuizResource extends JsonResource
{
    /**
     * Transform the quiz model into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'learning_id' => $this->learning_id,
            'title' => $this->title,
            'status' => $this->status,
            'questions_count' => $this->whenCounted('questions', $this->questions_count ?? 0),
        ];
    }
}
