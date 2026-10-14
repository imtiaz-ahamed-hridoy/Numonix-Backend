<?php

namespace App\Http\Requests\Learning;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a learning navigation (next/previous) request.
 */
class LearningNavigationRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'learning_id' => 'required|exists:learnings,id',
        ];
    }

    public function messages(): array
    {
        return [
            'learning_id.required' => 'Please enter your Learning Id.',
            'learning_id.exists' => 'The selected Learning Id does not exist in our system.',
        ];
    }

}
