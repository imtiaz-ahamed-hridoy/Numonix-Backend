<?php

namespace App\Http\Requests\API\Notification;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a request to send a custom notification.
 * Supports sending to all users or selected specific users.
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
            'recipient_type' => 'sometimes|in:all,specific',
            'user_id' => 'required_without_all:user_ids,recipient_type|nullable|exists:users,id',
            'user_ids' => 'required_if:recipient_type,specific|nullable|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required_without_all' => 'Please specify a recipient user or select recipient_type.',
            'user_id.exists' => 'The selected user does not exist in our system.',
            'user_ids.*.exists' => 'One or more selected users do not exist in our system.',
            'title.required' => 'Please enter a notification title.',
            'body.required' => 'Please enter the notification message body.',
        ];
    }
}