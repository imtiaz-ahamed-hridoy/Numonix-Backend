<?php

namespace App\Http\Requests\Web\Backend\EarTraining;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validates a request to create or update an ear-training Stage (Level).
 */
class StoreEarTrainingLevelRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Core
            'level'                 => 'required|in:beginner,intermediate,advance',
            'short_description'     => 'nullable|string|max:255',
            'description'           => 'nullable|string',

            // Stage display
            'accent_colour'         => 'nullable|string|max:20',
            'patterns_label'        => 'nullable|string|max:255',
            'next_stage_teaser'     => 'nullable|string|max:255',

            // Unlock chain
            'unlock_after_level_id' => 'nullable|integer|exists:ear_training_levels,id',

            // Level Complete Screen
            'completion_title'      => 'nullable|string|max:255',
            'completion_description'=> 'nullable|string',
            'motivational_quote'    => 'nullable|string|max:500',

            // Access & status
            'is_premium'            => 'nullable|boolean',
            'is_guest_accessible'   => 'nullable|in:null,true,false',
            'status'                => 'required|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'level.required'               => 'Please select a Stage.',
            'level.in'                     => 'Stage must be one of: beginner, intermediate, advance.',
            'accent_colour.max'            => 'Accent colour must not exceed 20 characters.',
            'patterns_label.max'           => 'Patterns label must not exceed 255 characters.',
            'next_stage_teaser.max'        => 'Next stage teaser must not exceed 255 characters.',
            'unlock_after_level_id.exists' => 'The selected unlock stage does not exist.',
            'completion_title.max'         => 'Completion title must not exceed 255 characters.',
            'motivational_quote.max'       => 'Motivational quote must not exceed 500 characters.',
            'is_guest_accessible.in'       => 'Guest access must be one of: null, true, false.',
            'status.required'              => 'Please select a status.',
            'status.in'                    => 'Status must be one of: draft, published.',
        ];
    }
}
