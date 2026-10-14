<?php

namespace App\Http\Controllers\API\Cms;

use App\Http\Controllers\Controller;
use App\Services\Cms\CmsService;
use App\Traits\ApiResponse;

/**
 * API controller for About NumoniX CMS content.
 */
class AboutNumoniXApiController extends Controller
{
    use ApiResponse;

    private CmsService $cmsService;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get the About NumoniX content.
     */
public function index()
    {
        try {
            $data = $this->cmsService->getAboutNumoniX();

            return $this->success('About NumoniX fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch About NumoniX.', 500);
        }
    }
}
