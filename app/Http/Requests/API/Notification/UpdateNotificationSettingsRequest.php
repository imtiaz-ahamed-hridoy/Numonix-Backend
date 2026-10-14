<?php

namespace App\Http\Requests\API\Notification;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate an update request for a notification settings.
 */
class UpdateNotificationSettingsRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_notifications' => ['sometimes', 'boolean'],
            'push_notifications' => ['sometimes', 'boolean'],
            'lesson_updates' => ['sometimes', 'boolean'],
            'practice_reminders' => ['sometimes', 'boolean'],
        ];
    }
}
