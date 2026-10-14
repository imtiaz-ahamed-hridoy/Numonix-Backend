<?php

namespace App\Http\Controllers\API\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Guest\IdentifyGuestDeviceRequest;
use App\Services\Guest\GuestDeviceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/guest/identify
 *
 * Flutter calls this endpoint to register/identify the guest device.
 * Accepts only `device_id` and `token` in the JSON body.
 *
 * The backend computes a deterministic guest_fingerprint using
 * HMAC-SHA256 of device_id + token with APP_KEY as the secret.
 * This fingerprint never changes for the same device_id + token pair.
 *
 * See also: GET /api/guest/device (requires validate.guest.headers middleware)
 */
class GuestDeviceController extends Controller
{
    use ApiResponse;

    private GuestDeviceService $guestDeviceService;

    public function __construct(GuestDeviceService $guestDeviceService)
    {
        $this->guestDeviceService = $guestDeviceService;
    }

    /**
     * POST /api/guest/identify
     */
    public function identify(IdentifyGuestDeviceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->guestDeviceService->identify(
            $validated['device_id'],
            $validated['token']
        );

        if ($result['status'] === 'error') {
            return $this->error($result['message'], $result['httpCode']);
        }

        return $this->success($result['message'], $result['data'], $result['httpCode']);
    }

    /**
     * GET /api/guest/device
     */
    public function device(Request $request): JsonResponse
    {
        $device = $request->input('_guest_device');

        if (! $device) {
            return $this->error('Guest device not found.', 404);
        }

        $info = $this->guestDeviceService->getDeviceInfo($device);

        return $this->success('Guest device found.', $info);
    }
}
