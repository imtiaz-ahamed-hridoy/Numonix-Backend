<?php

namespace App\Http\Requests\Web\Backend\Cms;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for the User Guide CMS page.
 */
class UpdateUserGuideRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tabs' => 'required|array',
            'tabs.*.tab' => 'required|in:getting_started,harmonic_builder',
            'tabs.*.heading' => 'required|string|max:255',
            'tabs.*.intro_text' => 'nullable|string',
            'tabs.*.sections' => 'nullable|array',
            'tabs.*.sections.*.id' => 'nullable|integer|exists:cms_user_guide_sections,id',
            'tabs.*.sections.*.heading' => 'nullable|string|max:255',
            'tabs.*.sections.*.items' => 'nullable|array',
            'tabs.*.sections.*.items.*.id' => 'nullable|integer|exists:cms_user_guide_items,id',
            'tabs.*.sections.*.items.*.title' => 'nullable|string|max:500',
            'tabs.*.sections.*.items.*.body' => 'nullable|string',
            'tabs.*.sections.*.items.*.existing_images' => 'nullable|array',
            'tabs.*.sections.*.items.*.new_images' => 'nullable|array',
            'tabs.*.sections.*.items.*.new_images.*' => 'nullable|file|image',
            'tabs.*.sections.*.items.*.keys' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'tabs.required' => 'Please enter your Tabs.',
            'tabs.*.tab.required' => 'Please enter your Tabs Tab.',
            'tabs.*.tab.in' => 'The Tabs Tab must be one of: getting_started, harmonic_builder.',
            'tabs.*.heading.required' => 'Please enter your Tabs Heading.',
            'tabs.*.heading.max' => 'The Tabs Heading must not exceed 255 characters.',
            'tabs.*.sections.required' => 'Please enter your Tabs Sections.',
            'tabs.*.sections.*.id.integer' => 'The Tabs Sections Id must be a whole number.',
            'tabs.*.sections.*.id.exists' => 'The selected Tabs Sections Id does not exist in our system.',
            'tabs.*.sections.*.heading.max' => 'The Tabs Sections Heading must not exceed 255 characters.',
            'tabs.*.sections.*.items.*.id.integer' => 'The Tabs Sections Items Id must be a whole number.',
            'tabs.*.sections.*.items.*.id.exists' => 'The selected Tabs Sections Items Id does not exist in our system.',
            'tabs.*.sections.*.items.*.title.max' => 'The Tabs Sections Items Title must not exceed 500 characters.',
        ];
    }

}
