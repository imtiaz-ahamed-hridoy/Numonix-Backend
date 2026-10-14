<?php

namespace App\Http\Requests\Learning;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a request to fetch learning items.
 */
class FetchLearningRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'nullable|in:lesson,core_theory,advanced_insight,case_study,fnh_architecture',
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'The Category must be one of: lesson, core_theory, advanced_insight, case_study, fnh_architecture.',
        ];
    }

}
