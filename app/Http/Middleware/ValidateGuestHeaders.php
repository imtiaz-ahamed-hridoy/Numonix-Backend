<?php

namespace App\Http\Middleware;

use App\Models\GuestDevice;
use App\Services\GuestFingerprintService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REQUIRED guest header validation middleware.
 *
 * Every guest-aware endpoint MUST send all three headers:
 *   X-Guest-Device-Id
 *   X-Guest-Token
 *   X-Guest-Fingerprint
 *
 * This middleware:
 *   1. Verifies all three headers are present and non-empty.
 *   2. Confirms a matching GuestDevice record exists.
 *   3. Recomputes the fingerprint server-side via GuestFingerprintService.
 *   4. Compares the client-provided fingerprint against the server-computed
 *      authoritative fingerprint using GuestFingerprintService::matches()
 *      (which internally uses hash_equals() - timing-attack resistant).
 *   5. Separately verifies the STORED fingerprint also matches the server
 *      computation (data integrity check - distinct from the client check).
 *   6. Confirms the device has NOT been migrated to a user.
 *   7. Attaches the resolved GuestDevice to $request->_guest_device.
 *
 * SECURITY:
 *   - Never trust the fingerprint sent by the client.
 *   - Always recompute using GuestFingerprintService (HMAC-SHA256 with APP_KEY).
 *   - Use hash_equals() for secure comparison (timing-attack resistant).
 *   - Step 4 and Step 5 are SEPARATE checks with SEPARATE error codes:
 *       422 -> client fingerprint does not match server computation
 *       409 -> stored fingerprint is missing or does not match server computation
 *             (indicates data corruption or records from before the fingerprint
 *              system was introduced - device must re-identify)
 *   - Reject the request if ANY validation fails - no controller executes.
 *
 * ROOT CAUSE PREVENTION:
 *   The previous implementation used:
 *     empty($device->guest_fingerprint) || !hash_equals($server, $client)
 *   This caused a NULL stored fingerprint to short-circuit into a 422 "mismatch"
 *   message even when the client's fingerprint was perfectly valid. This has been
 *   corrected: the NULL/empty stored fingerprint is now a distinct 409 integrity
 *   failure, checked AFTER the client fingerprint is confirmed valid.
 */
class ValidateGuestHeaders
{
    private GuestFingerprintService $fingerprintService;

    public function __construct(GuestFingerprintService $fingerprintService)
    {
        $this->fingerprintService = $fingerprintService;
    }

    private const REQUIRED_HEADERS = [
        'X-Guest-Device-Id',
        'X-Guest-Token',
        'X-Guest-Fingerprint',
    ];

    private function normalizeHeaderValue(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    /**
     * Handle an incoming request.
     *
     * - Authenticated users pass through without guest header validation.
     * - Unauthenticated users MUST provide all three guest headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Authenticated users bypass guest header validation
        if (auth('sanctum')->check()) {
            return $next($request);
        }

        $deviceId          = $this->normalizeHeaderValue($request->header('X-Guest-Device-Id'));
        $token             = $this->normalizeHeaderValue($request->header('X-Guest-Token'));
        $clientFingerprint = $this->normalizeHeaderValue($request->header('X-Guest-Fingerprint'));

        // -- 1. All three headers must be present and non-empty ------------
        $missing = [];
        if (empty($deviceId))          $missing[] = 'X-Guest-Device-Id';
        if (empty($token))             $missing[] = 'X-Guest-Token';
        if (empty($clientFingerprint)) $missing[] = 'X-Guest-Fingerprint';

        if (!empty($missing)) {
            $requiredHeaders = 'The following guest headers are required: ' . implode(', ', self::REQUIRED_HEADERS);

            return response()->json([
                'message' => $requiredHeaders,
                'errors'  => [
                    'headers' => [$requiredHeaders],
                ],
                'status'  => 422,
            ], 422);
        }

        // -- 2. Device must exist -----------------------------------------
        $device = GuestDevice::where('device_id', $deviceId)
            ->where('token', $token)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Invalid guest credentials.',
                'errors'  => [
                    'X-Guest-Device-Id' => ['No matching device found for the given device_id and token combination.'],
                    'X-Guest-Token'     => ['No matching device found for the given device_id and token combination.'],
                ],
                'status'  => 401,
            ], 401);
        }

        // -- 3. Validate client fingerprint against server computation -----
        //
        // GuestFingerprintService::matches() recomputes the HMAC-SHA256
        // fingerprint from device_id + token and compares it to the provided
        // value using hash_equals() (timing-attack resistant). Both sides are
        // normalised (trim + strtolower) before comparison.
        //
        // A failure here means the client sent a fingerprint that does NOT
        // correspond to this device_id + token combination - invalid request.
        if (!$this->fingerprintService->matches($deviceId, $token, $clientFingerprint)) {
            return response()->json([
                'message' => 'Guest fingerprint mismatch. The provided fingerprint does not match the server-computed fingerprint for this device.',
                'errors'  => [
                    'X-Guest-Fingerprint' => ['The provided fingerprint is invalid. It must match the HMAC-SHA256 of device_id and token signed with APP_KEY.'],
                ],
                'status'  => 422,
            ], 422);
        }

        // -- 4. Data integrity check: stored fingerprint must also match ---
        //
        // At this point the client fingerprint is confirmed valid (step 3).
        // Now verify the value persisted in the DB also matches the server
        // computation. A failure here indicates:
        //   a) The device was created before the fingerprint system existed
        //      (guest_fingerprint was NULL in the original nullable schema).
        //   b) Data corruption or manual DB tampering.
        //
        // This is deliberately a separate check with a separate 409 status
        // and a different message so operators can distinguish it from a
        // genuine client-side mismatch (422).
        //
        // The device must re-identify via POST /api/guest/identify to get
        // a correct fingerprint stored in the DB.
        $storedFingerprint = $device->guest_fingerprint;
        $serverFingerprint = $this->fingerprintService->compute($deviceId, $token);

        if (empty($storedFingerprint) || !hash_equals($serverFingerprint, strtolower(trim($storedFingerprint)))) {
            return response()->json([
                'message' => 'Device identity integrity check failed. Please re-identify the device via POST /api/guest/identify.',
                'errors'  => [
                    'device' => ['The stored fingerprint does not match the server-computed fingerprint. The device must re-identify to restore a valid fingerprint.'],
                ],
                'status'  => 409,
            ], 409);
        }

        // -- 5. Device must not have been migrated to a registered user ---
        if ($device->isMigrated()) {
            return response()->json([
                'message' => 'This guest device has already been registered to a user.',
                'errors'  => [
                    'device' => ['The device has already been associated with an account. Please log in instead.'],
                ],
                'status'  => 409,
            ], 409);
        }

        // -- All valid - attach device to request for downstream use ------
        $device->markActivity();
        $request->merge(['_guest_device' => $device]);

        return $next($request);
    }
}
