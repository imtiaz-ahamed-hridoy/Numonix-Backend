<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class UploadStorageHelper
{
    /**
     * Store an uploaded file in BOTH storage/app/public/<folder> and public/<folder>.
     * This guarantees that whether the URL is /storage/... or /uploads/...
     * or served via Nginx symlink or direct public path, the file is 100% accessible.
     */
    public static function store(UploadedFile $file, string $folder, ?string $customFilename = null): string
    {
        $folder = trim($folder, '/');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $filename = $customFilename ?: (time() . '_' . uniqid() . '.' . $ext);

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

        // If public/storage is a physical directory (not a symlink), mirror there too
        $publicStorageDir = public_path('storage/' . $folder);
        if (!is_link(public_path('storage')) && is_dir(public_path('storage'))) {
            if (!file_exists($publicStorageDir)) {
                @mkdir($publicStorageDir, 0775, true);
            }
            @copy($storageDir . DIRECTORY_SEPARATOR . $filename, $publicStorageDir . DIRECTORY_SEPARATOR . $filename);
        }

        return $folder . '/' . $filename;
    }

    /**
     * Delete a file from BOTH storage/app/public/<path> and public/<path>.
     */
    public static function delete(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path, PHP_URL_PATH) ?? '';
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $storageFull = storage_path('app/public/' . $path);
        $publicFull  = public_path($path);
        $publicStorageFull = public_path('storage/' . $path);

        if (file_exists($storageFull)) {
            @unlink($storageFull);
        }
        if (file_exists($publicFull)) {
            @unlink($publicFull);
        }
        if (!is_link(public_path('storage')) && file_exists($publicStorageFull)) {
            @unlink($publicStorageFull);
        }
    }

    /**
     * Sync all files from public/uploads to storage/app/public/uploads and vice versa.
     */
    public static function syncDirectories(): array
    {
        $publicUploads = public_path('uploads');
        $storageUploads = storage_path('app/public/uploads');

        $synced = 0;
        // 1. Copy public/uploads -> storage/app/public/uploads
        if (is_dir($publicUploads)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($publicUploads, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($files as $item) {
                $subPath = substr($item->getPathname(), strlen($publicUploads));
                $target = $storageUploads . $subPath;
                if ($item->isDir()) {
                    if (!is_dir($target)) @mkdir($target, 0775, true);
                } else {
                    $targetDir = dirname($target);
                    if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
                    if (!file_exists($target) || filemtime($item->getPathname()) > filemtime($target)) {
                        @copy($item->getPathname(), $target);
                        $synced++;
                    }
                }
            }
        }

        // 2. Copy storage/app/public/uploads -> public/uploads
        if (is_dir($storageUploads)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storageUploads, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($files as $item) {
                $subPath = substr($item->getPathname(), strlen($storageUploads));
                $target = $publicUploads . $subPath;
                if ($item->isDir()) {
                    if (!is_dir($target)) @mkdir($target, 0775, true);
                } else {
                    $targetDir = dirname($target);
                    if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
                    if (!file_exists($target) || filemtime($item->getPathname()) > filemtime($target)) {
                        @copy($item->getPathname(), $target);
                        $synced++;
                    }
                }
            }
        }

        // 3. If public/storage is a physical directory (not a symlink), sync to public/storage/uploads as well
        $publicStorageUploads = public_path('storage/uploads');
        if (!is_link(public_path('storage')) && is_dir(public_path('storage')) && is_dir($storageUploads)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storageUploads, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($files as $item) {
                $subPath = substr($item->getPathname(), strlen($storageUploads));
                $target = $publicStorageUploads . $subPath;
                if ($item->isDir()) {
                    if (!is_dir($target)) @mkdir($target, 0775, true);
                } else {
                    $targetDir = dirname($target);
                    if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
                    if (!file_exists($target) || filemtime($item->getPathname()) > filemtime($target)) {
                        @copy($item->getPathname(), $target);
                        $synced++;
                    }
                }
            }
        }

        return ['synced_files' => $synced];
    }
}
