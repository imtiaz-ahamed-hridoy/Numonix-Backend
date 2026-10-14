<?php

namespace App\Http\Requests\Web\Backend\Settings;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Support\Facades\Auth;

/**
 * Validate an update request for a profile.
 */
class UpdateProfileRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|max:100|min:2',
            'email' => 'nullable|email|unique:users,email,'.Auth::id(),
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'The Name must not exceed 100 characters.',
            'name.min' => 'The Name must be at least 2 characters.',
            'email.email' => 'Please enter a valid email address for Email.',
            'email.unique' => 'This Email is already taken. Please use a different one.',
        ];
    }

}
