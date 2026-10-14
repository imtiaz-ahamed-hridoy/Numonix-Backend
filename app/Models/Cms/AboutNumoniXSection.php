<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AboutNumoniXSection extends Model
{
    protected $table = 'cms_about_numonix_sections';

    protected $fillable = [
        'about_numonix_id',
        'heading',
        'description',
    ];


    public function aboutNumoniX(): BelongsTo
    {
        return $this->belongsTo(AboutNumoniX::class, 'about_numonix_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AboutNumoniXItem::class, 'section_id')->orderBy('id');
    }
}
