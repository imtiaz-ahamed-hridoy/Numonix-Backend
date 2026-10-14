<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class EarTrainingQuestion extends Model
{
    use LogsActivity;

    protected $fillable = [
        'ear_training_quiz_id',
        'question_text',
        'audio_url',
        'tonic_root_hint',
        'tonic_root_hint_description',
        'options',
        'correct_answer',
        'correct_answer_description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $casts = [
        'options' => 'json',
        'correct_answer' => 'json',
    ];

    public function getAudioUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return url($value);
        }
        return $value;
    }


    public function quiz(): BelongsTo
    {
        return $this->belongsTo(EarTrainingQuiz::class, 'ear_training_quiz_id');
    }
}
