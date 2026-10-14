<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EarTrainingStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'patterns_label',
        'description',
        'accent_colour',
        'sort_order',
        'is_locked_by_default',
        'unlock_criteria',
        'is_active',
        'is_premium',
        'guest_access_override',
        'next_stage_teaser',
    ];

    protected $casts = [
        'is_locked_by_default' => 'boolean',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'unlock_criteria' => 'array',
    ];

    public function levels(): HasMany
    {
        return $this->hasMany(EarTrainingLevel::class);
    }
}
