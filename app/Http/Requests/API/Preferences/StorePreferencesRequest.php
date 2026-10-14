<?php

namespace App\Http\Requests\API\Preferences;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate and persist a new preferences.
 */
class StorePreferencesRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preferences' => 'required|array',
            'preferences.*' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'preferences.required' => 'Please enter your Preferences.',
            'preferences.*.max' => 'The Preferences * must not exceed 1000 characters.',
        ];
    }

}
