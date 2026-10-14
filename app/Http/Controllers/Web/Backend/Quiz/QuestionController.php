<?php

namespace App\Http\Controllers\Web\Backend\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Quiz\StoreQuestionRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;

class QuestionController extends Controller
{
    public function index($quizId)
    {
        $quiz = Quiz::with(['learning', 'questions'])->findOrFail($quizId);

        return view('backend.layouts.quiz.questions.index', compact('quiz'));
    }

    public function create($quizId)
    {
        $quiz = Quiz::with('learning')->findOrFail($quizId);

        return view('backend.layouts.quiz.questions.create', compact('quiz'));
    }

    public function store(StoreQuestionRequest $request, $quizId)
    {
        $data = $request->only(['type', 'question_text', 'image_size']);
        $data['quiz_id'] = $quizId;

        // Handle Image - store in public/uploads/quizzes/questions/
        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeQuizImage($request->file('image'));
        }

        // Handle Type Specific Data
        if ($request->type === 'mcq') {
            $data['options'] = $request->mcq_options;
            $data['correct_answer'] = $request->mcq_correct;
        } elseif ($request->type === 'tap_order') {
            $data['options'] = $request->tap_items;
            $tapOrder = $request->tap_order;
            asort($tapOrder);
            $data['correct_answer'] = array_keys($tapOrder);
        }

        QuizQuestion::create($data);

        return redirect()->route('quiz.questions.index', $quizId)->with('t-success', 'Question added successfully.');
    }

    public function edit($quizId, $id)
    {
        $quiz = Quiz::with('learning')->findOrFail($quizId);
        $question = $quiz->questions()->findOrFail($id);

        return view('backend.layouts.quiz.questions.edit', compact('quiz', 'question'));
    }

    public function update(StoreQuestionRequest $request, $quizId, $id)
    {
        $quiz = Quiz::findOrFail($quizId);
        $question = $quiz->questions()->findOrFail($id);
        $data = $request->only(['type', 'question_text', 'image_size']);

        if ($request->hasFile('image')) {
            // Delete old image from public/
            $this->deleteQuizImage($question->getRawOriginal('image_url'));
            $data['image_url'] = $this->storeQuizImage($request->file('image'));
        }

        if ($request->type === 'mcq') {
            $data['options'] = $request->mcq_options;
            $data['correct_answer'] = $request->mcq_correct;
        } elseif ($request->type === 'tap_order') {
            $data['options'] = $request->tap_items;
            $tapOrder = $request->tap_order;
            asort($tapOrder);
            $data['correct_answer'] = array_keys($tapOrder);
        }

        $question->update($data);

        return redirect()->route('quiz.questions.index', $quizId)->with('t-success', 'Question updated successfully.');
    }

    public function destroy($quizId, $id)
    {
        $quiz = Quiz::findOrFail($quizId);
        $question = $quiz->questions()->findOrFail($id);

        $this->deleteQuizImage($question->getRawOriginal('image_url'));
        $question->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Question deleted successfully',
        ], 200);
    }

    /**
     * Store a quiz image in public/uploads/quizzes/questions/.
     * Returns the FULL URL so it can be stored directly in image_url column.
     */
    private function storeQuizImage(\Illuminate\Http\UploadedFile $file): string
    {
        $ext      = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $dir      = public_path('uploads/quizzes/questions');

        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        $file->move($dir, $filename);

        // Return full URL: https://api.example.com/uploads/quizzes/questions/xxx.png
        return asset('uploads/quizzes/questions/' . $filename);
    }

    /**
     * Delete a quiz image from public/.
     * Handles both full URLs and legacy storage/ paths.
     */
    private function deleteQuizImage(?string $imageUrl): void
    {
        if (empty($imageUrl)) {
            return;
        }

        // Extract the path component from the full URL
        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $path = parse_url($imageUrl, PHP_URL_PATH) ?? '';
            $path = ltrim($path, '/');

            // Legacy: was /storage/quizzes/... - strip /storage prefix
            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, strlen('storage/'));
            }
        } else {
            $path = ltrim($imageUrl, '/');
        }

        $full = public_path($path);
        if (file_exists($full)) {
            @unlink($full);
        }
    }
}
