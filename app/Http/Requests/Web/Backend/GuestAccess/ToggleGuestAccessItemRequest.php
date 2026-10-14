<?php

namespace App\Http\Requests\Web\Backend\GuestAccess;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate a toggle request for a guest access item item.
 */
class ToggleGuestAccessItemRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:learning,ear_training',
            'id' => 'required|integer',
            'value' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please enter your Type.',
            'type.in' => 'The Type must be one of: learning, ear_training.',
            'id.required' => 'Please enter your Id.',
            'id.integer' => 'The Id must be a whole number.',
            'value.required' => 'Please enter your Value.',
            'value.in' => 'The Value must be one of: null, true, false.',
        ];
    }

}
