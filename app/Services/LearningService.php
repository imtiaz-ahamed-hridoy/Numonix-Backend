<?php

namespace App\Services;

use App\Http\Resources\Learning\LearningElementResource;
use App\Http\Resources\Learning\LearningPageResource;
use App\Http\Resources\Learning\LearningResource;
use App\Models\GuestDevice;
use App\Models\Learning;
use App\Models\LearningChecklistProgress;
use App\Models\LearningUserProgress;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Repositories\Interfaces\LearningProgressRepositoryInterface;
use App\Repositories\Interfaces\LearningRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service layer for learning (lesson) operations.
 *
 * Orchestrates listing, detail retrieval, completion tracking,
 * checklist updates, and navigation to next/previous learnings
 * for both authenticated users and guest devices.
 */
class LearningService
{
    private static ?array $categoryMetaCache = null;

    public function __construct(
        private LearningRepositoryInterface $learningRepository,
        private LearningProgressRepositoryInterface $progressRepository,
    ) {}

    /**
     * GET /api/v1/learnings - List all published learnings, optionally
     * filtered by category. Excludes hidden categories for tier_1/guest users.
     *
     * @param  User|null  $user
     * @return array{categories: array, learnings: array}
     */
    public function getAllLearnings(?string $category, $user, Request $request): array
    {
        $learnings = $this->learningRepository->getAllPublished($category);

        // Exclude hidden categories for tier_1/guest users
        if (! $user || ! $user->hasTier('tier_2')) {
            $hiddenCategories = ContentAccessConfig::getTier1HiddenCategories();
            if (! empty($hiddenCategories)) {
                $learnings = $learnings->reject(fn (Learning $l) => in_array($l->category, $hiddenCategories, true));
            }
        }

        $learnings = $learnings->load('group');

        // Batch-load user/guest progress
        $progressMap = $this->loadGuestProgress($user, $request, $learnings->pluck('id'));

        // Build position counters per category
        $meta = $this->categoryMeta();
        $categoryPositions = [];
        $result = $learnings->map(function (Learning $learning) use ($user, &$categoryPositions, $progressMap, $meta) {
            $cat = $learning->category;
            $categoryPositions[$cat] = ($categoryPositions[$cat] ?? 0) + 1;
            $position = $categoryPositions[$cat];

            return $this->buildLearningListPayload($learning, $user, $position, $progressMap, $meta);
        })->values()->all();

        return [
            'categories' => $meta,
            'learnings' => $result,
        ];
    }

    /**
     * Fetch learnings for a non-grouped category (lesson, core_theory, advanced_insight).
     *
     * @param  User|null  $user
     * @return array{category_info: array|null, is_hidden: bool, learnings: array, message?: string}
     */
    public function getCategoryLearnings(string $category, $user, Request $request): array
    {
        $meta = $this->categoryMeta();

        // Check if this category is hidden for the user's tier
        $tier = $user ? $user->getSubscriptionTier() : 'tier_1';
        $hiddenCategories = ContentAccessConfig::getHiddenForTier($tier);

        if (in_array($category, $hiddenCategories, true)) {
            return [
                'category_info' => $meta[$category] ?? null,
                'is_hidden' => true,
                'learnings' => [],
                'message' => 'Subscribe to unlock this category.',
            ];
        }

        $learnings = $this->learningRepository->getPublishedByCategory($category)->load('group');
        $progressMap = $this->loadGuestProgress($user, $request, $learnings->pluck('id'));

        $position = 0;
        $result = $learnings->map(function (Learning $learning) use ($user, &$position, $progressMap, $meta) {
            $position++;

            return $this->buildLearningListPayload($learning, $user, $position, $progressMap, $meta);
        })->values()->all();

        return [
            'category_info' => $meta[$category] ?? null,
            'is_hidden' => false,
            'learnings' => $result,
        ];
    }

