<?php

namespace App\Services;

use App\Models\GuestDevice;
use App\Models\HomeModule;
use App\Repositories\Interfaces\HomeModuleRepositoryInterface;
use App\Repositories\Interfaces\LearningRepositoryInterface;
use App\Repositories\Interfaces\UserProgressRepositoryInterface;

/**
 * Service for building the home page payload.
 *
 * Aggregates greeting, continue-learning hero card, and module data
 * with access-gating logic for both authenticated and guest users.
 */
class HomeService
{
    private ContentAccessConfig $accessConfig;
    private HomeModuleRepositoryInterface $homeModuleRepository;
    private LearningRepositoryInterface $learningRepository;
    private UserProgressRepositoryInterface $progressRepository;

    public function __construct(
        ContentAccessConfig $accessConfig,
        HomeModuleRepositoryInterface $homeModuleRepository,
        LearningRepositoryInterface $learningRepository,
        UserProgressRepositoryInterface $progressRepository
    ) {
        $this->accessConfig = $accessConfig;
        $this->homeModuleRepository = $homeModuleRepository;
        $this->learningRepository = $learningRepository;
        $this->progressRepository = $progressRepository;
    }

    /**
     * Build the home page payload.
     */
    public function buildHomePayload(?object $user, ?GuestDevice $guestDevice): array
    {
        // -- Greeting -------------------------------------------------
        $hour = (int) now()->format('H');
        if ($hour < 12) {
            $greeting = 'Good morning';
        } elseif ($hour < 17) {
            $greeting = 'Good afternoon';
        } else {
            $greeting = 'Good evening';
        }

        // -- Continue Learning ----------------------------------------
        $continueLearning = null;
        $continueLearningModuleKey = null;

        if ($user || $guestDevice) {
            $continueLearning = $this->buildContinueLearning($user, $guestDevice);
            if ($continueLearning) {
                $continueLearningModuleKey = $continueLearning['module_key'] ?? null;
            }
        }

        // -- Modules --------------------------------------------------
        $modules = $this->homeModuleRepository->getActiveOrdered();

        $formattedModules = $modules
            ->map(function (HomeModule $module) use ($user, $guestDevice) {
                return $this->formatModule($module, $user, $guestDevice);
            })
            ->all();

        $totalModules = $modules->count();

        return [
            'greeting' => $greeting,
            'headline' => 'Ready to practise?',
            'continue_learning' => $continueLearning,
            'modules_label' => 'Explore Modules',
            'modules_count' => $totalModules,
            'modules' => $formattedModules,
        ];
    }

    /* ------------------------------------------------------------------
     * Continue Learning hero
     * ----------------------------------------------------------------*/

    /**
     * Find the user's latest in-progress lesson to show in the hero card.
     *
     * Priority 1: Resume an in-progress learning (started but not completed).
     *   - progress_percent = pages viewed ÷ total pages (within this learning)
     *   - subtitle = "<ItemLabel> <position> of <totalInCategory>"
     *
     * Priority 2: Start the next unstarted learning after completed ones.
     *   - progress_percent = completed items ÷ total items (category progress)
     *   - subtitle = "<ItemLabel> <position> of <totalInCategory>"
     *
     * @param  object|null  $user  Authenticated user or null
     * @param  GuestDevice|null  $guestDevice  Guest device or null
     * @return array|null Hero card payload or null if no progress
     */
    private function buildContinueLearning($user, $guestDevice = null): ?array
    {
        if (! $user && ! $guestDevice) {
            return null;
        }

        if ($user) {
            $allProgress = $this->progressRepository->getLearningProgressForUser($user->id);
        } else {
            $allProgress = $this->progressRepository->getLearningProgressForGuest($guestDevice->id);
        }

        if ($allProgress->isEmpty()) {
            return null;
        }

        $completedLearningIds = $allProgress
            ->filter(fn ($p) => $p->is_completed)
            ->keys()
            ->toArray();

        $categoryLabels = [
            'lesson' => 'Learn Lesson',
            'core_theory' => 'Core Theory',
            'case_study' => 'Case Study Analysis',
            'fnh_architecture' => 'FNH Architecture',
            'advanced_insight' => 'Advanced Insights',
        ];

        $itemLabels = [
            'lesson' => 'Lesson',
            'core_theory' => 'Chapter',
            'case_study' => 'Study',
            'fnh_architecture' => 'Module',
            'advanced_insight' => 'Insight',
        ];

        $accentColors = [
            'lesson' => '#2457E6',
            'core_theory' => '#0877B8',
            'case_study' => '#8B5CF6',
            'fnh_architecture' => '#F59E0B',
            'advanced_insight' => '#C40E46',
        ];

        $categories = [
            'lesson',
        ];

        foreach ($categories as $category) {
            $allInCategory = $this->learningRepository->getPublishedByCategory($category);

            if ($allInCategory->isEmpty()) {
                continue;
            }

            // Resume a lesson that has a persisted, unfinished progress record
            // before falling back to the next lesson after completed content.
            // The progress table currently tracks completion (page-level
            // tracking was intentionally removed), so the category progress
            // percentage remains completion-based.
            $inProgressLearning = $allInCategory->first(function ($learning) use ($allProgress) {
                $progress = $allProgress->get($learning->id);

                return $progress && ! $progress->is_completed;
            });

            $isResuming = $inProgressLearning !== null;
            $nextLearning = $inProgressLearning ?? $allInCategory->first(
                fn ($learning) => ! in_array($learning->id, $completedLearningIds)
            );

            if (! $nextLearning) {
                continue;
            }

            $totalInCategory = $allInCategory->count();

            $completedInCategory = $allInCategory
                ->whereIn('id', $completedLearningIds)
                ->count();
            $progressPercent = $totalInCategory > 0
                ? (int) round(($completedInCategory / $totalInCategory) * 100)
                : 0;

            $itemLabel = $itemLabels[$category] ?? 'Lesson';

            return [
                'label' => $isResuming || $progressPercent > 0 ? 'CONTINUE LEARNING' : 'START LEARNING',
                'learning_id' => $nextLearning->id,
                'title' => $categoryLabels[$category] ?? ucwords(str_replace('_', ' ', $category)),
                'subtitle' => $itemLabel.' '.$completedInCategory.' of '.$totalInCategory,
                'progress_percent' => $progressPercent,
                'accent_color' => $accentColors[$category] ?? '#2457E6',
                'module_key' => $category,
                'continue_route' => '/learnings/show/'.$nextLearning->id,
                'learning_title' => $nextLearning->title,
                'learning_slug' => $nextLearning->slug,
                'total_pages' => $nextLearning->pages()->count(),
                'all_lessons_route' => $this->categoryRoute($category),
            ];
        }

        return null;
    }

