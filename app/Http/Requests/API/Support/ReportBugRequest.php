<?php

namespace App\Http\Requests\API\Support;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a bug report submission.
 */
class ReportBugRequest extends FormRequest
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
            'screenshot' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a brief title for the bug.',
            'title.max' => 'The bug title cannot exceed 150 characters.',
            'description.required' => 'Please describe the bug in detail.',
            'description.max' => 'The bug description cannot exceed 3000 characters.',
            'email.email' => 'Please provide a valid email address.',
            'screenshot.image' => 'The screenshot must be an image file.',
        ];
    }
}
