<?php

namespace App\Http\Requests\Web\Backend\Cms;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for the Glossary CMS page.
 */
class UpdateGlossaryRequest extends FormRequest
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
            'terms' => 'nullable|array',
            'terms.*.term' => 'required_with:terms|string|max:500',
            'terms.*.definition' => 'required_with:terms|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter your Title.',
            'title.max' => 'The Title must not exceed 255 characters.',
            'terms.*.term.required_with' => 'The Terms Term field is required when Terms is present.',
            'terms.*.term.max' => 'The Terms Term must not exceed 500 characters.',
            'terms.*.definition.required_with' => 'The Terms Definition field is required when Terms is present.',
        ];
    }

}
