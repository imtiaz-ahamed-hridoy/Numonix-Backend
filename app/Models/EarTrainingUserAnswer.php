<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarTrainingUserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ear_training_user_progress_id',
        'exercise_index',
        'user_answer',
        'correct_answer',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'exercise_index' => 'integer',
    ];

    public function progress(): BelongsTo
    {
        return $this->belongsTo(EarTrainingUserProgress::class, 'ear_training_user_progress_id');
    }
}
