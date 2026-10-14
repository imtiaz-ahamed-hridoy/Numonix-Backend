<?php

namespace App\Http\Requests\Web\Backend\Settings;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for a password.
 */
class UpdatePasswordRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'old_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'Please enter your Old Password.',
            'password.required' => 'Please enter your Password.',
            'password.confirmed' => 'The Password confirmation does not match.',
            'password.min' => 'The Password must be at least 8 characters.',
        ];
    }

}
