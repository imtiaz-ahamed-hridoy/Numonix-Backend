<?php

namespace App\Http\Requests\Web\Backend\EarTraining;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validates a request to create or update an ear-training Level (Quiz).
 */
class StoreEarTrainingQuizRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $rawPatterns = $this->input('function_patterns_text');
        
        if ($rawPatterns) {
            $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $rawPatterns));
            $patterns = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line) {
                    $decoded = json_decode($line, true);
                    $patterns[] = is_array($decoded) ? $decoded : [$line];
                }
            }
            $this->merge([
                'function_patterns' => $patterns,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // Core display
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'order'                   => 'nullable|integer|min:0',

            // Access
            'is_premium'              => 'boolean',
            'status'                  => 'required|in:draft,published',

            // Generation Engine Contract
            'exercise_type'           => 'nullable|in:identify_function,identify_progression',
            'function_patterns'       => 'nullable|array|min:4',
            'function_patterns.*'     => 'array',
            'function_patterns.*.*'   => ['nullable', 'string', \Illuminate\Validation\Rule::in([
                'T', 'S', 'D', 'T#', 'S#', 'D#', 'd#', 'Db', 'd#T', 'd#S', 'd#D', 'd#T#', 'd#S#', 'd#D#'
            ])],
            'tempo_bpm'               => 'nullable|integer|min:40|max:300',
            'midi_program'            => 'nullable|integer|min:0|max:127',
            'chord_voicing'           => 'nullable|in:block,arpeggiated',
            'chord_size'              => 'nullable|in:3,4,5',
            'chord_purity'            => 'nullable|in:pure,mixed,any',
            'answer_display'          => 'nullable|in:leadership_only,with_influences',
            'chord_regularity'        => 'nullable|in:regular,irregular,any',
            'bar_length'              => 'nullable|integer|min:2|max:16',
            'closes_on_following_one' => 'boolean',
            'repetitions'             => 'nullable|integer|min:1|max:8',
            'exercise_count'          => 'nullable|integer|min:1|max:100',

            // Prompt override
            'question_text_override'  => 'nullable|string|max:255',

            // Level Complete Screen
            'completion_heading'      => 'nullable|string|max:255',
            'completion_body'         => 'nullable|string',
            'completion_footer'       => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'         => 'Please enter a Level title.',
            'title.max'              => 'Title must not exceed 255 characters.',
            'status.required'        => 'Please select a status.',
            'status.in'              => 'Status must be one of: draft, published.',
            'exercise_type.in'       => 'Exercise type must be identify_function or identify_progression.',
            'tempo_bpm.min'          => 'Tempo must be at least 40 BPM.',
            'tempo_bpm.max'          => 'Tempo must not exceed 300 BPM.',
            'midi_program.min'       => 'MIDI program must be between 0 and 127.',
            'midi_program.max'       => 'MIDI program must be between 0 and 127.',
            'bar_length.min'         => 'Bar length must be at least 2.',
            'bar_length.max'         => 'Bar length must not exceed 16.',
            'repetitions.min'        => 'Repetitions must be at least 1.',
            'exercise_count.min'     => 'Exercise count must be at least 1.',
            'function_patterns.*.*.in' => 'Invalid function token. Tokens are case-sensitive. Valid set: T, S, D, T#, S#, D#, d#, Db, d#T, d#S, d#D, d#T#, d#S#, d#D#, null.',
        ];
    }
}
