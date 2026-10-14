<?php

namespace App\Http\Resources\EarTraining;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for the ear training level complete payload.
 *
 * Wraps the array returned by EarTrainingService::getLevelComplete()
 * into the exact JSON structure clients expect.
 */
class EarTrainingLevelCompleteResource extends JsonResource
{
    /**
     * Transform the level complete data into an API response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->resource['title'] ?? '',
            'level' => $this->resource['level'] ?? [],
            'description' => $this->resource['description'] ?? [],
            'stats' => $this->resource['stats'] ?? [],
            'actions' => $this->resource['actions'] ?? [],
            'motivational_quote' => $this->resource['motivational_quote'] ?? [],
        ];
    }
}