    /**
     * Fetch learnings for a grouped category (case_study, fnh_architecture).
     *
     * @param  User|null  $user
     * @return array{category_info: array|null, groups: array, group_counts?: array, category_count_box?: array, section_label?: string}
     */
    public function getGroupedCategoryLearnings(string $category, $user, Request $request, bool $compact = false, bool $stripGroupMeta = false): array
    {
        $meta = $this->categoryMeta();
        $learningGroups = $this->learningRepository->getGroupsByCategory($category);

        $learnings = $this->learningRepository->getPublishedByCategoryOrdered($category)
            ->load('group');

        $progressMap = $this->loadGuestProgress($user, $request, $learnings->pluck('id'));

        // Group by group_id or fall back to group_label
        if ($learningGroups->isEmpty()) {
            $grouped = $learnings->groupBy('group_label');
        } else {
            $grouped = $learnings->groupBy(function (Learning $l) {
                return $l->group_id ?? '__ungrouped__';
            });
        }

        $isFnh = $category === 'fnh_architecture';
        $groups = [];
        $groupCounts = [];
        $categoryCountBox = [];
        $groupNumber = 0;

        foreach ($grouped as $groupKey => $items) {
            $first = $items->first();
            $count = $items->count();

            if ($groupKey === '__ungrouped__') {
                continue;
            }

            $groupNumber++;

            // Resolve group data
            $groupModel = null;
            if ($groupKey !== '') {
                if (is_numeric($groupKey)) {
                    $groupModel = $learningGroups->firstWhere('id', (int) $groupKey);
                }
                if (! $groupModel) {
                    $groupModel = $learningGroups->firstWhere('label', $groupKey);
                }
            }

            $groupLabel = $groupModel?->label ?? $first->group_label ?? $groupKey;
            $groupDescription = $groupModel?->description ?? $first->group_description;
            $groupColor = $groupModel?->color ?? $first->group_color;
            $groupOrder = $groupNumber;
            $groupIsPremium = $groupModel?->is_premium ?? $first->is_premium;
            $groupTier2Premium = $groupModel?->tier2_premium ?? false;
            $groupTier3Premium = $groupModel?->tier3_premium ?? false;

            $groupCounts[$groupLabel] = $count;
            $categoryCountBox[] = [
                'label' => $groupLabel,
                'color' => $groupColor,
                'count' => $count,
            ];

            // Lock check
            $isLocked = false;
            $lockReason = null;
            $userTier = $user ? $user->getSubscriptionTier() : 'tier_1';

            if ($userTier === 'tier_3') {
                $isLocked = $groupTier3Premium;
            } elseif ($userTier === 'tier_2') {
                $isLocked = $groupTier2Premium;
            } else {
                $isLocked = $groupIsPremium;
            }

            if ($isLocked) {
                $lockReason = 'subscription_required';
            }

            $lessonNum = 1;
            $formattedLearnings = $items->values()->map(function (Learning $learning) use ($progressMap, $isFnh, $groupOrder, &$lessonNum, $compact) {
                $isCompleted = false;
                $completedAt = null;
                $progress = $progressMap[$learning->id] ?? null;
                if ($progress && $progress->is_completed) {
                    $isCompleted = true;
                    $completedAt = $progress->updated_at?->toIso8601String();
                }

                $payload = $compact
                    ? [
                        'id' => $learning->id,
                        'title' => $learning->title,
                        'subtitle' => $learning->subtitle,
                        'is_completed' => $isCompleted,
                        'completed_at' => $completedAt,
                    ]
                    : [
                        'id' => $learning->id,
                        'title' => $learning->title,
                        'subtitle' => $learning->subtitle,
                        'slug' => $learning->slug,
                        'read_time' => $learning->read_time,
                        'is_completed' => $isCompleted,
                        'completed_at' => $completedAt,
                    ];

                if ($isFnh) {
                    $payload['number'] = $groupOrder.'.'.$lessonNum;
                    $lessonNum++;
                }

                return $payload;
            })->all();

            $group = [
                'label' => $groupLabel,
                'description' => $groupDescription,
                'color' => $groupColor,
                'count' => $count,
                'is_premium' => $groupIsPremium,
                'is_locked' => $isLocked,
                'lock_reason' => $lockReason,
                'learnings' => $formattedLearnings,
            ];

            if ($stripGroupMeta) {
                unset($group['lock_reason']);
                unset($group['count']);
            }

            if ($isFnh) {
                $group['number'] = $groupOrder;
                $group['lessons_label'] = $count.' '.($count === 1 ? 'lesson' : 'lessons');
            }

            $groups[] = $group;
        }

        // Build response with correct key order
        if ($stripGroupMeta) {
            if (isset($meta[$category])) {
                $catInfo = $meta[$category];
                unset($catInfo['accent_color']);
            } else {
                $catInfo = null;
            }

            $responseData = $category === 'case_study'
                ? [
                    'category_info' => $catInfo,
                    'category_count_box' => $categoryCountBox,
                    'groups' => $groups,
                ]
                : [
                    'category_info' => $catInfo,
                    'groups' => $groups,
                ];
        } else {
            $responseData = [
                'category_info' => $meta[$category] ?? null,
                'groups' => $groups,
                'group_counts' => $groupCounts,
            ];
        }

        if ($isFnh) {
            $responseData['section_label'] = 'MODULES';
        }

        return $responseData;
    }

