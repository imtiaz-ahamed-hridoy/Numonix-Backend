<?php

namespace App\Http\Controllers\API\SystemSetting;

use App\Http\Controllers\Controller;
use App\Services\SystemSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * API controller for system configuration retrieval.
 */
class SystemSettingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SystemSettingService $systemSettingService
    ) {}

    /**
     * Get the system configuration settings.
     */
public function systemSetting(): JsonResponse
    {
        $systemSetting = $this->systemSettingService->getSystemSetting();
        if ($systemSetting) {
            return $this->ok('Data Retrieve Successfully!', $systemSetting, 200);
        }

        return $this->error('System Setting not found', 500);
    }
}
