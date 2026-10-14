<?php

namespace App\Http\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for a contact support submission response.
 */
class ContactSupportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->resource['id'],
            'name'    => $this->resource['name'],
            'email'   => $this->resource['email'],
            'message' => $this->resource['message'],
        ];
    }
}
