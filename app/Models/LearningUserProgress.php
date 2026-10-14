<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GuestDevice;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class LearningUserProgress extends Model
{
    use LogsActivity;

    protected $table = 'learning_user_progress';

    protected $fillable = [
        'user_id',
        'learning_id',
        'guest_device_id',
        'guest_fingerprint',
        'is_completed',
    ];

    protected $casts = [
        'is_completed'     => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learning(): BelongsTo
    {
        return $this->belongsTo(Learning::class, 'learning_id');
    }

    public function guestDevice(): BelongsTo
    {
        return $this->belongsTo(GuestDevice::class, 'guest_device_id');
    }
}
