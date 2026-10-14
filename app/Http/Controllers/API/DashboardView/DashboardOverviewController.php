<?php

namespace App\Http\Controllers\API\DashboardView;

use App\Http\Controllers\Controller;
use App\Services\DashboardOverviewService;
use App\Traits\ApiResponse;

/**
 * API controller for admin dashboard overview statistics.
 */
class DashboardOverviewController extends Controller
{
    use ApiResponse;

    private DashboardOverviewService $dashboardService;

    public function __construct(DashboardOverviewService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $data = $this->dashboardService->getStats();

        return $this->ok('success', $data);
    }
}
