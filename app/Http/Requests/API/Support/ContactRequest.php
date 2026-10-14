<?php

namespace App\Http\Requests\API\Support;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a contact support submission.
 */
class ContactRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'name.max' => 'Your name cannot exceed 100 characters.',
            'email.required' => 'Please provide your email address so we can reply.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Your email address cannot exceed 255 characters.',
            'message.required' => 'Please enter your message.',
            'message.max' => 'Your message cannot exceed 2000 characters.',
        ];
    }
}
