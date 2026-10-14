<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermsConditions extends Model
{
    protected $table = 'cms_terms_conditions';

    protected $fillable = [
        'title',
        'last_updated_label',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(TermsConditionsSection::class, 'terms_conditions_id')->orderBy('id');
    }
}
