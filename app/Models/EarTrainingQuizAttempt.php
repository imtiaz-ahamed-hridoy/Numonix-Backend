<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GuestDevice;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class EarTrainingQuizAttempt extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'guest_device_id',
        'ear_training_quiz_id',
        'total_questions',
        'attempted_questions',
        'correct_answers',
        'score',
        'status',
        'answered_question_ids',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $casts = [
        'answered_question_ids' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(EarTrainingQuiz::class, 'ear_training_quiz_id');
    }

    public function guestDevice(): BelongsTo
    {
        return $this->belongsTo(GuestDevice::class, 'guest_device_id');
    }
}
