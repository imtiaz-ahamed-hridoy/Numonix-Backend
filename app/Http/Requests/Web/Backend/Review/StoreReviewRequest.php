<?php

namespace App\Http\Requests\Web\Backend\Review;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

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
            'user_id' => 'required|exists:users,id',
            'ratings' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'status' => 'required|in:Active,Inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Please enter your User Id.',
            'user_id.exists' => 'The selected User Id does not exist in our system.',
            'ratings.required' => 'Please enter your Ratings.',
            'ratings.integer' => 'The Ratings must be a whole number.',
            'ratings.min' => 'The Ratings must be at least 1 characters.',
            'ratings.max' => 'The Ratings must not exceed 5 characters.',
            'comment.max' => 'The Comment must not exceed 1000 characters.',
            'status.required' => 'Please enter your Status.',
            'status.in' => 'The Status must be one of: Active, Inactive.',
        ];
    }

}
