<?php

namespace App\Http\Controllers\Web\Backend\EarTraining;

use App\Http\Controllers\Controller;
use App\Models\EarTrainingExplanationTemplate;
use Illuminate\Http\Request;

class EarTrainingExplanationTemplateController extends Controller
{
    public function index()
    {
        $templates = EarTrainingExplanationTemplate::orderBy('type')->get();
        return view('backend.layouts.ear-training.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('backend.layouts.ear-training.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:transition,pattern,contrast,hint,prompt',
            'from_function' => 'nullable|string',
            'to_function' => 'nullable|string',
            'phrase' => 'nullable|string',
            'pattern' => 'nullable|string',
            'title' => 'nullable|string',
            'body' => 'nullable|string',
            'hint_text' => 'nullable|string',
            'display_value' => 'nullable|string',
            'exercise_type' => 'nullable|string',
            'prompt_text' => 'nullable|string',
        ]);

        EarTrainingExplanationTemplate::create($data);

        return redirect()->route('admin.ear-training.templates.index')->with('t-success', 'Template created successfully.');
    }

    public function edit(EarTrainingExplanationTemplate $template)
    {
        return view('backend.layouts.ear-training.templates.edit', compact('template'));
    }

    public function update(Request $request, EarTrainingExplanationTemplate $template)
    {
        $data = $request->validate([
            'type' => 'required|in:transition,pattern,contrast,hint,prompt',
            'from_function' => 'nullable|string',
            'to_function' => 'nullable|string',
            'phrase' => 'nullable|string',
            'pattern' => 'nullable|string',
            'title' => 'nullable|string',
            'body' => 'nullable|string',
            'hint_text' => 'nullable|string',
            'display_value' => 'nullable|string',
            'exercise_type' => 'nullable|string',
            'prompt_text' => 'nullable|string',
        ]);

        $template->update($data);

        return redirect()->route('admin.ear-training.templates.index')->with('t-success', 'Template updated successfully.');
    }

    public function destroy(EarTrainingExplanationTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.ear-training.templates.index')->with('t-success', 'Template deleted successfully.');
    }
}
