<?php

namespace App\Http\Controllers\Web\Backend\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Cms\UpdateAboutNumoniXRequest;
use App\Models\Cms\AboutNumoniX;
use App\Models\Cms\AboutNumoniXItem;
use App\Models\Cms\AboutNumoniXSection;
use Illuminate\Support\Facades\DB;

class AboutNumoniXController extends Controller
{
    public function index()
    {
        $about = AboutNumoniX::with('sections.items')->first();

        return view('backend.layouts.cms.about-numonix', compact('about'));
    }

    public function update(UpdateAboutNumoniXRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $about = AboutNumoniX::firstOrFail();

            // Update title & heading
            $about->update([
                'title' => $validated['title'],
                'heading' => $validated['heading'],
            ]);

            $submittedSectionIds = [];

            foreach (($validated['sections'] ?? []) as $sIndex => $sectionData) {
                if (! empty($sectionData['id'])) {
                    // Update existing section
                    $section = AboutNumoniXSection::findOrFail($sectionData['id']);
                    $section->update([
                        'heading' => $sectionData['heading'],
                        'description' => $sectionData['description'] ?? null,
                        'id' => $sIndex + 1,
                    ]);
                    $submittedSectionIds[] = $section->id;
                } else {
                    // Create new section
                    $section = $about->sections()->create([
                        'heading' => $sectionData['heading'] ?? 'New Section',
                        'description' => $sectionData['description'] ?? null,
                        'id' => $sIndex + 1,
                    ]);
                    $submittedSectionIds[] = $section->id;
                }

                $submittedItemIds = [];
                $items = $sectionData['items'] ?? [];

                foreach ($items as $iIndex => $itemData) {
                    if (! empty($itemData['id'])) {
                        // Update existing item
                        $item = AboutNumoniXItem::findOrFail($itemData['id']);
                        $item->update([
                            'title' => $itemData['title'] ?? null,
                            'body' => $itemData['body'] ?? '',
                            'id' => $iIndex + 1,
                        ]);
                        $submittedItemIds[] = $item->id;
                    } else {
                        // Create new item
                        $item = $section->items()->create([
                            'title' => $itemData['title'] ?? null,
                            'body' => $itemData['body'] ?? '',
                            'id' => $iIndex + 1,
                        ]);
                        $submittedItemIds[] = $item->id;
                    }
                }

                // Delete orphaned items (removed from UI)
                $section->items()
                    ->whereNotIn('id', $submittedItemIds)
                    ->delete();
            }

            // Delete orphaned sections (removed from UI)
            $about->sections()
                ->whereNotIn('id', $submittedSectionIds)
                ->delete();

            DB::commit();

            return redirect()->route('cms.about-numonix.index')
                ->with('success', 'About NumoniX updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('t-error', 'Failed to update About NumoniX. Please try again.')
                ->withInput();
        }
    }
}
