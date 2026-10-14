<?php

namespace App\Http\Requests\Web\Backend\EmailLog;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate a request to send a bulk mail.
 */
class SendBulkMailRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => 'nullable|array',
            'template_id' => 'required|exists:email_templates,id',
            'schedule_time' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'template_id.required' => 'Please enter your Template Id.',
            'template_id.exists' => 'The selected Template Id does not exist in our system.',
            'schedule_time.date' => 'Please enter a valid date for Schedule Time.',
        ];
    }

}
