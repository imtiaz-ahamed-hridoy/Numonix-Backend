<?php

namespace App\Http\Requests\Web\Backend\Quiz;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate and persist a new quiz.
 */
class StoreQuizRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'learning_id' => 'required|exists:learnings,id',
            'status' => 'required|in:published,draft',
        ];
    }

    public function messages(): array
    {
        return [
            'learning_id.required' => 'Please enter your Learning Id.',
            'learning_id.exists' => 'The selected Learning Id does not exist in our system.',
            'status.required' => 'Please enter your Status.',
            'status.in' => 'The Status must be one of: published, draft.',
        ];
    }

}
