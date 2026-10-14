<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGuideSection extends Model
{
    protected $table = 'cms_user_guide_sections';

    protected $fillable = [
        'user_guide_id',
        'heading',
    ];


    public function userGuide(): BelongsTo
    {
        return $this->belongsTo(UserGuide::class, 'user_guide_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(UserGuideItem::class, 'section_id')->orderBy('id');
    }
}
