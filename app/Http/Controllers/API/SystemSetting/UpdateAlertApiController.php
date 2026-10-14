<?php

namespace App\Http\Controllers\API\SystemSetting;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Public API controller for app update alert flags.
 *
 * Both endpoints are intentionally public (no authentication required)
 * so the mobile app can check them before the user even logs in.
 *
 * Response shape:
 *   { "data": true | false, "message": "...", "status": 200,
 *     "latest_version": string|null, "app_store_url": string|null, "play_store_url": string|null }
 */
class UpdateAlertApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SystemSettingService $systemSettingService
    ) {}

    /**
     * GET /api/app-update/soft
     *
     * Returns whether the admin has enabled the soft (dismissible) update alert.
     * The mobile app should show a skippable "New version available" dialog when true.
     */
    public function softUpdate(): JsonResponse
    {
        $setting = $this->systemSettingService->getSystemSetting();
        $isEnabled = $setting ? (bool) $setting->soft_update_alert : false;

        return $this->success(
            'Soft update alert status fetched successfully.',
            $isEnabled,
            200,
            $this->updateMeta($setting)
        );
    }

    /**
     * GET /api/app-update/force
     *
     * Returns whether the admin has enabled the force (blocking) update alert.
     * The mobile app must block all navigation and force the user to update when true.
     */
    public function forceUpdate(): JsonResponse
    {
        $setting = $this->systemSettingService->getSystemSetting();
        $isEnabled = $setting ? (bool) $setting->force_update_alert : false;

        return $this->success(
            'Force update alert status fetched successfully.',
            $isEnabled,
            200,
            $this->updateMeta($setting)
        );
    }

    /**
     * Optional metadata that the update dialog can use to deep-link
     * the user to the latest version in the stores.
     *
     * @return array{latest_version: string|null, app_store_url: string|null, play_store_url: string|null}
     */
    private function updateMeta(?SystemSetting $setting): array
    {
        return [
            'latest_version' => $setting?->latest_version ?? null,
            'app_store_url'  => $setting?->app_store_url ?? null,
            'play_store_url' => $setting?->play_store_url ?? null,
        ];
    }
}