    /**
     * GET /api/v1/learnings/show/{id}
     *
     * @throws HttpException
     */
    public function getLearningDetail(int $id, $user, int $pageNum, int $perPage, Request $request): array
    {
        $learning = $this->learningRepository->findOrFail($id);

        if (! $learning->is_published || ! in_array($learning->status, ['active', 'coming_soon'], true)) {
            throw new NotFoundHttpException('Learning not available.');
        }

        // Determine position for gating
        $position = $this->learningRepository->getPositionInCategory($learning);
        $access = ContentAccessConfig::isLearningAccessible($learning, $user, $position);

        if ($access['is_locked']) {
            throw new HttpException(403, 'This content requires a subscription to access.', null, [
                'lock_reason' => $access['lock_reason'],
            ]);
        }

        $quiz = $this->learningRepository->getQuizForLearning($learning->id);

        // Load pages with elements
        $this->learningRepository->loadPagesWithElements($learning);

        $allPages = $learning->pages->sortBy('page_number')->values();
        $totalPages = $allPages->count();

        // Build page tabs
        $pageTabs = $allPages->map(fn ($page) => [
            'id' => $page->id,
            'title' => $page->title,
            'section_title' => $page->section_title,
        ])->all();

        // Apply pagination if requested
        if ($pageNum > 0 && $perPage > 0) {
            $offset = ($pageNum - 1) * $perPage;
            $paginatedPages = $allPages->slice($offset, $perPage)->values();
        } else {
            $paginatedPages = $allPages;
        }

        // Collect all checklist element IDs for batch-loading progress
        $checklistProgressMap = collect();
        $allElementIds = $paginatedPages->flatMap(function ($page) {
            $elements = $page->relationLoaded('elements') ? $page->elements : $page->elements()->get();

            return $elements->where('type', 'checklist')->pluck('id');
        });

        if ($allElementIds->isNotEmpty()) {
            $checklistProgressMap = $this->loadGuestChecklistProgress($user, $request, $allElementIds);
        }

        // Use the pre-computed guest access flag
        $isGuestAccessible = $access['is_guest_accessible'];

        // Format pages with elements using LearningPageResource
        $formattedPages = $paginatedPages->map(function ($page) use ($checklistProgressMap, $request) {
            return (new LearningPageResource($page, $checklistProgressMap))
                ->toArray($request);
        })->all();

        // Build pagination metadata when paginating
        $pagination = null;
        if ($pageNum > 0 && $perPage > 0) {
            $pagination = [
                'current_page' => $pageNum,
                'per_page' => $perPage,
                'total_pages' => $totalPages,
                'has_more' => ($pageNum * $perPage) < $totalPages,
            ];
        }

        // Track which page the user is viewing - REMOVED (no longer tracking page views)

        return [
            'learning' => (new LearningResource(
                $learning,
                $pageTabs,
                $formattedPages,
                $isGuestAccessible,
                $pagination,
            ))->toArray($request),
            'quiz' => $quiz ? [
                'id' => $quiz->id,
                'status' => $quiz->status,
                'questions_count' => $quiz->questions_count,
            ] : null,
        ];
    }

    /**
     * GET /api/v1/learnings/summary/{id} - Build a lesson summary with
     * parsed key takeaways and a category-specific title.
     *
     * @param  User|null  $user
     * @return array{model: Learning, keyTakeaways: array, categoryTitle: string}
     */
    public function getLearningSummary(int $id, $user): array
    {
        $learning = $this->learningRepository->findOrFail($id);

        if (! $learning->is_published || ! in_array($learning->status, ['active'], true)) {
            throw new NotFoundHttpException('Learning not available.');
        }

        $position = $this->learningRepository->getPositionInCategory($learning);
        $access = ContentAccessConfig::isLearningAccessible($learning, $user, $position);

        if ($access['is_locked']) {
            throw new HttpException(403, 'This content requires a subscription to access.', null, [
                'lock_reason' => $access['lock_reason'],
            ]);
        }

        // Parse pipe-separated text into numbered key takeaways
        $keyTakeaways = [];
        if ($learning->summary) {
            $raw = $learning->summary;
            $lines = str_contains($raw, '|')
                ? explode('|', $raw)
                : preg_split('/\r\n|\r|\n/', $raw);

            $lines = array_filter(array_map('trim', $lines), fn ($line) => $line !== '');

            $number = 1;
            foreach ($lines as $line) {
                $keyTakeaways[] = [
                    'number' => $number,
                    'text' => $line,
                ];
                $number++;
            }
        }

        $categoryTitles = [
            'lesson' => 'Lesson Summary',
            'core_theory' => 'Core Theory Summary',
            'advanced_insight' => 'Advanced Insight Summary',
            'case_study' => 'Case Study Summary',
            'fnh_architecture' => 'FNH Architecture Summary',
        ];

        $categoryTitle = $categoryTitles[$learning->category] ?? 'Summary';

        return [
            'model' => $learning,
            'keyTakeaways' => $keyTakeaways,
            'categoryTitle' => $categoryTitle,
        ];
    }

