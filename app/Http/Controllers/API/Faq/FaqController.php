<?php

namespace App\Http\Controllers\API\Faq;

use App\Http\Controllers\Controller;
use App\Services\FaqService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * API controller for FAQ retrieval.
 */
class FaqController extends Controller
{
    use ApiResponse;

    public function __construct(
        private FaqService $faqService
    ) {}

    /**
     * Get all FAQs grouped by section.
     */
public function index(): JsonResponse
    {
        $grouped = $this->faqService->getGroupedFaqs();

        if (! empty($grouped)) {
            return $this->ok('Data found', $grouped, 200);
        }

        return $this->success('No Data found', [], 200);
    }
}
