<?php

namespace App\Http\Requests\Web\Backend\Settings;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for a firebase setting.
 */
class UpdateFirebaseSettingRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firebase_credentials' => [
                'required',
                'file',
                'max:4096',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($ext !== 'json') {
                        $fail('The credentials file must have a .json extension.');
                        return;
                    }
                    $content = @file_get_contents($value->getRealPath());
                    $json = json_decode($content, true);
                    if (json_last_error() !== JSON_ERROR_NONE || empty($json['project_id']) || empty($json['private_key'])) {
                        $fail('The uploaded file is not a valid Firebase service account JSON key (missing project_id or private_key).');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'firebase_credentials.required' => 'Please select a Firebase service account JSON file to upload.',
            'firebase_credentials.max' => 'The credentials file must not exceed 4MB.',
        ];
    }
}
