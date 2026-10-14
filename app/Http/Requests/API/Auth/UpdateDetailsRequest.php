<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate an update request for a details.
 */
class UpdateDetailsRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'dob' => ['sometimes', 'nullable', 'date'],
            'avatar' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'The Name must not exceed 255 characters.',
            'email.email' => 'Please enter a valid email address for Email.',
            'email.max' => 'The Email must not exceed 255 characters.',
            'phone.max' => 'The Phone must not exceed 50 characters.',
            'dob.date' => 'Please enter a valid date for Dob.',
            'avatar.image' => 'The Avatar must be an image file.',
            'avatar.mimes' => 'The Avatar must be a file of type: jpg, jpeg, png, webp.',
        ];
    }

}
