<?php

namespace App\Http\Controllers\Web\Backend\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Cms\UpdateCompleteGlossaryRequest;
use App\Models\Cms\CompleteGlossary;
use App\Models\Cms\CompleteGlossaryItem;
use App\Models\Cms\CompleteGlossarySection;
use Illuminate\Support\Facades\DB;

class CompleteGlossaryController extends Controller
{
    public function index()
    {
        $glossary = CompleteGlossary::with('sections.items')->first();

        return view('backend.layouts.cms.complete-glossary', compact('glossary'));
    }

    public function update(UpdateCompleteGlossaryRequest $request)
    {

        DB::beginTransaction();
        try {
            $glossary = CompleteGlossary::firstOrCreate([]);
            $glossary->update(['title' => $request->title]);

            // Delete old sections and recreate
            $glossary->sections()->delete();

            if (! empty($request->sections) && is_array($request->sections)) {
                foreach ($request->sections as $sIndex => $sectionData) {
                    if (empty(trim($sectionData['label'] ?? ''))) {
                        continue;
                    }
                    $section = CompleteGlossarySection::create([
                        'complete_glossary_id' => $glossary->id,
                        'label' => $sectionData['label'],
                        'id' => $sIndex + 1,
                    ]);

                    if (! empty($sectionData['items']) && is_array($sectionData['items'])) {
                        foreach ($sectionData['items'] as $iIndex => $itemData) {
                            if (empty(trim($itemData['body'] ?? ''))) {
                                continue;
                            }
                            CompleteGlossaryItem::create([
                                'section_id' => $section->id,
                                'title' => $itemData['title'] ?? null,
                                'body' => $itemData['body'],
                                'id' => $iIndex + 1,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('cms.complete-glossary.index')
                ->with('success', 'Complete Glossary updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }
}
