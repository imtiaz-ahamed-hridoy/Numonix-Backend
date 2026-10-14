<?php

namespace App\Services\Guest;

use App\Exceptions\DeviceConflictException;
use App\Models\GuestDevice;
use App\Repositories\Interfaces\GuestDeviceRepositoryInterface;
use App\Services\GuestFingerprintService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service layer for guest device identification and management.
 *
 * Handles guest device registration, fingerprint-based identity
 * resolution, conflict detection, and device info retrieval.
 */
class GuestDeviceService
{
    private GuestFingerprintService $fingerprintService;
    private GuestDeviceRepositoryInterface $deviceRepository;

    public function __construct(
        GuestFingerprintService $fingerprintService,
        GuestDeviceRepositoryInterface $deviceRepository
    ) {
        $this->fingerprintService = $fingerprintService;
        $this->deviceRepository = $deviceRepository;
    }

    /**
     * Identify or register a guest device by device_id and token.
     *
     * @return array{status: string, data: array, httpCode: int}
     */
    public function identify(string $deviceId, string $token): array
    {
        $guestFingerprint = $this->fingerprintService->compute($deviceId, $token);

        // -- 1. Check by deterministic fingerprint (fast path) ------------
        $existingByFingerprint = $this->deviceRepository->findByFingerprint($guestFingerprint);

        if ($existingByFingerprint) {
            if ($existingByFingerprint->device_id !== $deviceId ||
                $existingByFingerprint->token !== $token) {
                Log::warning('Guest device fingerprint collision or data corruption', [
                    'stored_device_id' => $existingByFingerprint->device_id,
                    'stored_token' => $existingByFingerprint->token,
                    'request_device_id' => $deviceId,
                    'request_token' => $token,
                    'fingerprint' => $guestFingerprint,
                ]);

                return [
                    'status' => 'error',
                    'message' => 'Device identity integrity check failed.',
                    'httpCode' => 500,
                ];
            }

            $existingByFingerprint->markActivity();

            return [
                'status' => 'success',
                'message' => 'Guest device identified.',
                'httpCode' => 200,
                'data' => [
                    'guest_device_id' => $existingByFingerprint->id,
                    'device_id' => $existingByFingerprint->device_id,
                    'token' => $existingByFingerprint->token,
                    'guest_fingerprint' => $existingByFingerprint->guest_fingerprint,
                    'is_new' => false,
                ],
            ];
        }

        // -- 2. Check identity conflicts ---------------------------------
        $existingById = $this->deviceRepository->findByDeviceId($deviceId);
        if ($existingById) {
            return [
                'status' => 'error',
                'message' => 'Device identity mismatch. This device_id is already registered with a different token.',
                'httpCode' => 403,
            ];
        }

        $existingByToken = $this->deviceRepository->findByToken($token);
        if ($existingByToken) {
            return [
                'status' => 'error',
                'message' => 'The provided token is already registered to another device.',
                'httpCode' => 409,
            ];
        }

        // -- 3. Create new device in a transaction ------------------------
        try {
            $device = DB::transaction(function () use ($deviceId, $token, $guestFingerprint): GuestDevice {
                $existing = $this->deviceRepository->findByFingerprintLockForUpdate($guestFingerprint);

                if ($existing) {
                    return $existing;
                }

                $conflictById = $this->deviceRepository->findByDeviceIdLockForUpdate($deviceId);
                if ($conflictById) {
                    throw new DeviceConflictException('device_id', $deviceId);
                }

                $conflictByToken = $this->deviceRepository->findByTokenLockForUpdate($token);
                if ($conflictByToken) {
                    throw new DeviceConflictException('token', $token);
                }

                return $this->deviceRepository->create([
                    'device_id' => $deviceId,
                    'token' => $token,
                    'guest_fingerprint' => $guestFingerprint,
                    'last_activity_at' => now(),
                ]);
            });

            if ($device->wasRecentlyCreated === false) {
                $device->markActivity();

                return [
                    'status' => 'success',
                    'message' => 'Guest device identified.',
                    'httpCode' => 200,
                    'data' => [
                        'guest_device_id' => $device->id,
                        'device_id' => $device->device_id,
                        'token' => $device->token,
                        'guest_fingerprint' => $device->guest_fingerprint,
                        'is_new' => false,
                    ],
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Guest device registered.',
                'httpCode' => 201,
                'data' => [
                    'guest_device_id' => $device->id,
                    'device_id' => $device->device_id,
                    'token' => $device->token,
                    'guest_fingerprint' => $device->guest_fingerprint,
                    'is_new' => true,
                ],
            ];

        } catch (DeviceConflictException $e) {
            return [
                'status' => 'error',
                'message' => $e->getField() === 'device_id'
                    ? 'The provided device_id is already registered to another device.'
                    : 'The provided token is already registered to another device.',
                'httpCode' => 409,
            ];
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') ||
                str_contains($e->getMessage(), 'UNIQUE constraint')) {
                $device = $this->deviceRepository->findByFingerprint($guestFingerprint);
                if ($device) {
                    $device->markActivity();

                    return [
                        'status' => 'success',
                        'message' => 'Guest device identified.',
                        'httpCode' => 200,
                        'data' => [
                            'guest_device_id' => $device->id,
                            'device_id' => $device->device_id,
                            'token' => $device->token,
                            'guest_fingerprint' => $device->guest_fingerprint,
                            'is_new' => false,
                        ],
                    ];
                }

                Log::error('Guest device creation failed due to duplicate key', [
                    'device_id' => $deviceId,
                    'token' => $token,
                    'fingerprint' => $guestFingerprint,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'status' => 'error',
                    'message' => 'A device with these credentials was just registered. Please retry.',
                    'httpCode' => 409,
                ];
            }

            Log::error('Guest device creation database error', [
                'device_id' => $deviceId,
                'token' => $token,
                'fingerprint' => $guestFingerprint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'error',
                'message' => 'A database error occurred. Please try again later.',
                'httpCode' => 500,
            ];
        }
    }

    /**
     * Get device info for the given guest device.
     */
    public function getDeviceInfo(?GuestDevice $device): ?array
    {
        if (! $device) {
            return null;
        }

        return [
            'guest_device_id' => $device->id,
            'device_id' => $device->device_id,
            'has_activity' => $device->activityLogs()->count() > 0,
            'is_migrated' => $device->isMigrated(),
            'last_activity_at' => $device->last_activity_at?->toIso8601String(),
        ];
    }
}
