<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGuide extends Model
{
    protected $table = 'cms_user_guides';

    protected $fillable = [
        'tab',
        'heading',
        'intro_text',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(UserGuideSection::class, 'user_guide_id')->orderBy('id');
    }
}
