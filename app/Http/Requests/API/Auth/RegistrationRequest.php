<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|string|email|max:80|unique:users,email',
            'phone' => 'nullable|string|max:20',
            //            'password' => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&]/', //for production
            'password' => 'required|string|min:6|confirmed',
            'is_age_verified' => 'required|boolean',

            // Guest device identity (from Flutter after /guest/identify)
            // This is the primary mechanism for guest data syncing.
            // Required so frontend always provides them to prevent sync mismatches.
            'device_id'         => 'nullable|string|min:1|max:255',
            'token'             => 'nullable|string|min:1|max:255',
            'guest_fingerprint' => 'nullable|string|min:1|max:255',
        ];
    }

    /**
     * Get custom validation error messages.
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'name.required'              => 'Please enter your name.',
            'name.min'                   => 'Your name must be at least 3 characters.',
            'email.required'             => 'Please enter your email address.',
            'email.unique'               => 'This email address is already registered. Please try logging in instead.',
            'password.required'          => 'Please enter a password.',
            'password.min'               => 'Your password must be at least 6 characters.',
            'password.confirmed'         => 'The password confirmation does not match.',
            'password.regex'             => 'The password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character.',
            'is_age_verified.required'   => 'You must confirm that you are at least 13 years old to create an account.',
            'is_age_verified.boolean'    => 'Age verification must be a yes or no value.',
        ];
    }
}
