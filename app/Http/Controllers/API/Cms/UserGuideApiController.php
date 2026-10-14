<?php

namespace App\Http\Controllers\API\Cms;

use App\Http\Controllers\Controller;
use App\Services\Cms\CmsService;
use App\Traits\ApiResponse;

/**
 * API controller for user guide CMS content.
 */
class UserGuideApiController extends Controller
{
    use ApiResponse;

    private CmsService $cmsService;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get the user guide content with formatted tabs.
     */
public function index()
    {
        try {
            $data = $this->cmsService->getUserGuide();

            return $this->success('User Guide fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch User Guide.', 500);
        }
    }
}
