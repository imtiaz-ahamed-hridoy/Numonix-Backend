<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class EarTrainingQuiz extends Model
{
    use LogsActivity;

    protected $fillable = [
        // Core display
        'ear_training_level_id',
        'title',
        'description',
        'order',

        // Access
        'is_premium',
        'status',

        // Generation Engine Contract
        'exercise_type',
        'function_patterns',
        'tempo_bpm',
        'midi_program',
        'chord_voicing',
        'chord_size',
        'chord_purity',
        'answer_display',
        'chord_regularity',
        'bar_length',
        'closes_on_following_one',
        'repetitions',
        'exercise_count',

        // Question prompt override
        'question_text_override',

        // Level Complete Screen
        'completion_heading',
        'completion_body',
        'completion_footer',
    ];

    protected $casts = [
        'function_patterns'       => 'array',
        'closes_on_following_one' => 'boolean',
        'is_premium'              => 'boolean',
        'order'                   => 'integer',
        'tempo_bpm'               => 'integer',
        'midi_program'            => 'integer',
        'bar_length'              => 'integer',
        'repetitions'             => 'integer',
        'exercise_count'          => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    // -- Relationships ------------------------------------------------------

    public function level(): BelongsTo
    {
        return $this->belongsTo(EarTrainingLevel::class, 'ear_training_level_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EarTrainingQuestion::class);
    }
}
