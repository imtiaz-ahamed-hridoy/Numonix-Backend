<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SystemSetting extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'title',
        'email',
        'number',
        'system_name',
        'address',
        'copyright_text',
        'logo',
        'favicon',
        'description',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'whatsapp_url',
        'telegram_url',
        'soft_update_alert',
        'force_update_alert',
        'latest_version',
        'app_store_url',
        'play_store_url',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'soft_update_alert'  => 'boolean',
        'force_update_alert' => 'boolean',
    ];

    /**
     * Build a full public URL from a relative path stored in the DB.
     * Works for both API and web requests.
     * Handles legacy Storage::disk paths (storage/uploads/...) transparently.
     */
    private function buildPublicUrl(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        // Already a full URL
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $path = ltrim($value, '/');

        // Legacy Storage::disk path stored as "uploads/logo/xxx" but served via /storage/
        // Strip the storage/ prefix if present so we resolve from public/ directly.
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return asset($path);
    }

    public function getLogoAttribute($value): ?string
    {
        // Always return full URL - both web backend and API need it for <img> tags
        return $this->buildPublicUrl($value);
    }

    public function getFaviconAttribute($value): ?string
    {
        return $this->buildPublicUrl($value);
    }

    /**
     * Observer hook: delete old file from public/ when logo or favicon is replaced.
     */
    protected static function booted()
    {
        static::updating(function ($setting) {
            if ($setting->isDirty('favicon')) {
                $old = $setting->getOriginal('favicon');
                static::deletePublicFileStatic($old);
            }

            if ($setting->isDirty('logo')) {
                $old = $setting->getOriginal('logo');
                static::deletePublicFileStatic($old);
            }
        });
    }

    /**
     * Static helper: delete a file from public/ by its relative or legacy path.
     */
    private static function deletePublicFileStatic(?string $path): void
    {
        if (empty($path) || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $full = public_path($path);
        if (file_exists($full)) {
            @unlink($full);
        }
    }
}
