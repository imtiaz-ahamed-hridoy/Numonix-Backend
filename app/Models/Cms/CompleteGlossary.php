<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompleteGlossary extends Model
{
    protected $table = 'cms_complete_glossaries';

    protected $fillable = [
        'title',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(CompleteGlossarySection::class, 'complete_glossary_id')->orderBy('id');
    }
}
