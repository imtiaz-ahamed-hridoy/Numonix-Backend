<?php

namespace App\Http\Requests\Web\Backend\Settings;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for a profile picture.
 */
class UpdateProfilePictureRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.required' => 'Please enter your Profile Picture.',
            'profile_picture.image' => 'The Profile Picture must be an image file.',
            'profile_picture.mimes' => 'The Profile Picture must be a file of type: jpeg, png, jpg, gif.',
            'profile_picture.max' => 'The Profile Picture must not exceed 10240 characters.',
        ];
    }

}
