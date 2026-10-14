<?php

namespace App\Http\Resources\Faq;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for a single FAQ item.
 */
class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->resource['id'],
            'question' => $this->resource['question'],
            'answer'   => $this->resource['answer'],
        ];
    }
}
