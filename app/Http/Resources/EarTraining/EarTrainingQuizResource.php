<?php

namespace App\Http\Resources\EarTraining;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for an ear training quiz.
 */
class EarTrainingQuizResource extends JsonResource
{
    /**
     * Transform the ear training quiz into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ear_training_level_id' => $this->ear_training_level_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'questions_count' => $this->whenCounted('questions', $this->questions_count ?? 0),
        ];
    }
}
