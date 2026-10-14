<?php

namespace App\Http\Requests\Web\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Validation\Rules;

/**
 * Validate a password reset request.
 */
class ResetPasswordRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Please enter your Token.',
            'email.required' => 'Please enter your Email.',
            'email.email' => 'Please enter a valid email address for Email.',
            'password.required' => 'Please enter your Password.',
            'password.confirmed' => 'The Password confirmation does not match.',
        ];
    }

}
