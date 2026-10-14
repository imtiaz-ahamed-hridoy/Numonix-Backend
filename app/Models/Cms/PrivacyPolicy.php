<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrivacyPolicy extends Model
{
    protected $table = 'cms_privacy_policies';

    protected $fillable = [
        'title',
        'last_updated_label',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(PrivacyPolicySection::class, 'privacy_policy_id')->orderBy('id');
    }
}