    /**
     * POST /api/v1/learnings/completed - Mark a learning item as completed
     * for the user or guest, send a notification, and return the next
     * learning + quiz data.
     *
     * @param  User|null  $user
     * @return array{is_guest: bool, is_completed: bool, completed_at: string|null, progress: LearningUserProgress|null, next_learning: array|null, quiz: array|null}
     */
    public function completeLearning(int $learningId, $user, Request $request): array
    {
        $learning = $this->learningRepository->findOrFail($learningId);

        $guestDevice = $user ? null : $this->getGuestDevice($request);

        $progress = null;

        if ($user || $guestDevice) {
            $matchAttrs = ['learning_id' => $learning->id];
            $updateValues = ['is_completed' => true];

            if ($user) {
                $matchAttrs['user_id'] = $user->id;
            } else {
                $matchAttrs['guest_device_id'] = $guestDevice->id;
                $guestDevice->markActivity();
            }

            $progress = $this->progressRepository->updateOrCreateLearningProgress($matchAttrs, $updateValues);

            if ($user) {
                $user->notify(new AppNotification(
                    'Learning Completed!',
                    "You've finished: {$learning->title}",
                    ['learning_id' => $learning->id],
                    'learning_completed'
                ));
            }
        }

        $nextLearning = $this->learningRepository->getNextInCategory($learning);
        $quiz = $this->learningRepository->getQuizForLearning($learning->id);

        $isCompleted = $progress ? (bool) $progress->is_completed : false;
        $completedAt = ($progress && $progress->is_completed) ? $progress->updated_at?->toIso8601String() : null;

        return [
            'is_guest' => $user === null,
            'is_completed' => $isCompleted,
            'completed_at' => $completedAt,
            'progress' => $progress,
            'next_learning' => $nextLearning ? $this->buildLearningListPayload($nextLearning, $user) : null,
            'quiz' => $quiz ? [
                'id' => $quiz->id,
                'learning_id' => $quiz->learning_id,
                'status' => $quiz->status,
            ] : null,
        ];
    }

    /**
     * POST /api/v1/learnings/next - Get the next two learning items
     * in the same category after the given one.
     *
     * @param  User|null  $user
     * @return array{next_learning_1: array|null, next_learning_2: array|null}
     */
    public function getNextLearning(int $learningId, $user): array
    {
        $learning = $this->learningRepository->findOrFail($learningId);

        if (! $learning->is_published || ! in_array($learning->status, ['active', 'coming_soon'], true)) {
            throw new NotFoundHttpException('Learning not available.');
        }

        $nextLearnings = $this->learningRepository->getNextTwoInCategory($learning);

        // Batch-load progress
        $progressMap = collect();
        if ($user && $nextLearnings->isNotEmpty()) {
            $progressMap = $this->progressRepository->getLearningProgressForUser(
                $user->id,
                $nextLearnings->pluck('id')->toArray()
            );
        }
        $meta = $this->categoryMeta();

        return [
            'next_learning_1' => $nextLearnings->get(0) ? $this->buildLearningListPayload($nextLearnings->get(0), $user, null, $progressMap, $meta) : null,
            'next_learning_2' => $nextLearnings->get(1) ? $this->buildLearningListPayload($nextLearnings->get(1), $user, null, $progressMap, $meta) : null,
        ];
    }

