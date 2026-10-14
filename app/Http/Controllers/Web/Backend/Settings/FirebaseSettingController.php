<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Settings\UpdateFirebaseSettingRequest;
use App\Services\FirebaseCredentialResolver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

class FirebaseSettingController extends Controller
{
    public function index()
    {
        $activeConfig = [
            'configured' => false,
            'project_id' => null,
            'client_email' => null,
            'file_path' => null,
        ];

        $resolved = FirebaseCredentialResolver::resolve();
        if ($resolved && File::exists($resolved)) {
            $content = @file_get_contents($resolved);
            $json = json_decode($content, true);
            if (is_array($json) && !empty($json['project_id'])) {
                $activeConfig = [
                    'configured' => true,
                    'project_id' => $json['project_id'],
                    'client_email' => $json['client_email'] ?? 'service-account@firebase',
                    'file_path' => $resolved,
                ];
            }
        }

        return view('backend.layouts.settings.firebase_settings', compact('activeConfig'));
    }

    public function update(UpdateFirebaseSettingRequest $request)
    {
        $file = $request->file('firebase_credentials');
        $raw = file_get_contents($file->getRealPath());
        $json = json_decode($raw, true);

        if (!is_array($json) || empty($json['project_id']) || empty($json['private_key'])) {
            return back()->withErrors(['firebase_credentials' => 'The uploaded file is not a valid Firebase service account JSON key.']);
        }

        $formatted = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Store securely in private storage directory with restricted permissions
        $privateDir = storage_path('app/private');
        if (!File::isDirectory($privateDir)) {
            File::makeDirectory($privateDir, 0700, true, true);
        }

        // Save to dashboard-specific locations and private secure store
        $dest = storage_path('app/firebase-credentials-dashboard.json');
        File::put($dest, $formatted);
        @chmod($dest, 0600);

        $privateDest = $privateDir . '/firebase-credentials-dashboard.json';
        File::put($privateDest, $formatted);
        @chmod($privateDest, 0600);

        $privatePath = $privateDir . '/firebase-credentials.json';
        File::put($privatePath, $formatted);
        @chmod($privatePath, 0600);

        // Also store encrypted backup
        try {
            $encrypted = Crypt::encryptString($formatted);
            File::put($privateDir . '/firebase-credentials-dashboard.enc', $encrypted);
            File::put($privateDir . '/firebase-credentials.enc', $encrypted);
            @chmod($privateDir . '/firebase-credentials-dashboard.enc', 0600);
            @chmod($privateDir . '/firebase-credentials.enc', 0600);
        } catch (\Throwable $e) {
            // Ignore if app key not configured
        }

        // Dynamically update runtime configuration so changes take effect immediately
        config(['firebase.projects.app.credentials' => $dest]);
        config(['services.firebase.credentials_file' => $dest]);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            // Ignore if artisan call is restricted
        }

        $projectId = $json['project_id'];
        return back()->with('t-success', "Firebase service account for project [{$projectId}] activated securely! Push notifications and social login are ready.");
    }

    public function destroy()
    {
        $files = [
            storage_path('app/private/firebase-credentials.json'),
            storage_path('app/private/firebase-credentials.enc'),
            storage_path('app/private/firebase-credentials-dashboard.json'),
            storage_path('app/private/firebase-credentials-dashboard.enc'),
            storage_path('app/firebase-credentials.json'),
            storage_path('app/firebase-credentials-dashboard.json'),
            storage_path('firebase-credentials.json'),
        ];

        foreach ($files as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        config(['firebase.projects.app.credentials' => null]);
        config(['services.firebase.credentials_file' => null]);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
        }

        return back()->with('t-success', 'Firebase credentials have been removed successfully.');
    }
}
