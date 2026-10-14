<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manages the App Update Alerts admin page.
 *
 * The admin can toggle a soft-update alert (dismissible) and a
 * force-update alert (blocking) independently. The mobile app reads
 * the current state via two public API endpoints:
 *   GET /api/app-update/soft
 *   GET /api/app-update/force
 */
class UpdateAlertController extends Controller
{
    /**
     * Display the update alerts settings page.
     */
    public function index(): View
    {
        $setting = SystemSetting::first();

        return view('backend.layouts.settings.update_alerts', compact('setting'));
    }

    /**
     * Save the update alert toggle states.
     */
    public function update(Request $request): RedirectResponse
    {
        // Validate optional metadata. The web middleware converts empty
        // strings to null, so cleared fields pass as nullable and are stored
        // as null, while malformed URLs are rejected with inline errors.
        $request->validate(
            [
                'latest_version' => ['nullable', 'string', 'max:50'],
                'app_store_url'  => ['nullable', 'url', 'max:255'],
                'play_store_url' => ['nullable', 'url', 'max:255'],
            ],
            [],
            [
                'latest_version' => 'Latest Version',
                'app_store_url'  => 'App Store URL',
                'play_store_url' => 'Play Store URL',
            ]
        );

        $setting = SystemSetting::first() ?? new SystemSetting;

        // Checkboxes are only present in the request when checked;
        // use input() with a default to safely handle both states.
        $setting->soft_update_alert  = (bool) $request->input('soft_update_alert', false);
        $setting->force_update_alert = (bool) $request->input('force_update_alert', false);

        // Optional update metadata used by the app's update dialog to deep-link
        // users to the latest version in the stores. Empty inputs are stored
        // as null so the nullable columns stay clean.
        $setting->latest_version  = $request->input('latest_version') ?: null;
        $setting->app_store_url   = $request->input('app_store_url') ?: null;
        $setting->play_store_url  = $request->input('play_store_url') ?: null;

        $setting->save();

        return back()->with('t-success', 'Update alert settings saved successfully.');
    }
}
