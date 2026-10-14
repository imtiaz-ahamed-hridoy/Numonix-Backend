<?php

namespace App\Http\Controllers\API\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Support\ContactRequest;
use App\Http\Requests\API\Support\ReportBugRequest;
use App\Http\Requests\API\Support\SuggestFeatureRequest;
use App\Services\SupportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * API controller for support, bug reports, and feature suggestions.
 */
class SupportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SupportService $supportService
    ) {}

    /**
     * Submit a contact or support request.
     */
public function contact(ContactRequest $request): JsonResponse
    {
        $data = $this->supportService->submitContact($request->validated());

        return $this->success('Support request submitted successfully', $data, 201);
    }

    /**
     * Submit a bug report with an optional screenshot.
     */
public function reportBug(ReportBugRequest $request): JsonResponse
    {
        $data = $this->supportService->reportBug(
            $request->validated(),
            $request->file('screenshot')
        );

        return $this->success('Bug report submitted successfully', $data, 201);
    }

    /**
     * Submit a feature suggestion.
     */
public function suggestFeature(SuggestFeatureRequest $request): JsonResponse
    {
        $data = $this->supportService->suggestFeature($request->validated());

        return $this->success('Feature suggestion submitted successfully', $data, 201);
    }
}
