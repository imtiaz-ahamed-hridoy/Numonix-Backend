<?php

namespace App\Http\Requests\Quiz;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a quiz answer check request.
 */
class QuizCheckAnswerRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => 'required|exists:quiz_questions,id',
            'answer' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.required' => 'Please enter your Question Id.',
            'question_id.exists' => 'The selected Question Id does not exist in our system.',
            'answer.required' => 'Please enter your Answer.',
        ];
    }

}
