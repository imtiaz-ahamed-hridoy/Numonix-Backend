<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Learning\StoreLearningRequest;
use App\Models\Learning;
use App\Models\LearningGroup;
use App\Models\LearningPage;
use App\Models\LearningPageElement;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Services\SentenceVisual\SentenceVisualService;
use App\Traits\ElementDataNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LearningController extends Controller
{
    use ElementDataNormalizer;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Learning::query()
                ->select([
                    'id',
                    'category',
                    'title',
                    'slug',
                    'status',
                    'is_published',
                    'is_premium',
                    'thumbnail_image_url',
                    'created_at',
                ])
                ->orderBy('id', 'ASC');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('category', function (Learning $learning) {
                    return ucwords(str_replace('_', ' ', $learning->category));
                })
                ->editColumn('is_published', function (Learning $learning) {
                    return $learning->is_published
                        ? "<span class='badge bg-success'>Yes</span>"
                        : "<span class='badge bg-secondary'>No</span>";
                })
                ->editColumn('is_premium', function (Learning $learning) {
                    return $learning->is_premium
                        ? "<span class='badge bg-danger'><i class='fa fa-lock'></i> Premium</span>"
                        : "<span class='badge bg-success'><i class='fa fa-unlock'></i> Tier 1</span>";
                })
                ->editColumn('status', function (Learning $learning) {
                    return match ($learning->status) {
                        'active' => "<span class='badge bg-success'>Active</span>",
                        'coming_soon' => "<span class='badge bg-warning'>Coming Soon</span>",
                        default => "<span class='badge bg-danger'>Inactive</span>",
                    };
                })
                ->addColumn('action', function (Learning $learning) {
                    $editUrl = route('learning.edit', $learning->id);

                    $buttons = '<div class="btn-group btn-group-sm" role="group" aria-label="Actions">';
                    $buttons .= '<a href="'.$editUrl.'" class="btn btn-primary fs-14 text-white" title="Edit">
                                    <i class="fe fe-edit"></i>
                                 </a>';
                    $buttons .= '<a href="#" onclick="showDeleteConfirm('.$learning->id.')" class="btn btn-danger fs-14 text-white" title="Delete">
                                    <i class="fe fe-trash"></i>
                                 </a>';
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['status', 'action', 'is_published', 'is_premium'])
                ->make(true);
        }

        return view('backend.layouts.learning.index');
    }

    public function create()
    {
        $learning = null;
        $elementTypes = LearningPageElement::TYPES;
        $groups = LearningGroup::query()->orderBy('category', 'asc')->orderBy('id', 'asc')->get();

        return view('backend.layouts.learning.create', compact('learning', 'elementTypes', 'groups'));
    }

    /**
     * Store a new learning in the database.
     *
     * @param  StoreLearningRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreLearningRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $data = [
                'category' => $validated['category'],
                'title' => $validated['title'],
                'subtitle' => $validated['subtitle'] ?? null,
                'read_time' => $validated['read_time'] ?? null,
                'summary' => $validated['summary'] ?? null,
                'status' => $validated['status'],
                'is_published' => $request->boolean('is_published'),
            ];

            $this->applyGuestAccessOverride($validated['guest_access_override'] ?? 'null', $data, $request);
            $data['group_id'] = $validated['group_id'] ?? null;

            if (! empty($validated['slug'])) {
                $slug = Str::slug($validated['slug']);
            } else {
                $slug = Str::slug($validated['title']);
            }

            $originalSlug = $slug;
            $counter = 1;
            while (Learning::where('slug', '=', $slug)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }
            $data['slug'] = $slug;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail_image_url'] = $this->storePublicFile($request->file('thumbnail'), 'learnings/thumbnails');
            }

            $learning = Learning::create($data);

            $pagesInput = $request->input('pages');
            if (! empty($pagesInput) && is_array($pagesInput)) {
                $this->savePagesAndElements($learning, $pagesInput, $request);
            }

            DB::commit();

            if ($learning->is_published) {
                $users = User::where('status', '=', 'active')->get(['*']);
                Notification::send($users, new AppNotification(
                    'New Lesson Available!',
                    "A new lesson '{$learning->title}' has been added.",
                    ['learning_id' => $learning->id],
                    'new_learning'
                ));
            }

            // --- Post-save validation: check sentence_visual elements -----
            $warnings = $this->validateSentenceVisualElements($learning);

            $redirect = redirect()->route('learning.index')
                ->with('success', 'Learning created successfully.');

            if (! empty($warnings)) {
                $redirect->with('warning', implode(' | ', $warnings));
            }

            return $redirect;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Show edit form for a learning item.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function edit(int|string $id)
    {
        $learning = Learning::with(['pages.elements'])->findOrFail($id);
        $elementTypes = LearningPageElement::TYPES;
        $groups = LearningGroup::query()->orderBy('category', 'asc')->orderBy('id', 'asc')->get();

        return view('backend.layouts.learning.edit', compact('learning', 'elementTypes', 'groups'));
    }

    /**
     * Redirect show view to edit view.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(int|string $id)
    {
        return redirect()->route('learning.edit', $id);
    }

    /**
     * Update an existing learning in the database.
     *
     * @param  StoreLearningRequest  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(StoreLearningRequest $request, int|string $id)
    {
        $learning = Learning::findOrFail($id);
        $wasPublished = $learning->is_published;

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $data = [
                'category' => $validated['category'],
                'title' => $validated['title'],
                'subtitle' => $validated['subtitle'] ?? null,
                'read_time' => $validated['read_time'] ?? null,
                'summary' => $validated['summary'] ?? null,
                'status' => $validated['status'],
                'is_published' => $request->boolean('is_published'),
                'group_id' => $validated['group_id'] ?? $learning->group_id,
            ];

            $this->applyGuestAccessOverride($validated['guest_access_override'] ?? 'null', $data, $request);

            if (! empty($validated['slug'])) {
                $slug = Str::slug($validated['slug']);
            } else {
                $slug = Str::slug($validated['title']);
            }

            $originalSlug = $slug;
            $counter = 1;
            while (Learning::where('slug', '=', $slug)->where('id', '!=', $learning->id)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }
            $data['slug'] = $slug;

            if ($request->hasFile('thumbnail')) {
                $this->deletePublicFile($learning->getRawOriginal('thumbnail_image_url'));
                $data['thumbnail_image_url'] = $this->storePublicFile($request->file('thumbnail'), 'learnings/thumbnails');
            }

            $learning->update($data);

            $pagesInput = $request->input('pages');
            if (! empty($pagesInput) && is_array($pagesInput)) {
                $this->syncPagesAndElements($learning, $pagesInput, $request);
            } else {
                // Remove all if empty
                foreach ($learning->pages as $page) {
                    foreach ($page->elements as $element) {
                        $this->deletePublicFile($element->getRawOriginal('file_url'));
                    }
                    $page->delete();
                }
            }

            DB::commit();

            if (! $wasPublished && $learning->is_published) {
                $users = User::where('status', '=', 'active')->get(['*']);
                Notification::send($users, new AppNotification(
                    'New Lesson Available!',
                    "A new lesson '{$learning->title}' has been published.",
                    ['learning_id' => $learning->id],
                    'new_learning'
                ));
            }

            // --- Post-save validation: check sentence_visual elements -----
            $warnings = $this->validateSentenceVisualElements($learning);

            $redirect = redirect()->route('learning.index')
                ->with('success', 'Learning updated successfully.');

            if (! empty($warnings)) {
                $redirect->with('warning', implode(' | ', $warnings));
            }

            return $redirect;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Delete a learning and its associated pages and elements.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy(int|string $id)
    {
        $learning = Learning::with('pages.elements')->findOrFail($id);

        $this->deletePublicFile($learning->getRawOriginal('thumbnail_image_url'));

        foreach ($learning->pages as $page) {
            foreach ($page->elements as $element) {
                $this->deletePublicFile($element->getRawOriginal('file_url'));
            }
        }

        $learning->delete();

        if (request()->ajax()) {
            return response()->json([
                't-success' => true,
                'message' => 'Learning deleted successfully.',
            ]);
        }

        return redirect()->route('learning.index')->with('success', 'Learning deleted successfully.');
    }

    /**
     * Apply the guest_access_override three-state logic to the data array.
     *
     * Syncing is_guest_accessible with is_premium, matching the
     * GuestAccessController::toggleItem() logic.
     *
     * @param  string  $guestOverride  The override value ('null', 'true', 'false')
     * @param  array  $data  The data array to modify (by reference)
     * @param  Request  $request  The current request (for is_premium fallback)
     */
    private function applyGuestAccessOverride(string $guestOverride, array &$data, Request $request): void
    {
        if ($guestOverride === 'true') {
            $data['is_guest_accessible'] = true;
            $data['is_premium'] = false;
        } elseif ($guestOverride === 'false') {
            $data['is_guest_accessible'] = false;
            $data['is_premium'] = true;
        } else {
            $data['is_guest_accessible'] = null;
            $data['is_premium'] = $request->has('is_premium') ? $request->boolean('is_premium') : null;
        }
    }

    private function savePagesAndElements(Learning $learning, array $pages, Request $request): void
    {
        $pageOrder = 1;

        foreach ($pages as $pIndex => $pageData) {
            if (empty($pageData['elements']) && empty($pageData['title']) && empty($pageData['section_title'])) {
                continue;
            }

            $page = LearningPage::create([
                'learning_id'    => $learning->id,
                'page_number'    => $pageOrder++,
                'title'          => $pageData['title'] ?? null,
                'section_title'  => $pageData['section_title'] ?? null,
            ]);

            if (isset($pageData['elements']) && is_array($pageData['elements'])) {
                $elementOrder = 1;
                foreach ($pageData['elements'] as $eIndex => $elementData) {
                    if (! $this->isAllowedElementForLearning($learning, $elementData)) {
                        continue;
                    }

                    $position = isset($elementData['position']) && is_numeric($elementData['position'])
                        ? (int) $elementData['position']
                        : $elementOrder++;

                    LearningPageElement::create($this->buildElementAttributes(
                        $page,
                        $elementData,
                        $request,
                        $pIndex,
                        $eIndex,
                        null,
                        $position
                    ));
                }
            }
        }
    }

    private function syncPagesAndElements(Learning $learning, array $pages, Request $request): void
    {
        $learning->load('pages.elements');

        $existingPages = $learning->pages->keyBy('id');
        $allExistingElements = $learning->pages->flatMap->elements->keyBy('id');
        $seenPageIds = [];
        $allSeenElementIds = [];
        $pageOrder = 1;

        foreach ($pages as $pIndex => $pageData) {
            if (empty($pageData['elements']) && empty($pageData['title']) && empty($pageData['section_title'])) {
                continue;
            }

            $page = null;
            $pageId = isset($pageData['id']) ? (int) $pageData['id'] : null;
            if ($pageId && $existingPages->has($pageId)) {
                $page = $existingPages->get($pageId);
                $page->update([
                    'page_number'    => $pageOrder++,
                    'title'          => $pageData['title'] ?? null,
                    'section_title'  => $pageData['section_title'] ?? null,
                ]);
            } else {
                $page = LearningPage::create([
                    'learning_id'    => $learning->id,
                    'page_number'    => $pageOrder++,
                    'title'          => $pageData['title'] ?? null,
                    'section_title'  => $pageData['section_title'] ?? null,
                ]);
            }

            $seenPageIds[] = $page->id;
            $elementOrder = 1;

            foreach (($pageData['elements'] ?? []) as $eIndex => $elementData) {
                if (! $this->isAllowedElementForLearning($learning, $elementData)) {
                    continue;
                }

                $element = null;
                $elementId = isset($elementData['id']) ? (int) $elementData['id'] : null;
                if ($elementId && $allExistingElements->has($elementId)) {
                    $element = $allExistingElements->get($elementId);
                }

                $position = isset($elementData['position']) && is_numeric($elementData['position'])
                    ? (int) $elementData['position']
                    : $elementOrder++;

                $attributes = $this->buildElementAttributes(
                    $page,
                    $elementData,
                    $request,
                    $pIndex,
                    $eIndex,
                    $element,
                    $position
                );

                if ($element) {
                    $element->update($attributes);
                    $allSeenElementIds[] = $element->id;
                } else {
                    $createdElement = LearningPageElement::create($attributes);
                    $allSeenElementIds[] = $createdElement->id;
                }
            }
        }

        // Clean up elements that are no longer present on any page
        foreach ($allExistingElements as $existingElement) {
            if (! in_array($existingElement->id, $allSeenElementIds, true)) {
                $this->deletePublicFile($existingElement->getRawOriginal('file_url'));
                $existingElement->delete();
            }
        }

        // Clean up pages that were removed
        foreach ($existingPages as $existingPage) {
            if (! in_array($existingPage->id, $seenPageIds, true)) {
                foreach ($existingPage->elements as $element) {
                    if (! in_array($element->id, $allSeenElementIds, true)) {
                        $this->deletePublicFile($element->getRawOriginal('file_url'));
                    }
                }
                $existingPage->delete();
            }
        }
    }

    private function isAllowedElementForLearning(Learning $learning, array $elementData): bool
    {
        if (empty($elementData['type']) || ! in_array($elementData['type'], LearningPageElement::TYPES, true)) {
            return false;
        }

        $isFnhCategory = in_array($learning->category, LearningPageElement::FNH_CATEGORIES, true);
        $isFnhType = in_array($elementData['type'], LearningPageElement::FNH_CASE_STUDY_TYPES, true);
        $isSharedStandard = in_array($elementData['type'], LearningPageElement::FNH_SHARED_STANDARD_TYPES, true);

        if ($learning->category === 'case_study' && $elementData['type'] === 'audio') {
            $isSharedStandard = true;
        }

        if ($isFnhCategory && ! $isFnhType && ! $isSharedStandard) {
            return false;
        }

        return $isFnhCategory || ! $isFnhType;
    }

    private function buildElementAttributes(
        LearningPage $page,
        array $elementData,
        Request $request,
        int|string $pIndex,
        int|string $eIndex,
        ?LearningPageElement $existingElement = null,
        int $position = 1
    ): array {
        $type = $elementData['type'];
        $isFileType = in_array($type, LearningPageElement::FILE_TYPES, true);
        $oldFileUrl = $existingElement?->getRawOriginal('file_url');
        $fileUrl = null;

        if ($isFileType) {
            // Start from the existing URL (or whatever was passed back from the form)
            $fileUrl = $elementData['existing_file_url'] ?? $oldFileUrl;

            if ($request->hasFile("pages.{$pIndex}.elements.{$eIndex}.file")) {
                $file   = $request->file("pages.{$pIndex}.elements.{$eIndex}.file");
                $folder = $type === 'audio' ? 'audio' : 'images';
                $newUrl = $this->storePublicFile($file, "learnings/elements/{$folder}");

                // Delete the old file only after the new one is safely stored
                if ($oldFileUrl && $oldFileUrl !== $newUrl) {
                    $this->deletePublicFile($oldFileUrl);
                }

                $fileUrl = $newUrl;
            }
        } else {
            // Non-file element - clean up any stale file that may have been stored
            // previously (e.g. if the element type was changed), then force null.
            if ($oldFileUrl) {
                $this->deletePublicFile($oldFileUrl);
            }
        }

        return [
            'learning_page_id' => $page->id,
            'position'         => $position,
            'type'             => $type,
            'data'             => $this->normalizeElementDataForSave($type, $elementData['data'] ?? [], $existingElement),
            'file_url'         => $fileUrl,
        ];
    }

    private function normalizeElementDataForSave(string $type, array $data, ?LearningPageElement $existingElement = null): array
    {
        $jsonKeyMap = [
            'sentence_visual'        => 'sentence_json',
            'table'                  => 'table_json',
            'template_form_table'    => 'template_form_table_json',
            'movement_quality_table' => 'movement_quality_table_json',
            'centre_character_table' => 'centre_character_table_json',
            'functional_flow'        => 'functional_flow_json',
            'exploring_section'      => 'exploring_section_json',
            'centre_identity_card'   => 'centre_identity_card_json',
            'form_identity_card'     => 'form_identity_card_json',
        ];

        if (isset($jsonKeyMap[$type])) {
            $jsonKey = $jsonKeyMap[$type];
            $jsonVal = $data[$jsonKey] ?? null;
            $decoded = is_string($jsonVal) && trim($jsonVal) !== '' ? json_decode($jsonVal, true) : null;

            // -- Case 1: No valid JSON submitted (block was NOT edited in form) --
            // Return existing database data EXACTLY as stored. Do not process,
            // reformat, or modify it in any way - any transformation risks data loss.
            if (! is_array($decoded)) {
                if ($existingElement) {
                    return $existingElement->data ?? [];
                }
                return [];
            }

            // -- Case 2: JSON submitted (block WAS edited in form) --
            if ($type === 'sentence_visual') {
                $decoded['type'] = 'sentence_visual';
                return $this->removeSentenceVisualBeatStrong($decoded);
            }

            return $decoded;
        }

        return $this->normalizeElementData($type, $data);
    }


    private function removeSentenceVisualBeatStrong(array $data): array
    {
        if (! isset($data['beats']) || ! is_array($data['beats'])) {
            return $data;
        }

        $data['beats'] = array_map(function ($beat) {
            if (is_array($beat)) {
                unset($beat['strong']);
            }

            return $beat;
        }, $data['beats']);

        return $data;
    }

    /**
     * Post-save validation: check every sentence_visual element against the
     * SentenceVisualService to detect canonical schema issues (beat:0, overlapping
     * segments, missing types, etc.) BEFORE the formatter masks them with its
     * hardcoded fallback.
     *
     * Warning messages are shown in the admin dashboard so the admin knows the
     * API show endpoint will display a simplified fallback instead of the
     * intended data.
     *
     * @param  Learning  $learning  The freshly-saved learning with pages+elements
     * @return array<int, string>  Warning messages to surface in the admin dashboard
     */
    private function validateSentenceVisualElements(Learning $learning): array
    {
        $warnings = [];

        $learning->load(['pages.elements']);

        foreach ($learning->pages as $page) {
            foreach ($page->elements as $element) {
                if ($element->type !== 'sentence_visual') {
                    continue;
                }

                $data = $element->data ?? [];
                if (! is_array($data) || ($data['type'] ?? null) !== 'sentence_visual') {
                    continue;
                }

                try {
                    app(SentenceVisualService::class)->format($data);
                } catch (\Throwable $e) {
                    $warnings[] = sprintf(
                        'Sentence visual on page "%s" (element #%d): %s. The API will display a simplified fallback.',
                        $page->title ?: 'Page '.$page->page_number,
                        $element->id,
                        $e->getMessage()
                    );
                }
            }
        }

        return $warnings;
    }
}