    /**
     * POST /api/v1/learnings/previous - Get the previous two learning items
     * in the same category before the given one.
     *
     * @param  User|null  $user
     * @return array{previous_learning_1: array|null, previous_learning_2: array|null}
     */
    public function getPreviousLearning(int $learningId, $user): array
    {
        $learning = $this->learningRepository->findOrFail($learningId);

        if (! $learning->is_published || ! in_array($learning->status, ['active', 'coming_soon'], true)) {
            throw new NotFoundHttpException('Learning not available.');
        }

        $previousLearnings = $this->learningRepository->getPreviousTwoInCategory($learning);

        // Batch-load progress
        $progressMap = collect();
        if ($user && $previousLearnings->isNotEmpty()) {
            $progressMap = $this->progressRepository->getLearningProgressForUser(
                $user->id,
                $previousLearnings->pluck('id')->toArray()
            );
        }
        $meta = $this->categoryMeta();

        return [
            'previous_learning_1' => $previousLearnings->get(0) ? $this->buildLearningListPayload($previousLearnings->get(0), $user, null, $progressMap, $meta) : null,
            'previous_learning_2' => $previousLearnings->get(1) ? $this->buildLearningListPayload($previousLearnings->get(1), $user, null, $progressMap, $meta) : null,
        ];
    }

    /**
     * POST /api/v1/learnings/checklist/update - Update checklist item
     * checked state for a user or guest.
     *
     * @param  User|null  $user
     * @return array{learning_id: int, element_id: int, items: array, completed_count: int, total_count: int}
     */
    public function updateChecklist(int $learningId, int $elementId, array $checkedItems, $user, Request $request): array
    {
        $element = $this->learningRepository->findElementWithPage($elementId);
        if (! $element) {
            throw new NotFoundHttpException('Element not found.');
        }

        if ($element->type !== 'checklist') {
            throw new \InvalidArgumentException('Element is not a checklist.');
        }

        // Verify the element belongs to the specified learning
        $belongsToLearning = $element->page
            && $element->page->learning_id === $learningId;

        if (! $belongsToLearning) {
            throw new \InvalidArgumentException('Element does not belong to the specified learning.');
        }

        $data = $element->data ?? [];
        $totalItems = count($data['items'] ?? []);

        // Convert boolean map to array of checked indexes
        $checkedIndexes = [];
        foreach ($checkedItems as $index => $value) {
            $idx = (int) $index;
            if ((int) $value === 1 && $idx >= 0 && $idx < $totalItems) {
                $checkedIndexes[] = $idx;
            }
        }
        $checkedIndexes = array_values(array_unique($checkedIndexes));

        $guestDevice = $user ? null : $this->getGuestDevice($request);

        if (! $user && ! $guestDevice) {
            throw new HttpException(401, 'Unauthorized to save checklist progress.');
        }

        // Sync checklist items
        $matchAttrs = ['element_id' => $element->id];
        $updateValues = ['checked_items' => $checkedIndexes];

        if ($user) {
            $matchAttrs['user_id'] = $user->id;
        } else {
            $matchAttrs['guest_device_id'] = $guestDevice->id;
            $guestDevice->markActivity();
        }

        $this->progressRepository->updateOrCreateChecklistProgress($matchAttrs, $updateValues);

        // Build response with merged checked state
        $items = collect($data['items'] ?? [])->map(function ($item, $index) use ($checkedIndexes) {
            if (is_array($item)) {
                $item['checked'] = in_array((int) $index, $checkedIndexes, true);
            }

            return $item;
        })->all();

        return [
            'learning_id' => $learningId,
            'element_id' => $element->id,
            'items' => $items,
            'completed_count' => count($checkedIndexes),
            'total_count' => $totalItems,
        ];
    }

    /* ------------------------------------------------------------------
     * Internal helpers
     * ----------------------------------------------------------------*/

