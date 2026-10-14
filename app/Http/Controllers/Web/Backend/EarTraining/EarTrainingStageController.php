<?php

namespace App\Http\Controllers\Web\Backend\EarTraining;

use App\Http\Controllers\Controller;
use App\Models\EarTrainingStage;
use Illuminate\Http\Request;

class EarTrainingStageController extends Controller
{
    public function index()
    {
        $stages = EarTrainingStage::orderBy('sort_order')->orderBy('id')->get();
        return view('backend.layouts.ear-training.stages.index', compact('stages'));
    }

    public function create()
    {
        return view('backend.layouts.ear-training.stages.create');
    }

    private function validateStage(Request $request): array
    {
        // Parse unlock_criteria if it's a string from the textarea
        if ($request->has('unlock_criteria') && is_string($request->input('unlock_criteria'))) {
            $uc = $request->input('unlock_criteria');
            $parsed = [];
            if (trim($uc) !== '') {
                if (str_starts_with(trim($uc), '[')) {
                    $parsed = json_decode($uc, true) ?? [];
                } else {
                    $parsed = array_filter(array_map('trim', explode(',', $uc)));
                }
            }
            $request->merge(['unlock_criteria' => empty($parsed) ? null : $parsed]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'patterns_label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'accent_colour' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_locked_by_default' => 'nullable|boolean',
            'unlock_criteria' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'guest_access_override' => 'nullable|in:Use Level Rule,Unlocked,Locked',
            'next_stage_teaser' => 'nullable|string|max:255',
        ]);

        $validated['is_locked_by_default'] = $request->has('is_locked_by_default');
        $validated['is_active'] = (bool) $request->input('is_active');
        $validated['guest_access_override'] = $validated['guest_access_override'] ?? 'Use Level Rule';

        // Sync is_premium with guest_access_override for dynamic consistency
        if ($validated['guest_access_override'] === 'Locked') {
            $validated['is_premium'] = true;
        } elseif ($validated['guest_access_override'] === 'Unlocked') {
            $validated['is_premium'] = false;
        } else {
            $validated['is_premium'] = (bool) $request->input('is_premium');
        }

        return $validated;
    }

    public function store(Request $request)
    {
        $validated = $this->validateStage($request);
        EarTrainingStage::create($validated);

        return redirect()->route('admin.ear-training.stages.index')
            ->with('t-success', 'Stage created successfully.');
    }

    public function edit(EarTrainingStage $stage)
    {
        return view('backend.layouts.ear-training.stages.edit', compact('stage'));
    }

    public function update(Request $request, EarTrainingStage $stage)
    {
        $validated = $this->validateStage($request);
        $stage->update($validated);

        return redirect()->route('admin.ear-training.stages.index')
            ->with('t-success', 'Stage updated successfully.');
    }

    public function destroy(EarTrainingStage $stage)
    {
        $stage->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Stage deleted successfully.',
        ]);
    }
}
