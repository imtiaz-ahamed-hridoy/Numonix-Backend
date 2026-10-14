<?php

namespace App\Services;

use App\Exceptions\GuestDeviceSyncException;
use App\Models\GuestDevice;
use App\Models\User;
use App\Repositories\Interfaces\EarTrainingAttemptRepositoryInterface;
use App\Repositories\Interfaces\LearningProgressRepositoryInterface;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles the full lifecycle of guest device data on registration:
 *
 *  1. Sync: reassign all server-side guest data (ear training attempts,
 *     quiz attempts, learning progress, checklist progress) to the new user.
 *     Matches by guest_device_id only - the ValidateGuestHeaders middleware
 *     ensures all guest data is linked to a validated device record.
 *
 *  2. Mark: set user_id on the GuestDevice so it is no longer "unmigrated".
 *
 *  3. Clean up: delete the guest device activity log entries; the GuestDevice
 *     record itself is deleted after migration.
 *
 * This runs ONCE during registration when the user provides device_id + token
 * + guest_fingerprint. It runs OUTSIDE the user-creation transaction so a
 * sync failure never prevents registration from completing.
 */
class GuestDeviceSyncService
{
    private EarTrainingAttemptRepositoryInterface $earTrainingAttemptRepository;
    private QuizAttemptRepositoryInterface $quizAttemptRepository;
    private LearningProgressRepositoryInterface $learningProgressRepository;

    public function __construct(
        EarTrainingAttemptRepositoryInterface $earTrainingAttemptRepository,
        QuizAttemptRepositoryInterface $quizAttemptRepository,
        LearningProgressRepositoryInterface $learningProgressRepository
    ) {
        $this->earTrainingAttemptRepository = $earTrainingAttemptRepository;
        $this->quizAttemptRepository = $quizAttemptRepository;
        $this->learningProgressRepository = $learningProgressRepository;
    }

    /**
     * Sync all guest device data to the user and clean up.
     *
     * Wraps everything in its own atomic transaction. If any step fails,
     * the whole sync is rolled back and a GuestDeviceSyncException is thrown.
     * The caller (RegistrationService) handles this exception gracefully.
     *
     * @param  User  $user  The newly registered user
     * @param  GuestDevice  $device  The matched guest device record
     *
     * @throws GuestDeviceSyncException
     */
    public function sync(User $user, GuestDevice $device): void
    {
        Log::info('Starting guest device sync', [
            'user_id' => $user->id,
            'guest_device_id' => $device->id,
        ]);

        try {
            DB::transaction(function () use ($user, $device): void {
                $this->reassignEarTrainingAttempts($user, $device);
                $this->reassignQuizAttempts($user, $device);
                $this->reassignLearningProgress($user, $device);
                $this->reassignChecklistProgress($user, $device);
                $device->migrateToUser($user);
                $device->cleanupGuestData();
            });
        } catch (GuestDeviceSyncException $e) {
            // Already a domain exception - bubble up directly
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Guest device sync transaction failed', [
                'user_id' => $user->id,
                'guest_device_id' => $device->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new GuestDeviceSyncException(
                'Guest device sync failed: '.$e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        Log::info('Guest device sync completed successfully', [
            'user_id' => $user->id,
            'guest_device_id' => $device->id,
        ]);
    }

    /* ------------------------------------------------------------------
     * Private: reassign server-side tracking records
     * ----------------------------------------------------------------*/

    /**
     * Reassign ear training quiz attempts from guest device to registered user.
     * Matches by guest_device_id only.
     */
    private function reassignEarTrainingAttempts(User $user, GuestDevice $device): void
    {
        $updated = $this->earTrainingAttemptRepository->reassignByGuestDeviceId($device->id, $user->id);

        if ($updated > 0) {
            Log::info('Ear training attempts reassigned', [
                'user_id' => $user->id,
                'guest_device_id' => $device->id,
                'count' => $updated,
            ]);
        }
    }

    /**
     * Reassign quiz attempts from guest device to registered user.
     * Matches by guest_device_id only.
     */
    private function reassignQuizAttempts(User $user, GuestDevice $device): void
    {
        $updated = $this->quizAttemptRepository->reassignByGuestDeviceId($device->id, $user->id);

        if ($updated > 0) {
            Log::info('Quiz attempts reassigned', [
                'user_id' => $user->id,
                'guest_device_id' => $device->id,
                'count' => $updated,
            ]);
        }
    }

    /**
     * Reassign learning progress records from guest device to registered user.
     * Matches by guest_device_id only.
     */
    private function reassignLearningProgress(User $user, GuestDevice $device): void
    {
        $updated = $this->learningProgressRepository->reassignProgressByGuestDeviceId($device->id, $user->id);

        if ($updated > 0) {
            Log::info('Learning progress reassigned', [
                'user_id' => $user->id,
                'guest_device_id' => $device->id,
                'count' => $updated,
            ]);
        }
    }

    /**
     * Reassign learning checklist progress records from guest device to registered user.
     * Matches by guest_device_id only.
     */
    private function reassignChecklistProgress(User $user, GuestDevice $device): void
    {
        $updated = $this->learningProgressRepository->reassignChecklistProgressByGuestDeviceId($device->id, $user->id);

        if ($updated > 0) {
            Log::info('Checklist progress reassigned', [
                'user_id' => $user->id,
                'guest_device_id' => $device->id,
                'count' => $updated,
            ]);
        }
    }
}
