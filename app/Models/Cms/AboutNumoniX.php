<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AboutNumoniX extends Model
{
    protected $table = 'cms_about_numonix';

    protected $fillable = [
        'title',
        'heading',
        'intro_text',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(AboutNumoniXSection::class, 'about_numonix_id')->orderBy('id');
    }
}
