<?php

namespace App\Http\Requests\Web\Backend\EarTraining;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate a request to update an ear-training Question.
 */
class UpdateEarTrainingQuestionRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:5120',
            'tonic_root_hint' => 'nullable|string',
            'tonic_root_hint_description' => 'nullable|string',
            'mcq_options' => 'required|array|min:2',
            'mcq_correct' => 'required|integer',
            'correct_answer_description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'question_text.required' => 'Please enter your Question Text.',
            'audio.mimes' => 'The Audio must be a file of type: mp3, wav, ogg, m4a.',
            'audio.max' => 'The Audio must not exceed 5120 characters.',
            'mcq_options.required' => 'Please enter your Mcq Options.',
            'mcq_options.min' => 'The Mcq Options must be at least 2 characters.',
            'mcq_correct.required' => 'Please enter your Mcq Correct.',
            'mcq_correct.integer' => 'The Mcq Correct must be a whole number.',
        ];
    }

}
