<?php

namespace App\Services;

use App\Models\EarTrainingStage;
use App\Models\EarTrainingLevel;
use App\Models\EarTrainingUserProgress;
use App\Models\EarTrainingUserAnswer;
use App\Models\GuestDevice;
use App\Models\User;
use App\Services\ContentAccessConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service layer for Ear Training domain.
 */
class EarTrainingService
{
    /**
     * Retrieve all active ear-training stages (formerly known as levels).
     */
    public function getStages($user, $guestDevice): array
    {
        $stages = EarTrainingStage::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Calculate overall progress based on levels the user has accessed or passed (mocked empty object here, add logic if needed based on previous implementation)
        $overallProgress = (object)[]; 

        $stagesData = $stages->map(function ($stage) {
            return [
                'id' => $stage->id,
                'title' => $stage->title,
                'subtitle' => $stage->subtitle,
                'patterns_label' => $stage->patterns_label,
                'description' => $stage->description,
                'accent_colour' => $stage->accent_colour,
                'is_premium' => (bool)$stage->is_premium,
                'is_locked_by_default' => (bool)$stage->is_locked_by_default,
                'next_stage_teaser' => $stage->next_stage_teaser,
                'status' => 'not_started', // simplified status for stage
            ];
        });

        return [
            'overall_progress' => $overallProgress,
            'levels' => $stagesData,
        ];
    }

    /**
     * Retrieve all quizzes (exercises/levels) for a specific stage.
     */
    public function getLevelsForStage(int $stageId, $user, $guestDevice): array
    {
        $stage = EarTrainingStage::where('is_active', true)->findOrFail($stageId);
        $levels = $stage->levels()->where('is_active', true)->orderBy('level_number')->get();

        $allProgress = $this->getProgressForUser($user, $guestDevice);

        $levelsData = $levels->map(function ($level) use ($allProgress, $user, $stage) {
            $progress = $allProgress->where('ear_training_level_id', $level->id)->first();

            // Check Access
            $access = ContentAccessConfig::isEarTrainingLevelAccessible($level, $user);
            $guestAccess = ContentAccessConfig::isEarTrainingLevelAccessible($level, null);

            $mappedStatus = 'not_started';
            if ($access['is_locked']) {
                $mappedStatus = 'locked';
            } elseif ($progress) {
                $mappedStatus = $progress->status === 'completed' ? 'practised' : $progress->status;
            }

            return [
                'id' => $level->id,
                'title' => $level->title,
                'description' => $level->description,
                'is_premium' => $guestAccess['is_locked'],
                'is_locked' => $access['is_locked'],
                'lock_reason' => $access['lock_reason'],
                'is_guest_accessible' => $access['is_guest_accessible'],
                'status' => $mappedStatus,
                'attempts' => $progress ? $progress->attempts : 0,
                'last_practised' => $progress ? $progress->updated_at->toIso8601String() : null,
                'best_score' => $progress ? $progress->correct_answers : 0,
                'passed' => $progress ? $progress->score_percentage >= 80 : false,
                
                // Generation Parameters
                'exercise_count' => $level->exercise_count,
                'exercise_type' => $level->exercise_type,
                'function_patterns' => is_string($level->function_patterns) ? json_decode($level->function_patterns, true) : $level->function_patterns,
                'tempo_bpm' => $level->tempo_bpm,
                'midi_program' => $level->midi_program,
                'chord_voicing' => $level->chord_voicing,
                'chord_size' => (int)$level->chord_size,
                'chord_purity' => $level->chord_purity,
                'answer_display' => $level->answer_display,
                'chord_regularity' => $level->chord_regularity,
                'bar_length' => $level->bar_length,
                'closes_on_following_one' => (bool)$level->closes_on_following_one,
                'repetitions' => $level->repetitions,
                'question_text_override' => $level->question_text_override,
                'completion_heading' => $level->completion_heading,
                'completion_body' => $level->completion_body,
                'completion_footer' => $level->completion_footer,
            ];
        });

        return $levelsData->toArray();
    }

