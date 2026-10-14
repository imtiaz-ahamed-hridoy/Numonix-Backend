<?php

namespace App\Services;

use App\Models\UserNotificationSetting;

/**
 * Service for managing user notification preference settings.
 */
class NotificationSettingsService
{
    /**
     * Available notification setting definitions.
     */
    private const SETTING_DEFINITIONS = [
        [
            'key' => 'email_notifications',
            'label' => 'Email Notifications',
            'description' => 'Updates sent to your email',
            'is_toggleable' => true,
        ],
        [
            'key' => 'push_notifications',
            'label' => 'Push Notifications',
            'description' => 'Alerts on your device',
            'is_toggleable' => true,
        ],
        [
            'key' => 'lesson_updates',
            'label' => 'Lesson Updates',
            'description' => 'When new lessons are available',
            'is_toggleable' => true,
        ],
        [
            'key' => 'practice_reminders',
            'label' => 'Practice Reminders',
            'description' => 'Daily streak reminders',
            'is_toggleable' => true,
        ],
    ];

    /**
     * Get the current notification settings for the user.
     */
    public function showSettings($user): array
    {
        $settings = $this->getOrCreateSettings($user);

        return [
            'update_endpoint' => '/notification-settings-update',
            'update_method' => 'POST',
            'settings' => $this->formatSettings($settings),
        ];
    }

    /**
     * Update notification settings for the user.
     */
    public function updateSettings($user, array $validated): array
    {
        $settings = $this->getOrCreateSettings($user);
        $settings->update($validated);
        $settings->refresh();

        return [
            'update_endpoint' => '/notification-settings-update',
            'update_method' => 'POST',
            'settings' => $this->formatSettings($settings),
        ];
    }

    private function getOrCreateSettings($user): UserNotificationSetting
    {
        return UserNotificationSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_notifications' => true,
                'push_notifications' => true,
                'lesson_updates' => true,
                'practice_reminders' => false,
            ]
        );
    }

    private function formatSettings(UserNotificationSetting $settings): array
    {
        return collect(self::SETTING_DEFINITIONS)->map(function ($def) use ($settings) {
            return [
                'key' => $def['key'],
                'label' => $def['label'],
                'description' => $def['description'],
                'value' => (bool) $settings->{$def['key']},
                'is_toggleable' => $def['is_toggleable'],
                'api_endpoint' => '/notification-settings-update',
                'method' => 'POST',
                'param_key' => $def['key'],
            ];
        })->values()->toArray();
    }
}
