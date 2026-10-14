<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsConditionsSection extends Model
{
    protected $table = 'cms_terms_conditions_sections';

    protected $fillable = [
        'terms_conditions_id',
        'section_number',
        'title',
        'body',
    ];

    protected $casts = [
        'section_number' => 'integer',
    ];

    public function termsConditions(): BelongsTo
    {
        return $this->belongsTo(TermsConditions::class, 'terms_conditions_id');
    }
}
