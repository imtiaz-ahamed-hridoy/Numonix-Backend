<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class UserCookieSetting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'guest_fingerprint',
        'essential_cookies',
        'analytics_cookies',
    ];

    protected $hidden = [
        'guest_fingerprint',
    ];

    protected $casts = [
        'essential_cookies' => 'boolean',
        'analytics_cookies' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
