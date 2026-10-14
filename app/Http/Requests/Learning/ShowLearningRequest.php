<?php

namespace App\Http\Requests\Learning;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a learning detail view request.
 */
class ShowLearningRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:0',
            'per_page' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'The Page must be a whole number.',
            'page.min' => 'The Page must be at least 0 characters.',
            'per_page.integer' => 'The Per Page must be a whole number.',
            'per_page.min' => 'The Per Page must be at least 0 characters.',
        ];
    }

}
