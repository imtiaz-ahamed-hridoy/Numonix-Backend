<?php

namespace App\Http\Resources\EarTraining;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for an ear training question.
 */
class EarTrainingQuestionResource extends JsonResource
{
    /**
     * Transform the ear training question into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ear_training_quiz_id' => $this->ear_training_quiz_id,
            'question_text' => $this->question_text,
            'audio_url' => $this->audio_url,
            'options' => $this->options,
            'tonic_root_hint' => strip_tags((string) ($this->tonic_root_hint ?? '')),
            'tonic_root_hint_description' => $this->tonic_root_hint_description,
            'correct_answer' => $this->when($request->routeIs('*.review*') || $request->boolean('show_answer'), $this->correct_answer),
            'correct_answer_description' => $this->correct_answer_description,
        ];
    }
}
