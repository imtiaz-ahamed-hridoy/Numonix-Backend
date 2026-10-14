<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Validation\Rules;

/**
 * Validate a user registration request.
 */
class RegisterUserRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your Name.',
            'name.max' => 'The Name must not exceed 255 characters.',
            'email.required' => 'Please enter your Email.',
            'email.email' => 'Please enter a valid email address for Email.',
            'email.max' => 'The Email must not exceed 255 characters.',
            'email.unique' => 'This Email is already taken. Please use a different one.',
            'password.required' => 'Please enter your Password.',
            'password.confirmed' => 'The Password confirmation does not match.',
        ];
    }

}
