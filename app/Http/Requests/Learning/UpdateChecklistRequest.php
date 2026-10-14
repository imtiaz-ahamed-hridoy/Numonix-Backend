<?php

namespace App\Http\Requests\Learning;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate an update request for a checklist.
 */
class UpdateChecklistRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'learning_id' => 'required|integer|exists:learnings,id',
            'element_id' => 'required|integer|exists:learning_page_elements,id',
            'checked_items' => 'required|array',
            'checked_items.*' => 'integer|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'learning_id.required' => 'Please enter your Learning Id.',
            'learning_id.integer' => 'The Learning Id must be a whole number.',
            'learning_id.exists' => 'The selected Learning Id does not exist in our system.',
            'element_id.required' => 'Please enter your Element Id.',
            'element_id.integer' => 'The Element Id must be a whole number.',
            'element_id.exists' => 'The selected Element Id does not exist in our system.',
            'checked_items.required' => 'Please enter your Checked Items.',
            'checked_items.*.integer' => 'The Checked Items * must be a whole number.',
            'checked_items.*.in' => 'The Checked Items * must be one of: 0, 1.',
        ];
    }

}
