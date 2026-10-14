<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class QuizQuestion extends Model
{
    use LogsActivity;

    protected $fillable = [
        'quiz_id',
        'type', // mcq, tap_order
        'question_text',
        'image_url',
        'image_size',
        'options',
        'correct_answer',
        'explanation',
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

    public function getImageUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return url($value);
        }
        return $value;
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
