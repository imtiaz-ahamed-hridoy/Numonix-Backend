<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GuestDevice;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class QuizAttempt extends Model
{
    protected $table = 'quiz_attempts';

    protected $primaryKey = 'attempt_id';

    public $incrementing = true;

    protected $keyType = 'int';

    use LogsActivity;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'guest_device_id',
        'guest_fingerprint',
        'total_questions',
        'attempted_questions',
        'correct_answers',
        'score',
        'answers_data',
    ];

    protected $hidden = [
        'guest_fingerprint',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $casts = [
        'answers_data' => 'array',
        'score' => 'decimal:2',
        'total_questions' => 'integer',
        'attempted_questions' => 'integer',
        'correct_answers' => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function guestDevice(): BelongsTo
    {
        return $this->belongsTo(GuestDevice::class, 'guest_device_id');
    }
}
