<?php

namespace App\Http\Requests\Web\Backend\HomeModule;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate and persist a new home module.
 */
class StoreHomeModuleRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|file|max:512',
            'badge_text' => 'nullable|string|max:50',
            'cta_text' => 'required|string|max:50',
            'is_premium' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter your Title.',
            'title.max' => 'The Title must not exceed 255 characters.',
            'description.required' => 'Please enter your Description.',
            'icon.max' => 'The Icon must not exceed 512 characters.',
            'badge_text.max' => 'The Badge Text must not exceed 50 characters.',
            'cta_text.required' => 'Please enter your Cta Text.',
            'cta_text.max' => 'The Cta Text must not exceed 50 characters.',
        ];
    }

}
