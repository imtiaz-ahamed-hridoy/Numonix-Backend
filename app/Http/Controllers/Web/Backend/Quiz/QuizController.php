<?php

namespace App\Http\Controllers\Web\Backend\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Quiz\StoreQuizRequest;
use App\Models\Learning;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Quiz::with('learning:id,title,category')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('learning', function (Quiz $quiz) {
                    return $quiz->learning
                        ? $quiz->learning->title.' ('.ucwords(str_replace('_', ' ', $quiz->learning->category)).')'
                        : '-';
                })
                ->addColumn('status', function (Quiz $quiz) {
                    return $quiz->status === 'published'
                        ? '<span class="badge bg-success">Published</span>'
                        : '<span class="badge bg-warning">Draft</span>';
                })
                ->addColumn('action', function (Quiz $quiz) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group">';
                    $buttons .= '<a href="'.route('quiz.questions.index', $quiz->id).'" class="btn btn-info text-white me-1" title="Manage Questions">
                                    <i class="fe fe-list"></i>
                                 </a>';
                    $buttons .= '<a href="'.route('quiz.edit', $quiz->id).'" class="btn btn-primary text-white me-1" title="Edit Quiz">
                                    <i class="fe fe-edit"></i>
                                 </a>';
                    $buttons .= '<a href="javascript:void(0);" onclick="showDeleteConfirm('.$quiz->id.')" class="btn btn-danger text-white" title="Delete Quiz">
                                    <i class="fe fe-trash"></i>
                                 </a>';
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.layouts.quiz.index');
    }

    public function create()
    {
        $learnings = Learning::orderBy('category')->orderBy('id')->orderBy('title')->get();

        return view('backend.layouts.quiz.create', compact('learnings'));
    }

    public function store(StoreQuizRequest $request)
    {
        $validated = $request->validated();

        Quiz::create($validated);

        return redirect()->route('quiz.index')->with('t-success', 'Quiz created successfully.');
    }

    public function edit($id)
    {
        $quiz = Quiz::findOrFail($id);
        $learnings = Learning::orderBy('category')->orderBy('id')->orderBy('title')->get();

        return view('backend.layouts.quiz.edit', compact('quiz', 'learnings'));
    }

    public function update(StoreQuizRequest $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $quiz->update($request->validated());

        return redirect()->route('quiz.index')->with('t-success', 'Quiz updated successfully.');
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return response()->json([
            't-success' => true,
            'message' => 'Quiz deleted successfully',
        ]);
    }
}
