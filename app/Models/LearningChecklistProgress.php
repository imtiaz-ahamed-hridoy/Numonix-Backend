<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GuestDevice;

class LearningChecklistProgress extends Model
{
    protected $table = 'learning_checklist_progress';

    protected $fillable = [
        'user_id',
        'element_id',
        'guest_device_id',
        'guest_fingerprint',
        'checked_items',
    ];

    protected $casts = [
        'checked_items' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(LearningPageElement::class, 'element_id');
    }

    public function guestDevice(): BelongsTo
    {
        return $this->belongsTo(GuestDevice::class, 'guest_device_id');
    }
}