    /* ------------------------------------------------------------------
     * Module formatter
     * ----------------------------------------------------------------*/

    /**
     * Format a home module for the API response with lock status.
     *
     * Uses a hybrid approach:
     *  - If module has is_premium = true -> locked for tier_1 users
     *  - For learning_category modules, also checks ContentAccessConfig
     *
     * @param  HomeModule  $module  The module to format
     * @param  object|null  $user  Authenticated user or null
     * @param  GuestDevice|null  $guestDevice  Guest device or null
     * @return array Formatted module payload
     */
    private function formatModule(HomeModule $module, $user, $guestDevice = null): array
    {
        $access = $this->accessConfig->isHomeModuleAccessible((bool) $module->is_premium, $user);
        $isLocked = $access['is_locked'];
        $lockReason = $access['lock_reason'];
        $badgeText = $module->badge_text;

        if ($module->module_type === 'learning_category') {
            $dynamicBadge = $this->dynamicBadgeText($module, $user, $guestDevice);
            if ($dynamicBadge) {
                $badgeText = $dynamicBadge;
            }
        }

        return [
            'id' => $module->id,
            'title' => $module->title,
            'description' => $module->description,
            'icon' => $module->icon_url,
            'badge_text' => $badgeText,
            'cta_text' => $isLocked ? 'Unlock Membership' : $module->cta_text,
            'module_type' => $module->module_type,
            'module_key' => $module->module_key,
            'is_locked' => $isLocked,
            'lock_reason' => $lockReason,
            'route' => $this->moduleRoute($module),
        ];
    }

    /**
     * Generate dynamic badge text for the 'lesson' category based on user progress.
     *
     * Only applies to the 'lesson' category. Returns "Lesson X of Y" format.
     *
     * @param  HomeModule  $module  The home module
     * @param  object|null  $user  Authenticated user or null
     * @param  GuestDevice|null  $guestDevice  Guest device or null
     * @return string|null Dynamic badge text or null to fall back to static badge
     */
    private function dynamicBadgeText(HomeModule $module, $user, $guestDevice = null): ?string
    {
        $categoryKey = $module->module_key;

        if ($categoryKey !== 'lesson') {
            return null;
        }

        $allPublished = $this->learningRepository->getAllPublished($categoryKey);
        $totalInCategory = $allPublished->count();

        if ($totalInCategory === 0) {
            return null;
        }

        $completedCount = 0;
        $publishedIds = $allPublished->pluck('id')->toArray();

        if ($user) {
            $allUserProgress = $this->progressRepository->getLearningProgressForUser($user->id);
            $completedCount = $allUserProgress
                ->where('is_completed', true)
                ->filter(fn ($p) => in_array($p->learning_id, $publishedIds))
                ->count();
        } elseif ($guestDevice) {
            $allGuestProgress = $this->progressRepository->getLearningProgressForGuest($guestDevice->id);
            $completedCount = $allGuestProgress
                ->where('is_completed', true)
                ->filter(fn ($p) => in_array($p->learning_id, $publishedIds))
                ->count();
        }

        $currentPosition = $completedCount + 1;
        if ($currentPosition > $totalInCategory) {
            $currentPosition = $totalInCategory;
        }

        $label = $module->badge_text ?: 'Lesson';

        return $label.' '.$currentPosition.' of '.$totalInCategory;
    }

    /**
     * Map a learning category key to its API listing route.
     */
    public function categoryRoute(string $category): string
    {
        $routes = [
            'lesson' => '/learnings/lessons',
            'core_theory' => '/learnings/core-theory',
            'case_study' => '/learnings/case-studies',
            'fnh_architecture' => '/learnings/fnh-architecture',
            'advanced_insight' => '/learnings/advanced-insights',
        ];

        return $routes[$category] ?? '/learnings';
    }

    /**
     * Determine the navigation route for a home module's CTA button.
     *
     * @param  HomeModule  $module  The home module
     * @return string|null The route path or null for external modules
     */
    private function moduleRoute(HomeModule $module): ?string
    {
        if ($module->module_type === 'learning_category') {
            return $this->categoryRoute($module->module_key);
        }

        if ($module->module_type === 'ear_training') {
            return '/ear-training/levels';
        }

        return null;
    }
}
