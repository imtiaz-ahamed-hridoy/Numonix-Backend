<?php

namespace App\Services\Auth;

use App\Exceptions\GuestDeviceSyncException;
use App\Models\User;
use App\Repositories\Interfaces\GuestDeviceRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\GuestDeviceSyncService;
use App\Services\GuestFingerprintService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service layer for user registration.
 *
 * Handles new user creation, OTP generation for email verification,
 * and optional guest-device data migration after successful registration.
 * Guest device sync failures are non-fatal to ensure registration completes.
 */
class RegistrationService
{
    use ApiResponse;

    protected UserRepositoryInterface $userRepository;

    protected EmailVerificationService $emailVerificationService;

    protected GuestDeviceSyncService $guestDeviceSyncService;

    protected GuestFingerprintService $fingerprintService;

    protected GuestDeviceRepositoryInterface $deviceRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        EmailVerificationService $emailVerificationService,
        GuestDeviceSyncService $guestDeviceSyncService,
        GuestFingerprintService $fingerprintService,
        GuestDeviceRepositoryInterface $deviceRepository
    ) {
        $this->userRepository = $userRepository;
        $this->emailVerificationService = $emailVerificationService;
        $this->guestDeviceSyncService = $guestDeviceSyncService;
        $this->fingerprintService = $fingerprintService;
        $this->deviceRepository = $deviceRepository;
    }

    /**
     * Register a new user.
     *
     * The transaction only covers user creation + OTP generation.
     * Guest-device sync runs AFTER the transaction commits so that a sync
     * failure can never prevent registration from completing.
     *
     * @param  array  $userData  Validated request data (may include device_id / token)
     * @return array{user: User, otp: string|null, guest_synced: bool}
     *
     * @throws Throwable Only when user creation itself fails.
     */
    public function registerUser(array $userData): array
    {
        // Extract guest-device credentials before persisting the user
        $deviceId = $userData['device_id'] ?? null;
        $token = $userData['token'] ?? null;
        $fingerprint = $userData['guest_fingerprint'] ?? null;
        unset($userData['device_id'], $userData['token'], $userData['guest_fingerprint']);

        // -- Phase 1: Create user + send OTP (atomic) --------------------
        DB::beginTransaction();
        try {
            $user = $this->userRepository->create($userData);
            $otpResult = $this->emailVerificationService->sendVerificationOtp($user->email);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // -- Phase 2: Sync guest data (non-fatal) ------------------------
        $guestSynced = false;

        if ($deviceId && $token && $fingerprint) {
            // Server is source of truth: validate fingerprint via the
            // centralized GuestFingerprintService::matches() which handles
            // normalization (trim + strtolower) and hash_equals() internally.
            if (! $this->fingerprintService->matches($deviceId, $token, $fingerprint)) {
                Log::warning('Guest device sync skipped during registration: fingerprint mismatch', [
                    'device_id' => $deviceId,
                    'fingerprint_match' => false,
                ]);
            } else {
                // Re-compute server fingerprint for the DB lookup (matches()
                // normalises internally but does not expose the computed value).
                $serverFingerprint = $this->fingerprintService->compute($deviceId, $token);

                $device = $this->deviceRepository->findByIdentifierUnmigrated($deviceId, $token, $serverFingerprint);

                if ($device) {
                    try {
                        $this->guestDeviceSyncService->sync($user, $device);
                        $guestSynced = true;
                    } catch (GuestDeviceSyncException $e) {
                        // Sync failure is non-fatal; user was already committed.
                        Log::error('Guest device sync failed during registration - user created successfully', [
                            'user_id' => $user->id,
                            'guest_device_id' => $device->id,
                            'error' => $e->getMessage(),
                        ]);
                    } catch (Throwable $e) {
                        // Catch any unexpected exception so registration is never aborted.
                        Log::error('Unexpected error during guest sync - user created successfully', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } else {
                    // Log exactly why it was skipped to help debugging
                    $exists = $this->deviceRepository->existsByIdentifier($deviceId, $token);
                    Log::warning('Guest device sync skipped during registration: Device not found or already migrated', [
                        'device_id' => $deviceId,
                        'token_provided' => ! empty($token),
                        'device_exists_in_db_at_all' => $exists,
                        'reason' => $exists ? 'Device exists but is already migrated (user_id is not null)' : 'Device does not exist at all (might have been deleted in a previous sync)',
                    ]);
                }
            }
        }

        return [
            'user' => $user,
            'otp' => $otpResult['otp'] ?? null,
            'guest_synced' => $guestSynced,
        ];
    }
}
