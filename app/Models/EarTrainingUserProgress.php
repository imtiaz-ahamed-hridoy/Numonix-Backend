<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarTrainingUserProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_device_id',
        'ear_training_level_id',
        'total_exercises',
        'completed_exercises',
        'correct_answers',
        'score_percentage',
        'attempts',
        'status', // not_started, completed
    ];

    protected $casts = [
        'total_exercises' => 'integer',
        'completed_exercises' => 'integer',
        'correct_answers' => 'integer',
        'score_percentage' => 'integer',
        'attempts' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guestDevice(): BelongsTo
    {
        return $this->belongsTo(GuestDevice::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(EarTrainingLevel::class, 'ear_training_level_id');
    }
}
