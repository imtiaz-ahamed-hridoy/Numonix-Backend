<?php

namespace App\Http\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for a bug report submission response.
 */
class BugReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->resource['id'],
            'title'       => $this->resource['title'],
            'description' => $this->resource['description'],
            'email'       => $this->resource['email'],
            'screenshot'  => $this->resource['screenshot'],
            'created_at'  => $this->resource['created_at'],
        ];
    }
}
