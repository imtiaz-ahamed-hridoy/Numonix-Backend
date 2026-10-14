<?php

namespace App\Http\Requests\API\SocialLogin;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a social login request.
 */
class SocialLoginRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|in:google,apple',
            'token' => 'required|string',
            'email' => 'sometimes|nullable|string|email|max:255',
            'name' => 'sometimes|nullable|string|max:255',
            'device_id' => 'sometimes|nullable|string|max:255',
            'guest_token' => 'sometimes|nullable|string|max:255',
            'guest_fingerprint' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'provider.required' => 'Please specify a social login provider.',
            'provider.in' => 'Please use a supported social provider (Google or Apple).',
            'token.required' => 'The social provider token is required.',
        ];
    }
}