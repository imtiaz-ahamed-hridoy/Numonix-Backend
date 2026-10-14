<?php

namespace App\Http\Requests\Quiz;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a quiz submission request.
 */
class QuizSubmitRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.answer' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Please enter your Answers.',
            'answers.*.question_id.required' => 'Please enter your Answers Question Id.',
            'answers.*.question_id.integer' => 'The Answers Question Id must be a whole number.',
        ];
    }

}
