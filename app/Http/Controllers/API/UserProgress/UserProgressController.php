<?php

namespace App\Http\Controllers\API\UserProgress;

use App\Http\Controllers\Controller;
use App\Models\GuestDevice;
use App\Services\UserProgressService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for user learning progress tracking.
 */
class UserProgressController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserProgressService $progressService
    ) {}

    private function getGuestDevice(Request $request): ?GuestDevice
    {
        return $request->input('_guest_device');
    }

    /**
     * Get the user's learning progress overview including completed and in-progress items.
     */
public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice($request);

            $data = $this->progressService->getProgress($user, $guestDevice);

            return $this->success('User progress fetched successfully.', $data);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('UserProgressController error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $msg = app()->environment('local', 'testing') 
                ? 'Failed to fetch progress: '.$e->getMessage() 
                : 'Failed to fetch progress.';

            return $this->error($msg, 500);
        }
    }
}
