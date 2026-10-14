<?php

namespace App\Http\Requests\API\Review;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate and persist a new review.
 */
class StoreReviewRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'ratings' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user for this review.',
            'user_id.exists' => 'The selected user does not exist in our system.',
            'ratings.required' => 'Please provide a rating.',
            'ratings.integer' => 'Rating must be a whole number.',
            'ratings.min' => 'Rating must be at least 1.',
            'ratings.max' => 'Rating cannot exceed 5.',
        ];
    }
}
