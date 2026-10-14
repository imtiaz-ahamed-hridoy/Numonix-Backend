<?php

namespace App\Http\Controllers\Web\Backend\EarTraining;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\EarTraining\StoreEarTrainingQuizRequest;
use App\Models\EarTrainingLevel;
use App\Models\EarTrainingQuiz;

class EarTrainingQuizController extends Controller
{
    public function index($levelId)
    {
        $level = EarTrainingLevel::with('quizzes')->findOrFail($levelId);

        return view('backend.layouts.ear-training.quizzes.index', compact('level'));
    }

    public function store(StoreEarTrainingQuizRequest $request, $levelId)
    {
        EarTrainingLevel::findOrFail($levelId); // Guard: ensure level exists

        EarTrainingQuiz::create(array_merge(
            $this->buildAttributes($request),
            ['ear_training_level_id' => $levelId]
        ));

        return redirect()->route('ear-training.quizzes.index', $levelId)
            ->with('t-success', 'Level created successfully.');
    }

    public function edit($id)
    {
        $quiz = EarTrainingQuiz::findOrFail($id);

        return view('backend.layouts.ear-training.quizzes.edit', compact('quiz'));
    }

    public function update(StoreEarTrainingQuizRequest $request, $id)
    {
        $quiz = EarTrainingQuiz::findOrFail($id);
        $quiz->update($this->buildAttributes($request));

        return redirect()->route('ear-training.quizzes.index', $quiz->ear_training_level_id)
            ->with('t-success', 'Level updated successfully.');
    }

    public function destroy($id)
    {
        EarTrainingQuiz::findOrFail($id)->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Level deleted successfully.',
        ]);
    }

    // -- Private helpers ----------------------------------------------------

    /**
     * Build the attributes array for store/update from the validated request.
     * function_patterns is submitted as a newline-separated string from the
     * textarea in the CMS form; we split + clean it before JSON storage.
     */
    private function buildAttributes(StoreEarTrainingQuizRequest $request): array
    {
        // function_patterns: accept JSON array or newline-separated text from the form
        $patterns = null;
        $rawPatterns = $request->input('function_patterns_text');
        
        if ($rawPatterns) {
            $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $rawPatterns));
            $patterns = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line) {
                    $decoded = json_decode($line, true);
                    // If it's valid JSON array, store as array; else store raw string
                    $patterns[] = is_array($decoded) ? $decoded : $line;
                }
            }
        } elseif ($request->has('function_patterns') && is_array($request->function_patterns)) {
            $patterns = $request->function_patterns;
        }

        return [
            // Core display
            'title'                   => $request->title,
            'description'             => $request->description,
            'order'                   => $request->input('order', 0),

            // Access
            'is_premium'              => $request->boolean('is_premium'),
            'status'                  => $request->status,

            // Generation Engine Contract
            'exercise_type'           => $request->input('exercise_type', 'identify_function'),
            'function_patterns'       => $patterns,
            'tempo_bpm'               => $request->input('tempo_bpm', 80),
            'midi_program'            => $request->input('midi_program', 0),
            'chord_voicing'           => $request->input('chord_voicing', 'block'),
            'chord_size'              => $request->input('chord_size', '3'),
            'chord_purity'            => $request->input('chord_purity', 'pure'),
            'answer_display'          => $request->input('answer_display', 'leadership_only'),
            'chord_regularity'        => $request->input('chord_regularity', 'regular'),
            'bar_length'              => $request->input('bar_length', 4),
            'closes_on_following_one' => $request->boolean('closes_on_following_one'),
            'repetitions'             => $request->input('repetitions', 1),
            'exercise_count'          => $request->input('exercise_count', 10),

            // Prompt override
            'question_text_override'  => $request->question_text_override ?: null,

            // Level Complete Screen
            'completion_heading'      => $request->completion_heading,
            'completion_body'         => $request->completion_body,
            'completion_footer'       => $request->completion_footer,
        ];
    }
}
