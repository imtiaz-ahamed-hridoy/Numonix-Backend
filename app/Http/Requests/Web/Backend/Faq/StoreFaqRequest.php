<?php

namespace App\Http\Requests\Web\Backend\Faq;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate and persist a new faq.
 */
class StoreFaqRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section' => 'nullable|string',
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'required|in:Active,Inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Please enter your Question.',
            'answer.required' => 'Please enter your Answer.',
            'status.required' => 'Please enter your Status.',
            'status.in' => 'The Status must be one of: Active, Inactive.',
        ];
    }

}
