<?php

namespace App\Http\Requests\Web\Backend\Cms;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for the About Numoni X CMS page.
 */
class UpdateAboutNumoniXRequest extends FormRequest
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
            'heading' => 'required|string|max:255',
            'sections' => 'nullable|array',
            'sections.*.id' => 'nullable|integer|exists:cms_about_numonix_sections,id',
            'sections.*.heading' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.items' => 'nullable|array',
            'sections.*.items.*.id' => 'nullable|integer|exists:cms_about_numonix_items,id',
            'sections.*.items.*.title' => 'nullable|string|max:500',
            'sections.*.items.*.body' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter your Title.',
            'title.max' => 'The Title must not exceed 255 characters.',
            'heading.required' => 'Please enter your Heading.',
            'heading.max' => 'The Heading must not exceed 255 characters.',
            'sections.*.id.integer' => 'The Sections Id must be a whole number.',
            'sections.*.id.exists' => 'The selected Sections Id does not exist in our system.',
            'sections.*.heading.required' => 'Please enter your Sections Heading.',
            'sections.*.heading.max' => 'The Sections Heading must not exceed 255 characters.',
            'sections.*.items.*.id.integer' => 'The Sections Items Id must be a whole number.',
            'sections.*.items.*.id.exists' => 'The selected Sections Items Id does not exist in our system.',
            'sections.*.items.*.title.max' => 'The Sections Items Title must not exceed 500 characters.',
        ];
    }

}