    /**
     * Submit answers for an ear-training level.
     */
    public function submitLevel(int $levelId, array $validated, $user, Request $request): array
    {
        $level = EarTrainingLevel::findOrFail($levelId);

        $access = ContentAccessConfig::isEarTrainingLevelAccessible($level, $user);
        if ($access['is_locked']) {
            throw new HttpException(403, 'This level requires a subscription to access.', null, [
                'lock_reason' => $access['lock_reason'],
            ]);
        }

        $correctAnswers = (int)$validated['correct_answers'];
        $totalExercises = (int)$validated['total_exercises'];
        $scorePercentage = $totalExercises > 0 ? (int)round(($correctAnswers / $totalExercises) * 100) : 0;
        
        $guestDevice = null;
        $userId = null;
        $guestDeviceId = null;

        if ($user) {
            $userId = $user->id;
        } else {
            $guestDevice = $request->input('_guest_device');
            if ($guestDevice) {
                $guestDeviceId = $guestDevice->id;
                $guestDevice->markActivity();
            }
        }

        // Find or create progress
        $progressQuery = EarTrainingUserProgress::where('ear_training_level_id', $level->id);
        if ($userId) {
            $progressQuery->where('user_id', $userId);
        } elseif ($guestDeviceId) {
            $progressQuery->where('guest_device_id', $guestDeviceId);
        } else {
            // Cannot track progress
            return [
                'best_score' => $correctAnswers,
                'correct_answers' => $correctAnswers,
                'total_exercises' => $totalExercises,
                'passed' => $scorePercentage >= 80,
            ];
        }

        $progress = $progressQuery->first();

        if (!$progress) {
            $progress = new EarTrainingUserProgress();
            $progress->user_id = $userId;
            $progress->guest_device_id = $guestDeviceId;
            $progress->ear_training_level_id = $level->id;
            $progress->attempts = 0;
        }

        $progress->attempts += 1;

        // Only update if it's better or new
        if (!$progress->exists || $scorePercentage >= $progress->score_percentage) {
            $progress->total_exercises = $totalExercises;
            $progress->completed_exercises = $totalExercises;
            $progress->correct_answers = $correctAnswers;
            $progress->score_percentage = $scorePercentage;
            $progress->status = 'completed';
        }
        $progress->save();

        // Save individual answers if provided
        if (isset($validated['answers']) && is_array($validated['answers'])) {
            foreach ($validated['answers'] as $index => $ans) {
                EarTrainingUserAnswer::create([
                    'ear_training_user_progress_id' => $progress->id,
                    'exercise_index' => $index,
                    'user_answer' => is_array($ans['user_answer']) ? json_encode($ans['user_answer']) : $ans['user_answer'],
                    'correct_answer' => is_array($ans['correct_answer']) ? json_encode($ans['correct_answer']) : $ans['correct_answer'],
                    'is_correct' => $ans['is_correct'],
                ]);
            }
        }

        $nextLevel = EarTrainingLevel::where('ear_training_stage_id', $level->ear_training_stage_id)
            ->where('level_number', '>', $level->level_number)
            ->where('is_active', true)
            ->orderBy('level_number')
            ->first();

        return [
            'best_score' => $progress->correct_answers,
            'correct_answers' => $correctAnswers,
            'total_exercises' => $totalExercises,
            'passed' => $scorePercentage >= 80,
            'next_level_id' => $nextLevel?->id,
            'is_guest' => $user === null,
        ];
    }

    /* ------------------------------------------------------------------
     * Private Helpers
     * ----------------------------------------------------------------*/

    /**
     * Retrieve all ear-training progress for the user.
     */
    private function getProgressForUser($user, $guestDevice = null): Collection
    {
        if ($user) {
            return EarTrainingUserProgress::where('user_id', $user->id)->get();
        }

        if ($guestDevice) {
            return EarTrainingUserProgress::where('guest_device_id', $guestDevice->id)->get();
        }

        return collect();
    }
}
