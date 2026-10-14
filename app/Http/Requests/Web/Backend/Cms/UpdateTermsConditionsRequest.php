<?php

namespace App\Http\Requests\Web\Backend\Cms;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for the Terms Conditions CMS page.
 */
class UpdateTermsConditionsRequest extends FormRequest
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
            'last_updated_label' => 'required|string|max:100',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required_with:sections|string|max:500',
            'sections.*.body' => 'required_with:sections|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter your Title.',
            'title.max' => 'The Title must not exceed 255 characters.',
            'last_updated_label.required' => 'Please enter your Last Updated Label.',
            'last_updated_label.max' => 'The Last Updated Label must not exceed 100 characters.',
            'sections.*.title.required_with' => 'The Sections Title field is required when Sections is present.',
            'sections.*.title.max' => 'The Sections Title must not exceed 500 characters.',
            'sections.*.body.required_with' => 'The Sections Body field is required when Sections is present.',
        ];
    }

}
