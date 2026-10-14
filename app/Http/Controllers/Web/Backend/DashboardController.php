<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Faq;
use App\Models\Learning;
use App\Models\Review;
use App\Repositories\Interfaces\GuestActivityLogRepositoryInterface;
use App\Repositories\Interfaces\FounderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    protected GuestActivityLogRepositoryInterface $activityLogRepository;
    protected FounderRepositoryInterface $founderRepository;

    public function __construct(
        GuestActivityLogRepositoryInterface $activityLogRepository,
        FounderRepositoryInterface $founderRepository
    ) {
        $this->activityLogRepository = $activityLogRepository;
        $this->founderRepository = $founderRepository;
    }

    /**
     * Display the dashboard page.
     */
    public function index(): View
    {
        if (Auth::check() && (Auth::user()->role === 'Admin')) {
            // Single aggregation for all user counts (replaces 4 queries)
            $userStats = \DB::selectOne("
                SELECT
                    SUM(CASE WHEN role = 'Admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN role != 'Admin' THEN 1 ELSE 0 END) as users,
                    SUM(CASE WHEN role != 'Admin' AND (subscription_tier IS NULL OR subscription_tier = 'tier_1') THEN 1 ELSE 0 END) as tier1_users,
                    SUM(CASE WHEN role != 'Admin' AND subscription_tier IS NOT NULL AND subscription_tier != 'tier_1' THEN 1 ELSE 0 END) as subscribed_users
                FROM users
            ");
            $admins = (int) $userStats->admins;
            $users = (int) $userStats->users;
            $tier1Users = (int) $userStats->tier1_users;
            $subscribedUsers = (int) $userStats->subscribed_users;

            $email = EmailLog::where('status', 'success')->count();
            $faq = Faq::where('status', 'Active')->count();
            $learning = Learning::count();
            $publishedLearning = Learning::where('is_published', true)->count();
            $review = Review::where('status', 'Active')->count();
            $activityLog = Activity::count();

            // Guest sessions today
            $guestSessionsToday = $this->activityLogRepository->countDistinctGuestSessionsToday();

            $founderStats = $this->founderRepository->getFounderStats();

            return view('backend.layouts.dashboard.index',
                compact('admins', 'users', 'email', 'faq', 'learning', 'publishedLearning',
                    'review', 'activityLog', 'tier1Users', 'subscribedUsers', 'guestSessionsToday', 'founderStats'));
        } else {
            return view('auth.layouts.login');
        }
    }
}
