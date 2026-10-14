<?php

namespace App\Http\Resources\EarTraining;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for the ear training quiz submit result.
 *
 * Wraps the array returned by EarTrainingService::submitQuiz()
 * into the exact JSON structure clients expect.
 */
class EarTrainingSubmitResultResource extends JsonResource
{
    /**
     * Transform the submit result array into an API response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'result' => $this->resource['result'] ?? [],
            'results' => $this->resource['results'] ?? [],
            'next_quiz_id' => $this->resource['next_quiz_id'] ?? null,
            'level_progress' => $this->resource['level_progress'] ?? [],
        ];
    }
}
