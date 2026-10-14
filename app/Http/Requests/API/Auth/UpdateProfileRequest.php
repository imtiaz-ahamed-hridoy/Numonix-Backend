<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'   => ['sometimes', 'string', 'max:255'],
            'phone'  => ['sometimes', 'nullable', 'string', 'max:50'],
            'dob'    => ['sometimes', 'nullable', 'date'],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'The Name must not exceed 255 characters.',
            'phone.max' => 'The Phone must not exceed 50 characters.',
            'dob.date' => 'Please enter a valid date for Dob.',
            'avatar.image' => 'The Avatar must be an image file.',
            'avatar.mimes' => 'The Avatar must be a file of type: jpeg, png, jpg, gif.',
            'avatar.max' => 'The Avatar must not exceed 2048 characters.',
        ];
    }

}
