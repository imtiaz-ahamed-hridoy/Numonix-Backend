<?php

namespace App\Http\Requests\API\Cookie;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate an update request for a cookie settings.
 */
class UpdateCookieSettingsRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'analytics_cookies' => ['sometimes', 'boolean'],
            'preset' => ['sometimes', 'string', 'in:essential_only,accept_all'],
        ];
    }

    public function messages(): array
    {
        return [
            'preset.in' => 'The Preset must be one of: essential_only, accept_all.',
        ];
    }

}
