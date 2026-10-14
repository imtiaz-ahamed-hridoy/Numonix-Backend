<?php

namespace App\Traits;

/**
 * Converts stored file paths into full public URLs.
 *
 * STORAGE CONVENTION (post-fix)
 * ------------------------------
 * All files are stored under public/uploads/<folder>/ and their relative path
 * in the DB looks like:  uploads/avatar/xxx.png
 *
 * Full URL = APP_URL + '/' + relative_path
 *            e.g. https://api.example.com/uploads/avatar/xxx.png
 *
 * Legacy compatibility:
 * - Paths starting with 'http' are returned as-is (social avatars, CDN links)
 * - Paths that start with '/storage/' are migrated to the uploads/ pattern via the
 *   accessor but served by checking public/storage/ if the file exists there.
 */
trait ImagePathTrait
{
    /**
     * Convert a stored file path into a full publicly-accessible URL.
     *
     * Works for:
     *   - Absolute URLs (http/https)            -> returned as-is
     *   - uploads/...  relative paths            -> APP_URL/uploads/...
     *   - /uploads/... leading-slash paths       -> APP_URL/uploads/...
     *   - storage/...  old Storage::disk paths   -> APP_URL/storage/...  (legacy)
     *   - /storage/... legacy full paths         -> APP_URL/storage/...  (legacy)
     */
    public function fullImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Already a full URL (social login avatar, CDN, etc.)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Strip any leading slash for uniform treatment
        $path = ltrim($path, '/');

        // Standard uploads/ path (new unified format)
        if (str_starts_with($path, 'uploads/')) {
            return rtrim(config('app.url'), '/') . '/' . $path;
        }

        // Legacy: was stored via Storage::disk('public') -> path under storage/app/public
        // These files would be at public/storage/<path> if the symlink existed,
        // but we still build the URL so old records don't break visually.
        if (str_starts_with($path, 'storage/')) {
            return rtrim(config('app.url'), '/') . '/' . $path;
        }

        // Fallback: just prefix with APP_URL
        return rtrim(config('app.url'), '/') . '/' . $path;
    }

    /**
     * Convert ONLY for API requests.
     */
    public function fullImageUrlForApi(?string $path): ?string
    {
        if (request()->is('api/*')) {
            return $this->fullImageUrl($path);
        }

        return $path;
    }
}
