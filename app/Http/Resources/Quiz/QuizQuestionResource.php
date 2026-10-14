<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for a single quiz question.
 */
class QuizQuestionResource extends JsonResource
{
    /**
     * Transform the quiz question into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'type' => $this->type,
            'question_text' => $this->question_text,
            'image_url' => $this->image_url,
            'image_size' => $this->image_size ?? ($this->image_url ? 'medium' : null),
            'options' => $this->options,
            'correct_answer' => $this->when($request->routeIs('*.review*') || $request->boolean('show_answer'), $this->correct_answer),
            'explanation' => $this->explanation,
        ];
    }
}
