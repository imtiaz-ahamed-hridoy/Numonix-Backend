<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class UserNotificationSetting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'lesson_updates',
        'practice_reminders',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications'  => 'boolean',
        'lesson_updates'      => 'boolean',
        'practice_reminders'  => 'boolean',
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
