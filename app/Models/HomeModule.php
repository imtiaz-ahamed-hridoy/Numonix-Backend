<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class HomeModule extends Model
{
    use LogsActivity;

    public const TYPES = ['learning_category', 'ear_training', 'external'];

    protected $fillable = [
        'title',
        'description',
        'icon',
        'badge_text',
        'cta_text',
        'module_type',
        'module_key',
        'is_premium',
        'is_active',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Scope: only active modules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: ordered by ID (insertion order).
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'ASC');
    }

    /**
     * Get the full URL for the icon.
     */
    public function getIconUrlAttribute(): ?string
    {
        if (!$this->icon) {
            return null;
        }

        if (str_starts_with($this->icon, 'http')) {
            return $this->icon;
        }

        return url($this->icon);
    }
}
