<?php

namespace App\Http\Requests\Web\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate a request to send a password reset link.
 */
class SendPasswordResetLinkRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your Email.',
            'email.email' => 'Please enter a valid email address for Email.',
        ];
    }

}
