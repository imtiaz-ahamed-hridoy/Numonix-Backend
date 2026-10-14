<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Provides file upload, deletion, and update operations.
 *
 * STORAGE STRATEGY
 * ----------------
 * All user-uploaded files are stored under public/uploads/<folder>/
 * and served as:  APP_URL + '/uploads/<folder>/<filename>'
 *
 * This avoids the `php artisan storage:link` symlink requirement entirely,
 * making it compatible with shared hosting and any server where
 * the document root may be the repo root (redirected to /public via .htaccess).
 *
 * uploadToPublic()  - stores in public/uploads/<folder>/ -> returns relative path
 * uploadToStorage() - ALSO stores in public/uploads/<folder>/ for consistency
 *
 * URL conversion is done by app/Helpers/StorageHelper.php::publicUrl()
 */
trait FileManager
{
    /**
     * Normalize any file input into a flat array of UploadedFile objects.
     */
    private function normalizeFiles($files): array
    {
        if (empty($files)) return [];

        if ($files instanceof UploadedFile) return [$files];

        $iterator = is_array($files) ? $files : [$files];
        $normalized = [];

        foreach ($iterator as $item) {
            if ($item instanceof UploadedFile) {
                $normalized[] = $item;
            } elseif (is_array($item)) {
                $normalized = array_merge($normalized, $this->normalizeFiles($item));
            }
        }

        return $normalized;
    }

    /**
     * Upload single or multiple files to public/uploads/<folder>/.
     * Returns an array of relative paths like: uploads/avatar/xxx.png
     */
    public function uploadToPublic($files, string $folder = 'uploads'): array
    {
        $files  = $this->normalizeFiles($files);
        $folder = trim($folder, '/');
        $paths  = [];

        foreach ($files as $file) {
            $ext      = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $storageDir = storage_path('app/public/' . $folder);
            $publicDir  = public_path($folder);

            if (!file_exists($storageDir)) {
                @mkdir($storageDir, 0775, true);
            }
            if (!file_exists($publicDir)) {
                @mkdir($publicDir, 0775, true);
            }

            $file->move($storageDir, $filename);
            @copy($storageDir . DIRECTORY_SEPARATOR . $filename, $publicDir . DIRECTORY_SEPARATOR . $filename);
            $paths[] = "$folder/$filename";
        }

        return $paths;
    }

    /**
     * Delete single or multiple files from public/uploads/ + DB update.
     */
    public function deleteFromPublic(object $model, string $column): bool
    {
        $raw = $model->getRawOriginal($column);
        if (empty($raw)) return false;

        $decoded = is_array($raw) ? $raw : json_decode($raw, true);
        $files   = is_array($decoded) ? $decoded : [$raw];

        foreach ($files as $filePath) {
            $filePath    = ltrim($filePath, '/');
            $publicFull  = public_path($filePath);
            $storageFull = storage_path('app/public/' . $filePath);
            if (file_exists($publicFull)) {
                @unlink($publicFull);
            }
            if (file_exists($storageFull)) {
                @unlink($storageFull);
            }
        }

        $model->{$column} = null;
        return $model->save();
    }

    /**
     * Upload files - same as uploadToPublic() for shared-hosting compatibility.
     * Kept for API surface compatibility; disk parameter is ignored.
     * Returns an array of relative paths like: uploads/avatar/xxx.png
     */
    public function uploadToStorage($files, string $folder = 'uploads', string $disk = 'public'): array
    {
        return $this->uploadToPublic($files, $folder);
    }

    /**
     * Delete files stored under public/ by their relative path.
     * Disk parameter kept for API compatibility.
     */
    public function deleteFromStorage(object $model, string $column, string $disk = 'public'): bool
    {
        return $this->deleteFromPublic($model, $column);
    }

    /**
     * Update files in public folder: delete old + upload new.
     */
    public function updateToPublic(object $model, string $column, $newFiles, string $folder = 'uploads'): array
    {
        $this->deleteFromPublic($model, $column);
        $paths = $this->uploadToPublic($newFiles, $folder);

        $model->{$column} = $paths;
        $model->save();

        return $paths;
    }

    /**
     * Update files - same as updateToPublic for shared-hosting compatibility.
     */
    public function updateToStorage(object $model, string $column, $newFiles, string $folder = 'uploads', string $disk = 'public'): array
    {
        return $this->updateToPublic($model, $column, $newFiles, $folder);
    }
}
