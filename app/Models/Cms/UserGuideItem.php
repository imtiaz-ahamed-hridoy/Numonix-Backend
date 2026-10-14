<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGuideItem extends Model
{
    protected $table = 'cms_user_guide_items';

    protected $fillable = [
        'section_id',
        'title',
        'body',
        'images',
        'keys',
    ];

    protected $casts = [
        'images' => 'array',
        'keys' => 'array',
    ];


    public function section(): BelongsTo
    {
        return $this->belongsTo(UserGuideSection::class, 'section_id');
    }
}
