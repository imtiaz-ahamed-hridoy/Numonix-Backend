<?php

namespace App\Http\Resources\EarTraining;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for an ear training level.
 */
class EarTrainingLevelResource extends JsonResource
{
    /**
     * Transform the ear training level into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->level,
            'level' => $this->level,
            'subtitle' => $this->short_description,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'icon' => $this->icon,
            'is_premium' => $this->is_premium,
            'quizzes_count' => $this->whenCounted('quizzes', $this->quizzes_count ?? 0),
        ];
    }
}
