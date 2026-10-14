<?php

namespace App\Http\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for a feature suggestion submission response.
 */
class FeatureSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->resource['id'],
            'title'       => $this->resource['title'],
            'description' => $this->resource['description'],
            'email'       => $this->resource['email'],
            'created_at'  => $this->resource['created_at'],
        ];
    }
}
