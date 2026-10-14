<?php

namespace App\Http\Requests\EarTraining;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate an ear-training quiz submission.
 */
class EarTrainingSubmitRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.answer' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Please enter your Answers.',
            'answers.min' => 'The Answers must be at least 1 characters.',
            'answers.*.question_id.required' => 'Please enter your Answers Question Id.',
            'answers.*.question_id.integer' => 'The Answers Question Id must be a whole number.',
        ];
    }

}
