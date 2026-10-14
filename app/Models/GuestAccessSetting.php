<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GuestAccessSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Cache TTL in seconds (1 hour).
     * Settings rarely change, so aggressive caching is fine.
     */
    private const CACHE_TTL = 3600;

    /**
     * Retrieve a setting value by key with caching.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("guest_access_setting:{$key}", self::CACHE_TTL, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Retrieve a setting value as an integer.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    /**
     * Retrieve a JSON setting value as an array.
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * Set a setting value (and clear cache).
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  string|null $description
     * @return static
     */
    public static function set(string $key, $value, ?string $description = null): static
    {
        $data = ['value' => is_array($value) ? json_encode($value) : (string) $value];

        if ($description !== null) {
            $data['description'] = $description;
        }

        $setting = static::updateOrCreate(['key' => $key], $data);

        static::clearCache($key);

        return $setting;
    }

    /**
     * Clear cache for a specific key or all settings.
     */
    public static function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("guest_access_setting:{$key}");
        } else {
            // Clear all known setting keys
            $keys = static::pluck('key')->toArray();
            foreach ($keys as $k) {
                Cache::forget("guest_access_setting:{$k}");
            }
        }
    }

    /**
     * Get all settings as a key-value array.
     */
    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Get all settings with descriptions.
     */
    public static function allWithDescriptions(): array
    {
        return static::all()->keyBy('key')->toArray();
    }
}
