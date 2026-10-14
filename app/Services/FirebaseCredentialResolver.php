<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

class FirebaseCredentialResolver
{
    /**
     * Resolve the active Firebase service account credentials file path.
     *
     * Order of precedence:
     * 1. Dashboard-uploaded credentials
     * 2. Secure private storage
     * 3. Decrypt from storage/app/private/firebase-credentials.enc if plaintext is absent
     * 4. Direct from FIREBASE_CREDENTIALS env / config
     * 5. GOOGLE_APPLICATION_CREDENTIALS env
     *
     * @return string|null Absolute path to credentials JSON file, or null if not found.
     */
    public static function resolve(): ?string
    {
        // 1. Dashboard-uploaded credentials
        $dashboardPath = storage_path('app/firebase-credentials-dashboard.json');
        if (File::exists($dashboardPath) && is_readable($dashboardPath) && filesize($dashboardPath) > 20) {
            return $dashboardPath;
        }

        $privateDashboardPath = storage_path('app/private/firebase-credentials-dashboard.json');
        if (File::exists($privateDashboardPath) && is_readable($privateDashboardPath) && filesize($privateDashboardPath) > 20) {
            return $privateDashboardPath;
        }

        // 2. Secure private storage location
        $privatePath = storage_path('app/private/firebase-credentials.json');
        if (File::exists($privatePath) && is_readable($privatePath) && filesize($privatePath) > 20) {
            return $privatePath;
        }

        // 3. Decrypt from encrypted backup if plaintext is missing
        $encPaths = [
            storage_path('app/private/firebase-credentials-dashboard.enc'),
            storage_path('app/private/firebase-credentials.enc'),
        ];
        foreach ($encPaths as $encPath) {
            if (File::exists($encPath) && is_readable($encPath)) {
                try {
                    $encData = File::get($encPath);
                    $decrypted = Crypt::decryptString($encData);
                    if (is_string($decrypted) && str_contains($decrypted, 'private_key')) {
                        File::put($dashboardPath, $decrypted);
                        @chmod($dashboardPath, 0600);
                        return $dashboardPath;
                    }
                } catch (\Throwable $e) {
                    // Ignore decrypt failure
                }
            }
        }

        // 3. Environment variable FIREBASE_CREDENTIALS or config
        $custom = (!empty($_ENV['FIREBASE_CREDENTIALS']) ? $_ENV['FIREBASE_CREDENTIALS'] : null)
            ?? (!empty($_SERVER['FIREBASE_CREDENTIALS']) ? $_SERVER['FIREBASE_CREDENTIALS'] : null)
            ?? (($val = getenv('FIREBASE_CREDENTIALS')) !== false && !empty($val) ? $val : null)
            ?? env('FIREBASE_CREDENTIALS')
            ?? config('firebase.projects.app.credentials');

        if (!empty($custom)) {
            // Check if $custom is raw JSON
            if (is_string($custom) && str_starts_with(trim($custom), '{') && str_ends_with(trim($custom), '}')) {
                $decoded = json_decode($custom, true);
                if (is_array($decoded) && !empty($decoded['project_id'])) {
                    $dir = storage_path('app/private');
                    if (!File::isDirectory($dir)) File::makeDirectory($dir, 0700, true, true);
                    @File::put($privatePath, $custom);
                    return $privatePath;
                }
            }

            // Check if $custom is base64-encoded JSON
            if (is_string($custom) && !File::exists($custom) && strlen($custom) > 100) {
                $b64 = base64_decode($custom, true);
                if ($b64 !== false && str_starts_with(trim($b64), '{')) {
                    $decoded = json_decode($b64, true);
                    if (is_array($decoded) && !empty($decoded['project_id'])) {
                        $dir = storage_path('app/private');
                        if (!File::isDirectory($dir)) File::makeDirectory($dir, 0700, true, true);
                        @File::put($privatePath, $b64);
                        return $privatePath;
                    }
                }
            }

            if (File::exists($custom) && is_readable($custom) && filesize($custom) > 20) {
                return $custom;
            }

            $baseCustom = base_path($custom);
            if (File::exists($baseCustom) && is_readable($baseCustom) && filesize($baseCustom) > 20) {
                return $baseCustom;
            }

            $storageCustom = storage_path($custom);
            if (File::exists($storageCustom) && is_readable($storageCustom) && filesize($storageCustom) > 20) {
                return $storageCustom;
            }
        }

        // 5. GOOGLE_APPLICATION_CREDENTIALS
        $google = env('GOOGLE_APPLICATION_CREDENTIALS');
        if (!empty($google)) {
            if (File::exists($google) && is_readable($google) && filesize($google) > 20) {
                return $google;
            }
            if (File::exists(base_path($google)) && is_readable(base_path($google)) && filesize(base_path($google)) > 20) {
                return base_path($google);
            }
        }

        return null;
    }
}
