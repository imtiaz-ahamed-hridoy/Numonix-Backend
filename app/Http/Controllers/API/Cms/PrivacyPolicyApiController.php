<?php

namespace App\Http\Controllers\API\Cms;

use App\Http\Controllers\Controller;
use App\Services\Cms\CmsService;
use App\Traits\ApiResponse;

/**
 * API controller for privacy policy CMS content.
 */
class PrivacyPolicyApiController extends Controller
{
    use ApiResponse;

    private CmsService $cmsService;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get the privacy policy content.
     */
public function index()
    {
        try {
            $data = $this->cmsService->getPrivacyPolicy();

            return $this->success('Privacy Policy fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch Privacy Policy.', 500);
        }
    }
}
