<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * API controller for the home page.
 *
 * Returns aggregated home page data including greeting,
 * headline, continue-learning hero card, and module cards
 * with lock status for both authenticated and guest users.
 */
class HomeController extends Controller
{
    use ApiResponse;

    private HomeService $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    /**
     * GET /api/home
     *
     * Returns the home page data:
     *  - greeting (time-based)
     *  - headline
     *  - continue_learning (hero card for returning users)
     *  - modules (list of module cards with lock status)
     */
    public function index(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $request->input('_guest_device');

            $payload = $this->homeService->buildHomePayload($user, $guestDevice);

            return $this->success('Home page loaded.', $payload);
        } catch (\Throwable $e) {
            return $this->error('Failed to load home page.', 500);
        }
    }
}
