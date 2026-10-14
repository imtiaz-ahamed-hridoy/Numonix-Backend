<?php

namespace App\Http\Requests\Web\Backend\Notification;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate a request to send a custom notification.
 */
class SendCustomNotificationRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_type' => ['required', 'in:all,specific'],
            'user_ids' => ['required_if:recipient_type,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'data' => ['nullable', 'json'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_type.required' => 'Please enter your Recipient Type.',
            'recipient_type.in' => 'The Recipient Type must be one of: all, specific.',
            'user_ids.*.integer' => 'The User Ids * must be a whole number.',
            'user_ids.*.exists' => 'The selected User Ids * does not exist in our system.',
            'title.required' => 'Please enter your Title.',
            'title.max' => 'The Title must not exceed 120 characters.',
            'body.required' => 'Please enter your Body.',
            'body.max' => 'The Body must not exceed 500 characters.',
        ];
    }

}
