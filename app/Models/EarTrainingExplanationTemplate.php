<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EarTrainingExplanationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'from_function',
        'to_function',
        'phrase',
        'pattern',
        'title',
        'body',
        'hint_text',
        'display_value',
        'exercise_type',
        'prompt_text',
    ];
}
