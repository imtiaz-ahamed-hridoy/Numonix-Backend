<?php

namespace App\Http\Requests\API\Guest;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a guest device identification request.
 */
class IdentifyGuestDeviceRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => 'required|string|min:1|max:255',
            'token' => 'required|string|min:1|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required' => 'Please enter your Device Id.',
            'device_id.min' => 'The Device Id must be at least 1 characters.',
            'device_id.max' => 'The Device Id must not exceed 255 characters.',
            'token.required' => 'Please enter your Token.',
            'token.min' => 'The Token must be at least 1 characters.',
            'token.max' => 'The Token must not exceed 255 characters.',
        ];
    }

}
