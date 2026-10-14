<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Learning\StoreLearningGroupRequest;
use App\Models\LearningGroup;

class LearningGroupController extends Controller
{
    public function index()
    {
        $groups = LearningGroup::orderBy('category')->orderBy('id')->get();

        return view('backend.layouts.learning-group.index', compact('groups'));
    }

    public function create()
    {
        return view('backend.layouts.learning-group.create');
    }

    public function store(StoreLearningGroupRequest $request)
    {
        $validated = $request->validated();

        LearningGroup::create([
            'category' => $validated['category'],
            'label' => $validated['label'],
            'color' => $validated['color'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_premium' => $request->boolean('is_premium'),
        ]);

        return redirect()->route('learning-group.index')
            ->with('success', 'Learning Group created successfully.');
    }

    public function edit($id)
    {
        $group = LearningGroup::findOrFail($id);

        return view('backend.layouts.learning-group.edit', compact('group'));
    }

    public function update(StoreLearningGroupRequest $request, $id)
    {
        $group = LearningGroup::findOrFail($id);

        $validated = $request->validated();

        $group->update([
            'category' => $validated['category'],
            'label' => $validated['label'],
            'color' => $validated['color'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_premium' => $request->boolean('is_premium'),
        ]);

        return redirect()->route('learning-group.index')
            ->with('success', 'Learning Group updated successfully.');
    }

    public function destroy($id)
    {
        $group = LearningGroup::findOrFail($id);

        // Disassociate any learnings linked to this group
        $group->learnings()->update(['group_id' => null]);

        $group->delete();

        return redirect()->route('learning-group.index')
            ->with('success', 'Learning Group deleted successfully.');
    }
}
