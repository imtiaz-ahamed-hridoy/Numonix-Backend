<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningGroup extends Model
{
    protected $fillable = [
        'category',
        'label',
        'color',
        'description',
        'is_premium',
        'tier2_premium',
        'tier3_premium',
    ];

    protected $casts = [
        'is_premium'    => 'boolean',
        'tier2_premium' => 'boolean',
        'tier3_premium' => 'boolean',
    ];

    public function learnings(): HasMany
    {
        return $this->hasMany(Learning::class, 'group_id');
    }
}
