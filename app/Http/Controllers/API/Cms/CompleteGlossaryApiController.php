<?php

namespace App\Http\Controllers\API\Cms;

use App\Http\Controllers\Controller;
use App\Services\Cms\CmsService;
use App\Traits\ApiResponse;

/**
 * API controller for complete glossary CMS content.
 */
class CompleteGlossaryApiController extends Controller
{
    use ApiResponse;

    private CmsService $cmsService;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get the complete glossary content with sections and items.
     */
public function index()
    {
        try {
            $data = $this->cmsService->getCompleteGlossary();

            return $this->success('Complete Glossary fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch Complete Glossary.', 500);
        }
    }
}
