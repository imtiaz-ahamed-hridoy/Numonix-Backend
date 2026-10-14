<?php

namespace App\Http\Requests\Web\Backend\Cms;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for the Complete Glossary CMS page.
 */
class UpdateCompleteGlossaryRequest extends FormRequest
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
            'sections' => 'nullable|array',
            'sections.*.label' => 'required_with:sections|string|max:255',
            'sections.*.items' => 'nullable|array',
            'sections.*.items.*.title' => 'nullable|string|max:500',
            'sections.*.items.*.body' => 'required_with:sections.*.items|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter your Title.',
            'title.max' => 'The Title must not exceed 255 characters.',
            'sections.*.label.required_with' => 'The Sections Label field is required when Sections is present.',
            'sections.*.label.max' => 'The Sections Label must not exceed 255 characters.',
            'sections.*.items.*.title.max' => 'The Sections Items Title must not exceed 500 characters.',
            'sections.*.items.*.body.required_with' => 'The Sections Items Body field is required when Sections Items is present.',
        ];
    }

}
