<?php

namespace App\Http\Controllers\Web\Backend\GuestAccess;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\GuestAccess\ToggleGuestAccessItemRequest;
use App\Http\Requests\Web\Backend\GuestAccess\UpdateGuestAccessRequest;
use App\Models\EarTrainingLevel;
use App\Models\EarTrainingStage;
use App\Models\Learning;
use App\Models\LearningGroup;
use App\Repositories\Interfaces\GuestAccessSettingRepositoryInterface;
use App\Repositories\Interfaces\GuestActivityLogRepositoryInterface;
use App\Services\ContentAccessConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuestAccessController extends Controller
{
    protected GuestActivityLogRepositoryInterface $activityLogRepository;
    protected GuestAccessSettingRepositoryInterface $accessSettingRepository;

    public function __construct(
        GuestActivityLogRepositoryInterface $activityLogRepository,
        GuestAccessSettingRepositoryInterface $accessSettingRepository
    ) {
        $this->activityLogRepository = $activityLogRepository;
        $this->accessSettingRepository = $accessSettingRepository;
    }

    /**
     * Display the guest access settings page.
     */
    public function index(): View
    {
        // Current settings
        $settings = [
            // Tier 1 limits (guest + tier_1 users)
            'tier1_limit_lesson' => $this->accessSettingRepository->getInt('tier1_limit_lesson', 6),
            'tier1_limit_core_theory' => $this->accessSettingRepository->getInt('tier1_limit_core_theory', 6),
            'tier1_limit_case_study' => $this->accessSettingRepository->getInt('tier1_limit_case_study', 6),
            'tier1_limit_fnh_architecture' => $this->accessSettingRepository->getInt('tier1_limit_fnh_architecture', 1),
            'tier1_limit_advanced_insight' => $this->accessSettingRepository->getInt('tier1_limit_advanced_insight', 0),
            'tier1_ear_training_beginner_levels' => $this->accessSettingRepository->getInt('tier1_ear_training_beginner_levels', 1),
            'tier1_ear_training_intermediate_levels' => $this->accessSettingRepository->getInt('tier1_ear_training_intermediate_levels', 1),
            'tier1_ear_training_advanced_levels' => $this->accessSettingRepository->getInt('tier1_ear_training_advanced_levels', 1),
            'hidden_categories' => $this->accessSettingRepository->getArray('hidden_categories', []),
            // Tier 2 limits (partial premium)
            'tier2_limit_lesson' => $this->accessSettingRepository->getInt('tier2_limit_lesson', 99),
            'tier2_limit_core_theory' => $this->accessSettingRepository->getInt('tier2_limit_core_theory', 99),
            'tier2_limit_case_study' => $this->accessSettingRepository->getInt('tier2_limit_case_study', 99),
            'tier2_limit_fnh_architecture' => $this->accessSettingRepository->getInt('tier2_limit_fnh_architecture', 99),
            'tier2_limit_advanced_insight' => $this->accessSettingRepository->getInt('tier2_limit_advanced_insight', 99),
            'tier2_ear_training_beginner_levels' => $this->accessSettingRepository->getInt('tier2_ear_training_beginner_levels', 1),
            'tier2_ear_training_intermediate_levels' => $this->accessSettingRepository->getInt('tier2_ear_training_intermediate_levels', 1),
            'tier2_ear_training_advanced_levels' => $this->accessSettingRepository->getInt('tier2_ear_training_advanced_levels', 1),
            'tier2_hidden_categories' => $this->accessSettingRepository->getArray('tier2_hidden_categories', []),
            // Tier 3 limits (full premium - defaults to unlimited)
            'tier3_limit_lesson' => $this->accessSettingRepository->getInt('tier3_limit_lesson', 99),
            'tier3_limit_core_theory' => $this->accessSettingRepository->getInt('tier3_limit_core_theory', 99),
            'tier3_limit_case_study' => $this->accessSettingRepository->getInt('tier3_limit_case_study', 99),
            'tier3_limit_fnh_architecture' => $this->accessSettingRepository->getInt('tier3_limit_fnh_architecture', 99),
            'tier3_limit_advanced_insight' => $this->accessSettingRepository->getInt('tier3_limit_advanced_insight', 99),
            'tier3_ear_training_beginner_levels' => $this->accessSettingRepository->getInt('tier3_ear_training_beginner_levels', 1),
            'tier3_ear_training_intermediate_levels' => $this->accessSettingRepository->getInt('tier3_ear_training_intermediate_levels', 1),
            'tier3_ear_training_advanced_levels' => $this->accessSettingRepository->getInt('tier3_ear_training_advanced_levels', 1),
            'tier3_hidden_categories' => $this->accessSettingRepository->getArray('tier3_hidden_categories', []),
        ];

        // Overview stats - single aggregation query (replaces 3 queries)
        $userStats = DB::selectOne("
            SELECT
                COUNT(*) as total_users,
                SUM(CASE WHEN subscription_tier IS NULL OR subscription_tier = 'tier_1' THEN 1 ELSE 0 END) as tier1_users
            FROM users WHERE role != 'Admin'
        ");
        $totalUsers = (int) $userStats->total_users;
        $tier1Users = (int) $userStats->tier1_users;
        $subscribedUsers = $totalUsers - $tier1Users;
        $guestSessionsToday = $this->activityLogRepository->countDistinctGuestSessionsToday();

        // Content counts
        $totalLearnings = Learning::where('is_published', true)
            ->whereIn('status', ['active', 'coming_soon'])->count();

        // All learnings for the per-item control table
        $learnings = Learning::where('is_published', true)
            ->whereIn('status', ['active', 'coming_soon'])
            ->orderBy('category')
            ->orderBy('id')
            ->get();

        // All ear training stages with levels count
        $earTrainingStages = EarTrainingStage::withCount('levels')->get();
        $earTrainingLevels = EarTrainingLevel::with('stage')->orderBy('ear_training_stage_id')->orderBy('level_number')->get();

        // Case Study groups with premium status
        $caseStudyGroups = LearningGroup::where('category', 'case_study')
            ->orderBy('id')
            ->get();

        // FNH Architecture groups with premium status
        $fnhArchitectureGroups = LearningGroup::where('category', 'fnh_architecture')
            ->orderBy('id')
            ->get();

        // Available categories
        $categories = [
            'lesson' => 'Lessons',
            'core_theory' => 'Core Theory',
            'case_study' => 'Case Studies',
            'fnh_architecture' => 'FNH Architecture',
            'advanced_insight' => 'Advanced Insights',
        ];

        return view('backend.layouts.guest-access.index', [
            'settings' => $settings,
            'totalUsers' => $totalUsers,
            'tier1Users' => $tier1Users,
            'subscribedUsers' => $subscribedUsers,
            'guestSessionsToday' => $guestSessionsToday,
            'totalLearnings' => $totalLearnings,
            'learnings' => $learnings,
            'earTrainingStages' => $earTrainingStages,
            'earTrainingLevels' => $earTrainingLevels,
            'categories' => $categories,
            'caseStudyGroups' => $caseStudyGroups,
            'fnhArchitectureGroups' => $fnhArchitectureGroups,
        ]);
    }

    /**
     * Update guest access settings.
     */
    public function update(UpdateGuestAccessRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            // Save each tier-1 limit setting
            $tier1LimitKeys = [
                'tier1_limit_lesson',
                'tier1_limit_core_theory',
                'tier1_limit_case_study',
                'tier1_limit_fnh_architecture',
                'tier1_limit_advanced_insight',
            ];

            foreach ($tier1LimitKeys as $key) {
                $this->accessSettingRepository->set($key, (string) $validated[$key]);
            }

            // Save each tier-2 limit setting
            $tier2LimitKeys = [
                'tier2_limit_lesson',
                'tier2_limit_core_theory',
                'tier2_limit_case_study',
                'tier2_limit_fnh_architecture',
                'tier2_limit_advanced_insight',
            ];

            foreach ($tier2LimitKeys as $key) {
                $this->accessSettingRepository->set($key, (string) $validated[$key]);
            }

            // Save each tier-3 limit setting
            $tier3LimitKeys = [
                'tier3_limit_lesson',
                'tier3_limit_core_theory',
                'tier3_limit_case_study',
                'tier3_limit_fnh_architecture',
                'tier3_limit_advanced_insight',
            ];

            foreach ($tier3LimitKeys as $key) {
                $this->accessSettingRepository->set($key, (string) $validated[$key]);
            }

            // Tier 1 ear training toggle settings (checkboxes)
            foreach (['tier1_ear_training_beginner_levels', 'tier1_ear_training_intermediate_levels', 'tier1_ear_training_advanced_levels'] as $key) {
                $this->accessSettingRepository->set($key, $request->has($key) ? '1' : '0');
            }

            // Tier 2 ear training toggle settings (checkboxes)
            foreach (['tier2_ear_training_beginner_levels', 'tier2_ear_training_intermediate_levels', 'tier2_ear_training_advanced_levels'] as $key) {
                $this->accessSettingRepository->set($key, $request->has($key) ? '1' : '0');
            }

            // Tier 3 ear training toggle settings (checkboxes)
            foreach (['tier3_ear_training_beginner_levels', 'tier3_ear_training_intermediate_levels', 'tier3_ear_training_advanced_levels'] as $key) {
                $this->accessSettingRepository->set($key, $request->has($key) ? '1' : '0');
            }

            // Save case study group-level premium toggles
            // Reset all groups first (unchecked checkboxes aren't submitted)
            LearningGroup::where('category', 'case_study')->update([
                'is_premium' => false,
                'tier2_premium' => false,
                'tier3_premium' => false,
            ]);

            $caseStudyGroups = LearningGroup::where('category', 'case_study')->get();
            foreach ($caseStudyGroups as $group) {
                $group->update([
                    'is_premium' => $request->has("group_premium.{$group->id}"),
                    'tier2_premium' => $request->has("group_tier2_premium.{$group->id}"),
                    'tier3_premium' => $request->has("group_tier3_premium.{$group->id}"),
                ]);
            }

            // Save FNH Architecture group-level premium toggles
            LearningGroup::where('category', 'fnh_architecture')->update([
                'is_premium' => false,
                'tier2_premium' => false,
                'tier3_premium' => false,
            ]);

            $fnhGroups = LearningGroup::where('category', 'fnh_architecture')->get();
            foreach ($fnhGroups as $group) {
                $group->update([
                    'is_premium' => $request->has("group_premium_fnh.{$group->id}"),
                    'tier2_premium' => $request->has("group_tier2_premium_fnh.{$group->id}"),
                    'tier3_premium' => $request->has("group_tier3_premium_fnh.{$group->id}"),
                ]);
            }

            // Save hidden categories (array -> JSON)
            $this->accessSettingRepository->set(
                'hidden_categories',
                $validated['hidden_categories'] ?? []
            );

            // Save tier 2 hidden categories
            $this->accessSettingRepository->set(
                'tier2_hidden_categories',
                $validated['tier2_hidden_categories'] ?? []
            );

            // Save tier 3 hidden categories
            $this->accessSettingRepository->set(
                'tier3_hidden_categories',
                $validated['tier3_hidden_categories'] ?? []
            );

            // Clear all caches to ensure changes take effect immediately
            $this->accessSettingRepository->clearCache();

            return redirect()->route('guest-access.index')
                ->with('t-success', 'Guest access settings updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('guest-access.index')
                ->with('t-error', 'Failed to update settings: '.$e->getMessage());
        }
    }

    /**
     * AJAX: Toggle guest accessibility for an individual item.
     */
    public function toggleItem(ToggleGuestAccessItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            if ($validated['type'] === 'learning') {
                $item = Learning::findOrFail($validated['id']);
                
                $newValue = match ($validated['value']) {
                    'null' => null,
                    'true' => true,
                    'false' => false,
                    default => null,
                };
                
                $item->is_guest_accessible = $newValue;
                
                if ($newValue === false) {
                    $item->is_premium = true;
                } elseif ($newValue === true) {
                    $item->is_premium = false;
                } else {
                    $item->is_premium = null;
                }
            } else {
                // Ear Training Level uses guest_access_override string
                $item = EarTrainingLevel::findOrFail($validated['id']);
                
                // Natively accept the strings from the UI
                $newValue = $validated['value'];
                
                if ($newValue === 'Locked') {
                    $item->guest_access_override = 'Locked';
                    $item->is_premium = true;
                } elseif ($newValue === 'Unlocked') {
                    $item->guest_access_override = 'Unlocked';
                    $item->is_premium = false;
                } else {
                    $item->guest_access_override = 'Use Level Rule';
                    $item->is_premium = false;
                }
            }

            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Guest access updated for: '.($item->title ?? 'Ear Training Level'),
                'value' => $newValue,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Get content preview - what guests currently see.
     */
    public function contentPreview(): JsonResponse
    {
        $learnings = Learning::where('is_published', true)
            ->whereIn('status', ['active', 'coming_soon'])
            ->orderBy('category')
            ->orderBy('id')
            ->get();

        $categoryPositions = [];
        $learningsPreview = $learnings->map(function (Learning $learning) use (&$categoryPositions) {
            $cat = $learning->category;
            $categoryPositions[$cat] = ($categoryPositions[$cat] ?? 0) + 1;
            $position = $categoryPositions[$cat];

            $access = ContentAccessConfig::isLearningAccessible($learning, null, $position);

            return [
                'id' => $learning->id,
                'title' => $learning->title,
                'category' => $learning->category,
                'position' => $position,
                'is_locked' => $access['is_locked'],
                'lock_reason' => $access['lock_reason'],
                'is_guest_accessible' => $access['is_guest_accessible'],
                'is_hidden' => $access['is_hidden'],
                'admin_override' => $learning->is_guest_accessible,
            ];
        });

        // Ear Training Levels preview
        $levels = EarTrainingLevel::with('stage')->orderBy('ear_training_stage_id')->orderBy('level_number')->get();
        $levelsPreview = $levels->map(function (EarTrainingLevel $level) {
            $access = ContentAccessConfig::isEarTrainingLevelAccessible($level, null);

            return [
                'id' => $level->id,
                'level' => current(explode(' ', $level->stage?->title ?? '')),
                'short_description' => $level->title,
                'position' => $level->level_number,
                'is_locked' => $access['is_locked'],
                'lock_reason' => $access['lock_reason'],
                'is_guest_accessible' => $access['is_guest_accessible'],
                'hide_sublisting' => $access['hide_sublisting'],
                'admin_override' => $level->guest_access_override === 'Unlocked' ? true : ($level->guest_access_override === 'Locked' ? false : null),
            ];
        });

        return response()->json([
            'learnings' => $learningsPreview,
            'ear_training_levels' => $levelsPreview,
        ]);
    }
}
