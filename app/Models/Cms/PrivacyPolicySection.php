<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyPolicySection extends Model
{
    protected $table = 'cms_privacy_policy_sections';

    protected $fillable = [
        'privacy_policy_id',
        'section_number',
        'title',
        'body',
    ];

    protected $casts = [
        'section_number' => 'integer',
    ];

    public function privacyPolicy(): BelongsTo
    {
        return $this->belongsTo(PrivacyPolicy::class, 'privacy_policy_id');
    }
}
