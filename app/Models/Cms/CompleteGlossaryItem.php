<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompleteGlossaryItem extends Model
{
    protected $table = 'cms_complete_glossary_items';

    protected $fillable = [
        'section_id',
        'title',
        'body',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CompleteGlossarySection::class, 'section_id');
    }
}
