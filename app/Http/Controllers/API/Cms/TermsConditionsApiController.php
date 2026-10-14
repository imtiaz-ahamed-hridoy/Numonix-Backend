<?php

namespace App\Http\Controllers\API\Cms;

use App\Http\Controllers\Controller;
use App\Services\Cms\CmsService;
use App\Traits\ApiResponse;

/**
 * API controller for terms and conditions CMS content.
 */
class TermsConditionsApiController extends Controller
{
    use ApiResponse;

    private CmsService $cmsService;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get the terms and conditions content.
     */
public function index()
    {
        try {
            $data = $this->cmsService->getTermsConditions();

            return $this->success('Terms & Conditions fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch Terms & Conditions.', 500);
        }
    }
}
