<?php

namespace App\Http\Requests\Web\Backend\Mail;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate and persist a new mail template.
 */
class StoreMailTemplateRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your Name.',
            'subject.required' => 'Please enter your Subject.',
            'body.required' => 'Please enter your Body.',
        ];
    }

}
