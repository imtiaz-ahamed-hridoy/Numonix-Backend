<?php

namespace App\Http\Requests\API\Support;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a feature suggestion submission.
 */
class SuggestFeatureRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:3000'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a brief title for your suggestion.',
            'title.max' => 'The suggestion title cannot exceed 150 characters.',
            'description.required' => 'Please describe your feature suggestion in detail.',
            'description.max' => 'The suggestion description cannot exceed 3000 characters.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }
}
