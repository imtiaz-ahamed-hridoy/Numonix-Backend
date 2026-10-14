<?php

namespace App\Http\Controllers\Web\Backend\EarTraining;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\EarTraining\StoreEarTrainingQuestionRequest;
use App\Http\Requests\Web\Backend\EarTraining\UpdateEarTrainingQuestionRequest;
use App\Models\EarTrainingQuestion;
use App\Models\EarTrainingQuiz;

class EarTrainingQuestionController extends Controller
{
    public function index($quizId)
    {
        $quiz = EarTrainingQuiz::with(['questions', 'level'])->findOrFail($quizId);

        return view('backend.layouts.ear-training.questions.index', compact('quiz'));
    }

    public function create($quizId)
    {
        $quiz = EarTrainingQuiz::findOrFail($quizId);

        return view('backend.layouts.ear-training.questions.create', compact('quiz'));
    }

    public function store(StoreEarTrainingQuestionRequest $request, $quizId)
    {
        $data = $request->only(['question_text', 'tonic_root_hint', 'tonic_root_hint_description', 'correct_answer_description']);
        $data['ear_training_quiz_id'] = $quizId;
        $data['options']              = $request->mcq_options;
        $data['correct_answer']       = $request->mcq_correct;

        if ($request->hasFile('audio')) {
            $data['audio_url'] = $this->storeAudio($request->file('audio'));
        }

        EarTrainingQuestion::create($data);

        return redirect()->route('ear-training.questions.index', $quizId)->with('t-success', 'Question added successfully.');
    }

    public function edit($quizId, $id)
    {
        $quiz     = EarTrainingQuiz::findOrFail($quizId);
        $question = EarTrainingQuestion::findOrFail($id);

        return view('backend.layouts.ear-training.questions.edit', compact('quiz', 'question'));
    }

    public function update(UpdateEarTrainingQuestionRequest $request, $quizId, $id)
    {
        $question = EarTrainingQuestion::findOrFail($id);
        $data     = $request->only(['question_text', 'tonic_root_hint', 'tonic_root_hint_description', 'correct_answer_description']);
        $data['options']        = $request->mcq_options;
        $data['correct_answer'] = $request->mcq_correct;

        if ($request->hasFile('audio')) {
            $this->deletePublicFile($question->getRawOriginal('audio_url'));
            $data['audio_url'] = $this->storeAudio($request->file('audio'));
        }

        $question->update($data);

        return redirect()->route('ear-training.questions.index', $quizId)->with('t-success', 'Question updated successfully.');
    }

    public function destroy($quizId, $id)
    {
        $question = EarTrainingQuestion::findOrFail($id);
        $this->deletePublicFile($question->getRawOriginal('audio_url'));
        $question->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Question deleted successfully',
        ], 200);
    }

    /**
     * Store an audio file in public/uploads/ear-training/audio/.
     * Returns the full public URL.
     */
    private function storeAudio(\Illuminate\Http\UploadedFile $file): string
    {
        $ext      = strtolower($file->getClientOriginalExtension() ?: 'mp3');
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $dir      = public_path('uploads/ear-training/audio');

        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        $file->move($dir, $filename);

        // Full URL: https://api.example.com/uploads/ear-training/audio/xxx.mp3
        return asset('uploads/ear-training/audio/' . $filename);
    }

    /**
     * Delete a file from public/ given its full URL or relative path.
     */
    private function deletePublicFile(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $path = parse_url($url, PHP_URL_PATH) ?? '';
            $path = ltrim($path, '/');
            // Legacy storage/ prefix
            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, strlen('storage/'));
            }
        } else {
            $path = ltrim($url, '/');
        }

        $full = public_path($path);
        if (file_exists($full)) {
            @unlink($full);
        }
    }
}
