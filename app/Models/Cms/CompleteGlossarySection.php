<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompleteGlossarySection extends Model
{
    protected $table = 'cms_complete_glossary_sections';

    protected $fillable = [
        'complete_glossary_id',
        'label',
    ];


    public function completeGlossary(): BelongsTo
    {
        return $this->belongsTo(CompleteGlossary::class, 'complete_glossary_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CompleteGlossaryItem::class, 'section_id')->orderBy('id');
    }
}
