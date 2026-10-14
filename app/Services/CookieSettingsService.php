<?php

namespace App\Services;

use App\Models\UserCookieSetting;
use Illuminate\Http\Request;

/**
 * Service for managing user cookie consent settings.
 *
 * Provides setting definitions, preset options, and CRUD operations
 * for per-user/guest cookie preferences.
 */
class CookieSettingsService
{
    private const SETTING_DEFINITIONS = [
        [
            'key' => 'essential_cookies',
            'label' => 'Essential Cookies',
            'description' => 'Required for the app to function. Cannot be disabled.',
            'is_required' => true,
            'is_toggleable' => false,
        ],
        [
            'key' => 'analytics_cookies',
            'label' => 'Analytics Cookies',
            'description' => 'Help us understand how you use the app so we can improve it.',
            'is_required' => false,
            'is_toggleable' => true,
        ],
    ];

    private const AVAILABLE_PRESETS = [
        ['key' => 'essential_only', 'label' => 'Essential Only', 'api_endpoint' => '/cookie-settings-update', 'method' => 'POST'],
        ['key' => 'accept_all',     'label' => 'Accept All',    'api_endpoint' => '/cookie-settings-update', 'method' => 'POST'],
    ];

    /**
     * Get the current cookie settings for the authenticated user or guest.
     *
     * Returns the full settings payload including setting definitions,
     * current values, available presets, and UI metadata.
     */
    public function showSettings(Request $request): array
    {
        $settings = $this->getOrCreateSettings($request);

        return [
            'description' => 'NumoniX uses cookies and similar technologies to operate the app, understand usage, and personalise your experience.',
            'update_endpoint' => '/cookie-settings-update',
            'update_method' => 'POST',
            'settings' => $this->formatSettings($settings),
            'available_presets' => self::AVAILABLE_PRESETS,
            'save_button' => ['label' => 'Save My Preferences', 'api_endpoint' => '/cookie-settings-update', 'method' => 'POST'],
            'footer_note' => 'You can update your preferences at any time. See our Privacy Policy for more information.',
        ];
    }

    /**
     * Update cookie settings based on the provided validated data.
     *
     * Supports both preset-based updates (essential_only, accept_all)
     * and individual toggle updates.
     *
     * @return array Updated settings and available presets
     */
    public function updateSettings(Request $request, array $validated): array
    {
        $settings = $this->getOrCreateSettings($request);

        if (! empty($validated['preset'])) {
            $analyticsValue = $validated['preset'] === 'accept_all';
            $settings->update([
                'essential_cookies' => true,
                'analytics_cookies' => $analyticsValue,
            ]);
        } else {
            $settings->update(
                collect($validated)->except('preset')->toArray() + ['essential_cookies' => true]
            );
        }

        $settings->refresh();

        return [
            'settings' => $this->formatSettings($settings),
            'available_presets' => self::AVAILABLE_PRESETS,
        ];
    }

    private function getOrCreateSettings(Request $request): UserCookieSetting
    {
        $user = auth('sanctum')->user();

        if ($user) {
            return UserCookieSetting::firstOrCreate(
                ['user_id' => $user->id],
                ['essential_cookies' => true, 'analytics_cookies' => true]
            );
        }

        $guestDevice = $request->input('_guest_device');
        if ($guestDevice) {
            return UserCookieSetting::firstOrCreate(
                ['guest_fingerprint' => $guestDevice->guest_fingerprint],
                ['user_id' => null, 'essential_cookies' => true, 'analytics_cookies' => true]
            );
        }

        return UserCookieSetting::firstOrCreate(
            ['user_id' => null, 'guest_fingerprint' => null],
            ['essential_cookies' => true, 'analytics_cookies' => true]
        );
    }

    private function formatSettings(UserCookieSetting $settings): array
    {
        return collect(self::SETTING_DEFINITIONS)->map(function ($def) use ($settings) {
            return [
                'key' => $def['key'],
                'label' => $def['label'],
                'description' => $def['description'],
                'value' => (bool) $settings->{$def['key']},
                'is_required' => $def['is_required'],
                'is_toggleable' => $def['is_toggleable'],
                'api_endpoint' => $def['is_toggleable'] ? '/cookie-settings-update' : null,
                'method' => $def['is_toggleable'] ? 'POST' : null,
                'param_key' => $def['key'],
            ];
        })->values()->toArray();
    }
}
