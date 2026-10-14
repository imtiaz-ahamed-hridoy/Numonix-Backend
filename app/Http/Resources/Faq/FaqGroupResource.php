<?php

namespace App\Http\Resources\Faq;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for a FAQ group (section + items).
 */
class FaqGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'section' => $this->resource['section'],
            'items'   => FaqResource::collection($this->resource['items']),
        ];
    }
}
