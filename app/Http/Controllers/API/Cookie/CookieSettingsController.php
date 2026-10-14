<?php

namespace App\Http\Controllers\API\Cookie;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Cookie\UpdateCookieSettingsRequest;
use App\Services\CookieSettingsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for cookie consent settings.
 */
class CookieSettingsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CookieSettingsService $cookieSettingsService
    ) {}

    /**
     * Get the current cookie consent settings for the user or guest.
     */
public function show(Request $request): JsonResponse
    {
        $data = $this->cookieSettingsService->showSettings($request);

        return $this->success('Cookie settings retrieved successfully.', $data);
    }

    /**
     * Update cookie consent settings.
     */
public function update(UpdateCookieSettingsRequest $request): JsonResponse
    {
        $data = $this->cookieSettingsService->updateSettings($request, $request->validated());

        return $this->success('Cookie settings updated successfully.', $data);
    }
}
