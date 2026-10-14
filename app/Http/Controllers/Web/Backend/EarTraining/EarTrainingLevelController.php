<?php

namespace App\Http\Controllers\Web\Backend\EarTraining;

use App\Http\Controllers\Controller;
use App\Models\EarTrainingLevel;
use App\Models\EarTrainingStage;
use Illuminate\Http\Request;

class EarTrainingLevelController extends Controller
{
    public function index(EarTrainingStage $stage)
    {
        $levels = $stage->levels()->orderBy('level_number')->orderBy('id')->get();
        return view('backend.layouts.ear-training.levels.index', compact('stage', 'levels'));
    }

    public function create(EarTrainingStage $stage)
    {
        return view('backend.layouts.ear-training.levels.create', compact('stage'));
    }

    public function store(Request $request, EarTrainingStage $stage)
    {
        $validated = $this->validateLevel($request);
        $stage->levels()->create($validated);

        return redirect()->route('admin.ear-training.levels.index', $stage)
            ->with('t-success', 'Level created successfully.');
    }

    public function edit(EarTrainingLevel $level)
    {
        $stage = $level->stage;
        return view('backend.layouts.ear-training.levels.edit', compact('stage', 'level'));
    }

    public function update(Request $request, EarTrainingLevel $level)
    {
        $validated = $this->validateLevel($request);
        $level->update($validated);

        return redirect()->route('admin.ear-training.levels.index', $level->ear_training_stage_id)
            ->with('t-success', 'Level updated successfully.');
    }

    public function destroy(EarTrainingLevel $level)
    {
        $level->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Level deleted successfully.',
        ]);
    }

    private function validateLevel(Request $request): array
    {
        // Parse function_patterns
        if ($request->has('function_patterns')) {
            $fp = $request->input('function_patterns');
            $parsed = [];

            if (is_string($fp)) {
                $fp = trim($fp);
                if ($fp !== '') {
                    // First try to parse the entire input as a valid JSON array
                    $fullDecoded = json_decode($fp, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($fullDecoded)) {
                        $parsed = $fullDecoded;
                    } else {
                        $parseTokens = function($str) {
                            $tokens = explode(',', $str);
                            $result = [];
                            foreach ($tokens as $token) {
                                $t = trim($token);
                                if ($t === '' || strtolower($t) === 'null') {
                                    $result[] = null;
                                } else {
                                    $result[] = $t;
                                }
                            }
                            return $result;
                        };

                        if (preg_match_all('/\[(.*?)\]/', $fp, $matches)) {
                            foreach ($matches[1] as $inner) {
                                $cleanLine = str_replace(['"', "'"], '', $inner);
                                $parsed[] = $parseTokens($cleanLine);
                            }
                        } else {
                            $lines = explode("\n", $fp);
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if ($line === '') continue;
                                
                                $lineDecoded = json_decode($line, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($lineDecoded)) {
                                    $result = [];
                                    foreach ($lineDecoded as $token) {
                                        if ($token === null || (is_string($token) && trim($token) === '')) {
                                            $result[] = null;
                                        } else {
                                            $result[] = is_string($token) ? trim($token) : $token;
                                        }
                                    }
                                    $parsed[] = $result;
                                } else {
                                    $cleanLine = str_replace(['"', "'", '[', ']'], '', $line);
                                    $parsed[] = $parseTokens($cleanLine);
                                }
                            }
                        }
                    }
                }
            } elseif (is_array($fp)) {
                // Handle dynamic UI array of arrays
                foreach ($fp as $row) {
                    if (is_array($row)) {
                        $cleanedRow = [];
                        $isEmptyRow = true;
                        foreach ($row as $token) {
                            $t = is_string($token) ? trim($token) : $token;
                            if ($t === null || $t === '' || (is_string($t) && strtolower($t) === 'null')) {
                                $cleanedRow[] = null;
                            } else {
                                $cleanedRow[] = $t;
                                $isEmptyRow = false;
                            }
                        }
                        // Only keep the row if it contains at least one actual function token
                        if (!$isEmptyRow) {
                            $parsed[] = $cleanedRow;
                        }
                    }
                }
            }

            $request->merge(['function_patterns' => empty($parsed) ? null : $parsed]);
        }

        $validated = $request->validate([
            'level_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'guest_access_override' => 'nullable|in:Use Level Rule,Unlocked,Locked',
            
            // Generation parameters
            'exercise_type' => 'required|in:identify_function,identify_progression',
            'function_patterns' => 'nullable|array|min:4',
            'function_patterns.*' => 'array',
            'function_patterns.*.*' => ['nullable', 'string', \Illuminate\Validation\Rule::in([
                'T', 'S', 'D', 'T#', 'S#', 'D#', 'd#', 'Db', 'd#T', 'd#S', 'd#D', 'd#T#', 'd#S#', 'd#D#'
            ])],
            'tempo_bpm' => 'required|integer|min:30|max:300',
            'midi_program' => 'required|integer|min:0|max:127',
            'chord_voicing' => 'required|in:block,arpeggiated',
            'chord_size' => 'required|in:3,4,5',
            'chord_purity' => 'required|in:pure,mixed,any',
            'answer_display' => 'required|in:leadership_only,with_influences',
            'chord_regularity' => 'required|in:regular,irregular,any',
            'bar_length' => 'required|integer|min:1|max:16',
            'closes_on_following_one' => 'nullable|boolean',
            'repetitions' => 'required|integer|min:1|max:10',
            'exercise_count' => 'required|integer|min:1|max:100',
            'question_text_override' => 'nullable|string|max:255',
            
            // Completion
            'completion_heading' => 'nullable|string|max:255',
            'completion_body' => 'nullable|string',
            'completion_footer' => 'nullable|string|max:255',
        ], [
            'function_patterns.*.*.in' => 'Invalid function token. Tokens are case-sensitive. Valid set: T, S, D, T#, S#, D#, d#, Db, d#T, d#S, d#D, d#T#, d#S#, d#D#, null.'
        ]);

        $validated['is_active'] = (bool) $request->input('is_active');
        $validated['closes_on_following_one'] = $request->has('closes_on_following_one');
        $validated['guest_access_override'] = $validated['guest_access_override'] ?? 'Use Level Rule';

        // Sync is_premium with guest_access_override for dynamic consistency
        // If admin explicitly locks/unlocks via override, force is_premium accordingly
        if ($validated['guest_access_override'] === 'Locked') {
            $validated['is_premium'] = true;
        } elseif ($validated['guest_access_override'] === 'Unlocked') {
            $validated['is_premium'] = false;
        } else {
            // Use Level Rule -> respect the checkbox
            $validated['is_premium'] = $request->has('is_premium');
        }
        
        return $validated;
    }
}
