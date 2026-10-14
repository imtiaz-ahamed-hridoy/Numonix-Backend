<?php

namespace App\Http\Controllers\API\Cms;

use App\Http\Controllers\Controller;
use App\Services\Cms\CmsService;
use App\Traits\ApiResponse;

/**
 * API controller for glossary CMS content.
 */
class GlossaryApiController extends Controller
{
    use ApiResponse;

    private CmsService $cmsService;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get the glossary terms.
     */
public function index()
    {
        try {
            $data = $this->cmsService->getGlossary();

            return $this->success('Glossary fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch Glossary.', 500);
        }
    }
}
