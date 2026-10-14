<?php

namespace App\Http\Controllers\Web\Backend\GuestAccess;

use App\Http\Controllers\Controller;
use App\Models\Learning;
use App\Services\GuestAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestAnalyticsController extends Controller
{
    private GuestAnalyticsService $analytics;

    public function __construct(GuestAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Display the full analytics dashboard.
     */
    public function index(): View
    {
        $overview = $this->analytics->getOverviewStats();
        $tierDistribution = $this->analytics->getUserTierDistribution();
        $funnel = $this->analytics->getConversionFunnel();
        $dailySessions = $this->analytics->getDailySessionsChart(30);
        $registrationTrend = $this->analytics->getRegistrationTrend(30);
        $contentEngagement = $this->analytics->getContentEngagement(30, 15);
        $topLocked = $this->analytics->getTopLockedContent(30, 10);
        $activityByTier = $this->analytics->getActivityByTier(30);

        // Resolve content titles for engagement tables
        $contentEngagement = $this->resolveContentTitles($contentEngagement);
        $topLocked = $this->resolveContentTitles($topLocked);

        return view('backend.layouts.guest-analytics.index', compact(
            'overview',
            'tierDistribution',
            'funnel',
            'dailySessions',
            'registrationTrend',
            'contentEngagement',
            'topLocked',
            'activityByTier'
        ));
    }

    /**
     * AJAX: Get chart data with a custom date range.
     */
    public function chartData(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);
        $days = min(max($days, 7), 90); // Clamp between 7 and 90

        return response()->json([
            'daily_sessions' => $this->analytics->getDailySessionsChart($days),
            'registration_trend' => $this->analytics->getRegistrationTrend($days),
            'conversion_funnel' => $this->analytics->getConversionFunnel(),
            'tier_distribution' => $this->analytics->getUserTierDistribution(),
            'activity_by_tier' => $this->analytics->getActivityByTier($days),
            'content_engagement' => $this->analytics->getContentEngagement($days, 15),
        ]);
    }

    /**
     * Resolve content IDs to human-readable titles.
     */
    private function resolveContentTitles(array $items): array
    {
        $learningIds = collect($items)
            ->where('content_type', 'learning')
            ->pluck('content_id')
            ->filter()
            ->unique()
            ->toArray();

        $learningTitles = [];
        if (! empty($learningIds)) {
            $learningTitles = Learning::whereIn('id', $learningIds)
                ->pluck('title', 'id')
                ->toArray();
        }

        return array_map(function ($item) use ($learningTitles) {
            $item['title'] = match ($item['content_type']) {
                'learning' => $learningTitles[$item['content_id']] ?? "Learning #{$item['content_id']}",
                'ear_training' => "Ear Training Level #{$item['content_id']}",
                'quiz' => "Quiz #{$item['content_id']}",
                default => "#{$item['content_id']}",
            };

            return $item;
        }, $items);
    }
}
