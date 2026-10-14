<?php

namespace App\Http\Requests\API\PushNotification;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a request to delete a firebase token.
 */
class DeleteFirebaseTokenRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required' => 'Please enter your Device Id.',
        ];
    }

}
