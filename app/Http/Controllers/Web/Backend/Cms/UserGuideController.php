<?php

namespace App\Http\Controllers\Web\Backend\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Cms\UpdateUserGuideRequest;
use App\Models\Cms\UserGuide;
use App\Models\Cms\UserGuideItem;
use App\Models\Cms\UserGuideSection;
use Illuminate\Support\Facades\DB;

class UserGuideController extends Controller
{
    public function index()
    {
        $tabs = UserGuide::with('sections.items')->get()->keyBy('tab');

        return view('backend.layouts.cms.user-guide', compact('tabs'));
    }

    public function update(UpdateUserGuideRequest $request)
    {

        DB::beginTransaction();
        try {
            foreach ($request->tabs as $tabData) {
                $guide = UserGuide::updateOrCreate(
                    ['tab' => $tabData['tab']],
                    [
                        'heading' => $tabData['heading'],
                        'intro_text' => $tabData['intro_text'] ?? null,
                    ]
                );

                $submittedSectionIds = [];

                foreach ($tabData['sections'] ?? [] as $sIndex => $sectionData) {
                    if (! empty($sectionData['id'])) {
                        // Update existing section
                        $section = $guide->sections()->findOrFail($sectionData['id']);
                        $sectionUpdate = [];
                        if (isset($sectionData['heading'])) {
                            $sectionUpdate['heading'] = $sectionData['heading'];
                        }
                        $section->update($sectionUpdate);
                        $submittedSectionIds[] = $section->id;
                    } else {
                        // Create new section
                        $section = $guide->sections()->create([
                            'heading' => $sectionData['heading'] ?? 'New Section',
                            'id' => $sIndex + 1,
                        ]);
                        $submittedSectionIds[] = $section->id;
                    }

                    $submittedItemIds = [];
                    $items = $sectionData['items'] ?? [];

                    foreach ($items as $iIndex => $itemData) {
                        $itemDataImages = [];

                        // Preserve images that remain in the UI and append new
                        // uploads. Adding one image must not discard the rest.
                        if (isset($itemData['existing_images']) && is_array($itemData['existing_images'])) {
                            foreach ($itemData['existing_images'] as $src) {
                                $itemDataImages[] = ['src' => $src];
                            }
                        }

                        if (isset($itemData['new_images']) && is_array($itemData['new_images'])) {
                            foreach ($itemData['new_images'] as $file) {
                                if ($file->isValid()) {
                                    $ext      = strtolower($file->getClientOriginalExtension() ?: 'png');
                                    $filename = time() . '_' . uniqid() . '.' . $ext;
                                    $dir      = public_path('uploads/cms/user-guide');
                                    if (!file_exists($dir)) mkdir($dir, 0775, true);
                                    $file->move($dir, $filename);
                                    // Store full URL so it works in API responses without accessor
                                    $itemDataImages[] = ['src' => asset('uploads/cms/user-guide/' . $filename)];
                                }
                            }
                        }

                        $keys = [];
                        if (isset($itemData['keys']) && is_array($itemData['keys'])) {
                            foreach ($itemData['keys'] as $k) {
                                if (trim($k) !== '') {
                                    $keys[] = trim($k);
                                }
                            }
                        }
                        
                        $itemUpdate = [
                            'title' => $itemData['title'] ?? null,
                            'body' => $itemData['body'] ?? '',
                            'images' => empty($itemDataImages) ? null : $itemDataImages,
                            'keys' => empty($keys) ? null : $keys,
                        ];

                        if (! empty($itemData['id'])) {
                            // Update existing item
                            $item = $section->items()->findOrFail($itemData['id']);
                            $item->update($itemUpdate);
                            $submittedItemIds[] = $item->id;
                        } else {
                            // Create new item
                            $item = $section->items()->create($itemUpdate);
                            $submittedItemIds[] = $item->id;
                        }
                    }

                    // Delete orphaned items (removed from UI)
                    $section->items()
                        ->whereNotIn('id', $submittedItemIds)
                        ->delete();
                }

                // Delete orphaned sections (removed from UI)
                $guide->sections()
                    ->whereNotIn('id', $submittedSectionIds)
                    ->delete();
            }

            DB::commit();

            return redirect()->route('cms.user-guide.index')
                ->with('success', 'User Guide updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }
}
