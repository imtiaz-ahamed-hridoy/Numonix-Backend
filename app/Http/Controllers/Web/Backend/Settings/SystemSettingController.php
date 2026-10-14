<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Settings\UpdateSystemSettingRequest;
use App\Models\SystemSetting;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    /**
     * Display the system settings page.
     */
    public function index(): View
    {
        $setting = SystemSetting::latest('id')->first();

        return view('backend.layouts.settings.system_settings', compact('setting'));
    }

    /**
     * Update the system settings.
     * All files stored under public/uploads/ directly (no Storage symlink needed).
     */
    public function update(UpdateSystemSettingRequest $request): RedirectResponse
    {
        try {
            $setting = SystemSetting::latest('id')->first() ?? new SystemSetting;

            $setting->title          = $request->title;
            $setting->email          = $request->email;
            $setting->number         = $request->number;
            $setting->system_name    = $request->system_name;
            $setting->address        = $request->address;
            $setting->copyright_text = $request->copyright_text;
            $setting->description    = $request->description;

            $setting->facebook_url   = $request->facebook_url;
            $setting->twitter_url    = $request->twitter_url;
            $setting->instagram_url  = $request->instagram_url;
            $setting->linkedin_url   = $request->linkedin_url;
            $setting->youtube_url    = $request->youtube_url;
            $setting->whatsapp_url   = $request->whatsapp_url;
            $setting->telegram_url   = $request->telegram_url;

            // Handle Logo Upload - store in public/uploads/logo/
            if ($request->hasFile('logo')) {
                $this->deletePublicFile($setting->getRawOriginal('logo'));

                $logo     = $request->file('logo');
                $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $dir      = public_path('uploads/logo');
                if (!file_exists($dir)) mkdir($dir, 0775, true);
                $logo->move($dir, $logoName);
                $setting->logo = 'uploads/logo/' . $logoName;
            }

            // Handle Favicon Upload - store in public/uploads/favicon/
            if ($request->hasFile('favicon')) {
                $this->deletePublicFile($setting->getRawOriginal('favicon'));

                $favicon     = $request->file('favicon');
                $faviconName = time() . '_' . uniqid() . '.' . $favicon->getClientOriginalExtension();
                $dir         = public_path('uploads/favicon');
                if (!file_exists($dir)) mkdir($dir, 0775, true);
                $favicon->move($dir, $faviconName);
                $setting->favicon = 'uploads/favicon/' . $faviconName;
            }

            $setting->save();

            return back()->with('t-success', 'Updated successfully');
        } catch (\Throwable $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * Delete a file from the public/ directory by its relative path.
     * Handles both old "uploads/..." paths and legacy "storage/uploads/..." paths.
     */
    private function deletePublicFile(?string $path): void
    {
        if (empty($path)) return;

        $path = ltrim($path, '/');

        // Legacy Storage::disk path - strip 'storage/' prefix
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $full = public_path($path);
        if (file_exists($full)) {
            @unlink($full);
        }
    }
}
