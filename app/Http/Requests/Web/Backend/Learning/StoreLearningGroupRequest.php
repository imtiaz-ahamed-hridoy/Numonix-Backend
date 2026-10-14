<?php

namespace App\Http\Requests\Web\Backend\Learning;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate and persist a new learning group.
 */
class StoreLearningGroupRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'required|in:case_study,fnh_architecture',
            'label' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_premium' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Please enter your Category.',
            'category.in' => 'The Category must be one of: case_study, fnh_architecture.',
            'label.required' => 'Please enter your Label.',
            'label.max' => 'The Label must not exceed 255 characters.',
            'color.max' => 'The Color must not exceed 20 characters.',
            'description.max' => 'The Description must not exceed 500 characters.',
        ];
    }

}
