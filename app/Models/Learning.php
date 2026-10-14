<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Learning extends Model
{
    use LogsActivity;

    public const CATEGORIES = ['lesson', 'core_theory', 'case_study', 'fnh_architecture', 'advanced_insight'];
    public const STATUSES = ['active', 'coming_soon', 'inactive'];

    protected $fillable = [
        'category',
        'title',
        'subtitle',
        'slug',
        'read_time',
        'summary',
        'thumbnail_image_url',
        'is_published',
        'is_premium',
        'is_guest_accessible',
        'status',
        'group_id',
    ];

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LearningGroup::class, 'group_id');
    }

    protected $casts = [
        'is_published' => 'boolean',
        'is_premium'   => 'boolean',
        // is_guest_accessible intentionally NOT cast as boolean.
        // It is a tri-state field: null = "Use Category Rule",
        // true = "Force Unlocked", false = "Force Locked".
        // A boolean cast would convert null -> false and destroy
        // the null semantics. The custom accessor below handles
        // the conversion instead.
    ];

    /**
     * Accessor for is_guest_accessible.
     * Normalises raw DB values (null/1/0) to proper nullable bool
     * so strict comparisons (===) in blade templates work correctly:
     *   null -> null  (Use Category Rule)
     *   1    -> true  (Force Unlocked)
     *   0    -> false (Force Locked)
     *
     * ContentAccessConfig uses getRawOriginal() which bypasses this
     * accessor to get the raw DB value.
     */
    public function getIsGuestAccessibleAttribute($value): ?bool
    {
        if ($value === null) {
            return null;
        }
        return (bool) $value;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(LearningUserProgress::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(LearningPage::class)->orderBy('page_number');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function getThumbnailImageUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return url($value);
        }
        return $value;
    }

    public function getThumnailImageUrlAttribute()
    {
        return $this->thumbnail_image_url;
    }
}
