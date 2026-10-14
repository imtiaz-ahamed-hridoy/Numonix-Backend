<?php

namespace App\Services;

use App\Models\GuestDevice;
use App\Models\User;
use App\Repositories\Interfaces\EarTrainingLevelRepositoryInterface;
use App\Repositories\Interfaces\LearningRepositoryInterface;
use App\Repositories\Interfaces\UserProgressRepositoryInterface;

/**
 * Service layer for aggregated user or guest progress across
 * learning items (lessons, theory, etc.) and ear-training levels.
 */
class UserProgressService
{
    public function __construct(
        private UserProgressRepositoryInterface $progressRepository,
        private LearningRepositoryInterface $learningRepository,
        private EarTrainingLevelRepositoryInterface $earTrainingLevelRepository,
    ) {}

    /**
     * Compute and return the full progress snapshot for a user or guest,
     * including per-category completion, ear-training level progress,
     * and overall percentage.
     *
     * @param  User|null  $user
     * @param  GuestDevice|null  $guestDevice
     * @return array{summary: array, items: array{categories: array}}
     */
    public function getProgress($user, $guestDevice): array
    {
        $userId = $user ? $user->id : null;

        // --- Lessons Progress ----------------------------------------------
        $learnings = $this->learningRepository->getAllPublished();

        if ($user) {
            $allProgress = $this->progressRepository->getLearningProgressForUser($userId);
        } elseif ($guestDevice) {
            $allProgress = $this->progressRepository->getLearningProgressForGuest($guestDevice->id);
        } else {
            $allProgress = collect();
        }

        $totalLessons = $learnings->count();
        $completedLessons = 0;

        $categoryConfig = [
            'lesson' => ['title' => 'Lessons',           'icon' => 'book',         'items' => [], 'completed' => 0, 'total' => 0],
            'core_theory' => ['title' => 'Core Theory',       'icon' => 'lightbulb',    'items' => [], 'completed' => 0, 'total' => 0],
            'case_study' => ['title' => 'Case Studies',      'icon' => 'cases',        'items' => [], 'completed' => 0, 'total' => 0],
            'fnh_architecture' => ['title' => 'FNH Architecture',  'icon' => 'architecture', 'items' => [], 'completed' => 0, 'total' => 0],
            'advanced_insight' => ['title' => 'Advanced Insights', 'icon' => 'insights',     'items' => [], 'completed' => 0, 'total' => 0],
        ];

        foreach ($learnings as $learning) {
            $cat = $learning->category;
            if (! isset($categoryConfig[$cat])) {
                continue;
            }

            $categoryConfig[$cat]['total']++;
            $itemIndex = $categoryConfig[$cat]['total'];

            $access = ContentAccessConfig::isLearningAccessible($learning, $user, $itemIndex);

            $isCompleted = isset($allProgress[$learning->id]) && $allProgress[$learning->id]->is_completed;

            if ($isCompleted) {
                $completedLessons++;
                $categoryConfig[$cat]['completed']++;
                $progressPercent = 100;
                $state = 'completed';
                $subtitle = $categoryConfig[$cat]['title'].' '.$itemIndex.' -- Complete';
            } else {
                $progressPercent = 0;
                $state = 'not_started';
                $subtitle = $categoryConfig[$cat]['title'].' '.$itemIndex.' -- Not started';
            }

            $categoryConfig[$cat]['items'][] = [
                'id' => $learning->id,
                'title' => $learning->title,
                'subtitle' => $subtitle,
                'progress_percentage' => $progressPercent,
                'state' => $state,
                'is_premium' => (bool) $learning->is_premium,
                'is_locked' => $access['is_locked'],
                'lock_reason' => $access['lock_reason'],
                'is_guest_accessible' => $access['is_guest_accessible'],
            ];
        }

        $lessonsPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        // --- Ear Training Progress -----------------------------------------
        $levels = $this->earTrainingLevelRepository->getPublishedLevels();

        if ($user) {
            $userAttempts = $this->progressRepository->getEarTrainingAttemptsForUser($userId);
        } elseif ($guestDevice) {
            $userAttempts = $this->progressRepository->getEarTrainingAttemptsForGuest($guestDevice->id);
        } else {
            $userAttempts = collect();
        }

        $totalExercisesOverall = 0;
        $completedExercisesOverall = 0;
        $earTrainingItems = [];
        $activeEarTrainingLevel = null;

        foreach ($levels as $level) {
            $stage = $level->stage;
            $difficulty = $stage ? strtolower($stage->title ?? '') : '';
            if ($difficulty === 'advance') {
                $difficulty = 'advanced';
            }
            
            $totalExercises = (int) $level->exercise_count;
            $totalExercisesOverall += $totalExercises;

            // Find the progress record for this level
            $progressRecord = $userAttempts->where('ear_training_level_id', $level->id)->first();
            
            $exercisesCompleted = $progressRecord ? (int) $progressRecord->completed_exercises : 0;
            $completedExercisesOverall += $exercisesCompleted;

            $access = ContentAccessConfig::isEarTrainingLevelAccessible($level, $user);

            if ($access['is_locked']) {
                $state = 'locked';
                $subtitle = 'Locked';
                $progressPercent = 0;
            } elseif ($exercisesCompleted >= $totalExercises && $totalExercises > 0) {
                $state = 'completed';
                $subtitle = $exercisesCompleted.' / '.$totalExercises.' exercises';
                $progressPercent = 100;
            } elseif ($exercisesCompleted > 0) {
                $state = 'in_progress';
                $subtitle = $exercisesCompleted.' / '.$totalExercises.' exercises';
                $progressPercent = $totalExercises > 0 ? round(($exercisesCompleted / $totalExercises) * 100) : 0;
                if (! $activeEarTrainingLevel) {
                    $activeEarTrainingLevel = ($stage ? $stage->title : ucfirst($difficulty)) . ' in progress';
                }
            } else {
                $state = 'not_started';
                $subtitle = 'Not started';
                $progressPercent = 0;
            }

            $earTrainingItems[] = [
                'id' => $level->id,
                'title' => 'Level ' . $level->level_number . ($level->title ? ': ' . $level->title : ''),
                'subtitle' => $subtitle,
                'progress_percentage' => $progressPercent,
                'state' => $state,
                'is_premium' => $level->is_premium ?? ContentAccessConfig::isEarTrainingLevelPremium($difficulty),
                'is_locked' => $access['is_locked'],
                'lock_reason' => $access['lock_reason'],
                'is_guest_accessible' => $access['is_guest_accessible'] ?? false,
                'hide_sublisting' => $access['hide_sublisting'] ?? false,
            ];
        }

        $earTrainingPercent = $totalExercisesOverall > 0
            ? round(($completedExercisesOverall / $totalExercisesOverall) * 100)
            : 0;

        // --- Build Categories ----------------------------------------------
        $categories = [];

        foreach ($categoryConfig as $key => $config) {
            $categories[] = [
                'key' => $key,
                'title' => $config['title'],
                'icon' => $config['icon'],
                'subtitle' => $config['completed'].' of '.$config['total'].' completed',
                'completed' => $config['completed'],
                'total' => $config['total'],
                'items' => $config['items'],
            ];
        }

        $categories[] = [
            'key' => 'ear_training',
            'title' => 'Ear Training',
            'icon' => 'headphones',
            'subtitle' => $activeEarTrainingLevel
                ?: $completedExercisesOverall.' of '.$totalExercisesOverall.' exercises completed',
            'completed' => $completedExercisesOverall,
            'total' => $totalExercisesOverall,
            'items' => $earTrainingItems,
        ];

        $totalItems = $totalLessons + $totalExercisesOverall;
        $totalCompleted = $completedLessons + $completedExercisesOverall;
        $overallPercent = $totalItems > 0 ? round(($totalCompleted / $totalItems) * 100) : 0;

        return [
            'summary' => [
                'total_percentage' => $overallPercent,
                'learnings' => [
                    'completed' => $completedLessons,
                    'total' => $totalLessons,
                    'fraction' => $completedLessons.'/'.$totalLessons,
                    'percentage' => $lessonsPercent,
                ],
                'ear_training' => [
                    'completed' => $completedExercisesOverall,
                    'total' => $totalExercisesOverall,
                    'fraction' => $completedExercisesOverall.'/'.$totalExercisesOverall,
                    'percentage' => $earTrainingPercent,
                ],
            ],
            'items' => [
                'categories' => $categories,
            ],
        ];
    }
}
