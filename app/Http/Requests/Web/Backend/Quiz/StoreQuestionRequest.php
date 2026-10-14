<?php

namespace App\Http\Requests\Web\Backend\Quiz;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate and persist a new question.
 */
class StoreQuestionRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:mcq,tap_order',
            'question_text' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'image_size' => 'nullable|in:small,medium',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please enter your Type.',
            'type.in' => 'The Type must be one of: mcq, tap_order.',
            'question_text.required' => 'Please enter your Question Text.',
            'image.image' => 'The Image must be an image file.',
            'image.max' => 'The Image must not exceed 2048 characters.',
        ];
    }

}
