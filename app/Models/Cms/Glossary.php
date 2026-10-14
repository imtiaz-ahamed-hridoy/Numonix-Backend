<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Glossary extends Model
{
    protected $table = 'cms_glossaries';

    protected $fillable = [
        'title',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(GlossaryTerm::class, 'glossary_id')->orderBy('id');
    }
}
