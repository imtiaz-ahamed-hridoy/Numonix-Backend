<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutNumoniXItem extends Model
{
    protected $table = 'cms_about_numonix_items';

    protected $fillable = [
        'section_id',
        'title',
        'body',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(AboutNumoniXSection::class, 'section_id');
    }
}