    /**
     * Build a standardized learning list-item payload with access gating,
     * completion status, and category metadata.
     *
     * @param  User|null  $user
     * @return array{id: int, category: string, category_label: string, title: string, subtitle: string|null, slug: string, summary: string|null, read_time: int|null, thumbnail_image_url: string|null, status: string, is_premium: bool, is_locked: bool, lock_reason: string|null, is_guest_accessible: bool, is_completed: bool, completed_at: string|null, accent_color: string}
     */
    private function buildLearningListPayload(
        Learning $learning,
        $user = null,
        ?int $position = null,
        $progressMap = [],
        ?array $meta = null
    ): array {
        if ($position === null) {
            $position = $this->learningRepository->getPositionInCategory($learning);
        }

        $access = ContentAccessConfig::isLearningAccessible($learning, $user, $position);

        if ($learning->status === 'coming_soon' && ! $access['is_locked']) {
            $access['is_locked'] = true;
            $access['lock_reason'] = 'upcoming';
        }

        $isCompleted = false;
        $completedAt = null;

        $progress = $progressMap[$learning->id] ?? null;
        if ($progress && $progress->is_completed) {
            $isCompleted = true;
            $completedAt = $progress->updated_at?->toIso8601String();
        }

        $meta = $meta ?? $this->categoryMeta();

        $guestAccess = ContentAccessConfig::isLearningAccessible($learning, null, $position);
        $isPremium = $guestAccess['is_locked'];

        return [
            'id' => $learning->id,
            'category' => $learning->category,
            'category_label' => $meta[$learning->category]['label'] ?? $learning->category,
            'title' => $learning->title,
            'subtitle' => $learning->subtitle,
            'slug' => $learning->slug,
            'summary' => $learning->summary,
            'read_time' => $learning->read_time,
            'thumbnail_image_url' => $learning->thumbnail_image_url,
            'status' => $learning->status,
            'is_premium' => $isPremium,
            'is_locked' => $access['is_locked'],
            'lock_reason' => $access['lock_reason'],
            'is_guest_accessible' => $access['is_guest_accessible'],
            'is_completed' => $isCompleted,
            'completed_at' => $completedAt,
            'accent_color' => $meta[$learning->category]['accent_color'] ?? '#0B4F86',
        ];
    }

    /**
     * Get static category metadata (label, subtitle, accent colour).
     * Results are cached in a static variable for the request lifecycle.
     */
    private function categoryMeta(): array
    {
        if (self::$categoryMetaCache !== null) {
            return self::$categoryMetaCache;
        }

        return self::$categoryMetaCache = [
            'lesson' => [
                'label' => 'Lessons',
                'subtitle' => 'Step-by-step through Functional Numeric Harmony',
                'accent_color' => '#2457E6',
            ],
            'core_theory' => [
                'label' => 'Core Theory',
                'subtitle' => 'The foundations of Functional Numeric Harmony',
                'accent_color' => '#0877B8',
            ],
            'advanced_insight' => [
                'label' => 'Advanced Insights',
                'subtitle' => 'Deeper explorations of FNH beyond the lessons',
                'accent_color' => '#C40E46',
            ],
            'case_study' => [
                'label' => 'Case Studies',
                'subtitle' => 'Recognise the harmonic logic behind real music.',
                'accent_color' => '#8B5CF6',
            ],
            'fnh_architecture' => [
                'label' => 'FNH Architecture',
                'subtitle' => 'Where FNH theory becomes music. Principles, templates, creative freedom.',
                'accent_color' => '#F59E0B',
            ],
        ];
    }

    /**
     * Extract the guest device from the request input (injected by middleware).
     */
    private function getGuestDevice(Request $request): ?GuestDevice
    {
        return $request->input('_guest_device');
    }

    /**
     * Batch-load learning progress for a user or guest device.
     *
     * @param  User|null  $user
     * @param  array<int>|\Illuminate\Support\Collection  $learningIds
     * @return \Illuminate\Support\Collection<int, LearningUserProgress>
     */
    private function loadGuestProgress($user, Request $request, $learningIds): \Illuminate\Support\Collection
    {
        $learningIds = $learningIds instanceof \Illuminate\Support\Collection
            ? $learningIds->toArray()
            : (array) $learningIds;

        if (empty($learningIds)) {
            return collect();
        }

        if ($user) {
            return $this->progressRepository->getLearningProgressForUser($user->id, $learningIds);
        }

        $guestDevice = $this->getGuestDevice($request);
        if ($guestDevice) {
            return $this->progressRepository->getLearningProgressForGuest($guestDevice->id, $learningIds);
        }

        return collect();
    }

    /**
     * Batch-load checklist progress for a user or guest device.
     *
     * @param  User|null  $user
     * @param  array<int>|\Illuminate\Support\Collection  $elementIds
     * @return \Illuminate\Support\Collection<int, LearningChecklistProgress>
     */
    private function loadGuestChecklistProgress($user, Request $request, $elementIds): \Illuminate\Support\Collection
    {
        $elementIds = $elementIds instanceof \Illuminate\Support\Collection
            ? $elementIds->toArray()
            : (array) $elementIds;

        if (empty($elementIds)) {
            return collect();
        }

        if ($user) {
            return $this->progressRepository->getChecklistProgressForUser($user->id, $elementIds);
        }

        $guestDevice = $this->getGuestDevice($request);
        if ($guestDevice) {
            return $this->progressRepository->getChecklistProgressForGuest($guestDevice->id, $elementIds);
        }

        return collect();
    }


}
