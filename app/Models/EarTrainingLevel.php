<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EarTrainingLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'ear_training_stage_id',
        'level_number',
        'title',
        'description',
        'is_active',
        'is_premium',
        'guest_access_override',
        
        // Generation parameters
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
        'question_text_override',
        
        // Completion content
        'completion_heading',
        'completion_body',
        'completion_footer',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'function_patterns' => 'array',
        'tempo_bpm' => 'integer',
        'midi_program' => 'integer',
        'chord_size' => 'integer',
        'bar_length' => 'integer',
        'closes_on_following_one' => 'boolean',
        'repetitions' => 'integer',
        'exercise_count' => 'integer',
        'level_number' => 'integer',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EarTrainingStage::class, 'ear_training_stage_id');
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(EarTrainingUserProgress::class);
    }
}
