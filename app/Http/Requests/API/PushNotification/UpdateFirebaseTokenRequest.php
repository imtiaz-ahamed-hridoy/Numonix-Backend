<?php

namespace App\Http\Requests\API\PushNotification;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate an update request for a firebase token.
 */
class UpdateFirebaseTokenRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'device_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Please enter your Token.',
            'device_id.required' => 'Please enter your Device Id.',
        ];
    }

}
