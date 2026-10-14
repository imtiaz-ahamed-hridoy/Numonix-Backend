<?php

namespace App\Http\Controllers\Web\Backend\Founder;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\FounderReviewSubmission;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Admin Founder Programme Dashboard Controller (Spec 3.16).
 *
 * Displays:
 * - Total founder count
 * - Active trial count / expired trial count
 * - Review form completion rate
 * - Conversion rate (founders who paid 24.99)
 */
class FounderDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getFounderStats();
        $founders = $this->getPaginatedFounders($request);

        return view('backend.founder.index', compact('stats', 'founders'));
    }

    public function export()
    {
        $founders = User::where('is_founder', true)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'founder_trial_ends_at',
                   'founder_review_completed', 'founder_converted_at', 'subscription_tier',
                   'founder_day25_sent_at', 'founder_day30_sent_at', 'created_at']);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="founders-export-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($founders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Trial Ends At', 'Review Completed',
                             'Converted At', 'Tier', 'Day25 Sent', 'Day30 Sent', 'Registered At']);
            foreach ($founders as $founder) {
                fputcsv($file, [
                    $founder->id,
                    $founder->name,
                    $founder->email,
                    $founder->founder_trial_ends_at?->toDateTimeString(),
                    $founder->founder_review_completed ? 'Yes' : 'No',
                    $founder->founder_converted_at?->toDateTimeString(),
                    $founder->subscription_tier,
                    $founder->founder_day25_sent_at?->toDateTimeString(),
                    $founder->founder_day30_sent_at?->toDateTimeString(),
                    $founder->created_at?->toDateTimeString(),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getFounderStats(): array
    {
        $totalFounders = User::where('is_founder', true)->count();
        $now = Carbon::now();

        $activeTrials = User::where('is_founder', true)
            ->whereNull('founder_converted_at')
            ->where('founder_trial_ends_at', '>', $now)->count();

        $expiredTrials = User::where('is_founder', true)
            ->whereNull('founder_converted_at')
            ->where('founder_trial_ends_at', '<=', $now)->count();

        $converted = User::where('is_founder', true)
            ->whereNotNull('founder_converted_at')->count();

        $reviewsCompleted = User::where('is_founder', true)
            ->where('founder_review_completed', true)->count();

        $conversionRate = $totalFounders > 0
            ? round(($converted / $totalFounders) * 100, 1) : 0;

        $reviewCompletionRate = $totalFounders > 0
            ? round(($reviewsCompleted / $totalFounders) * 100, 1) : 0;

        return compact('totalFounders', 'activeTrials', 'expiredTrials',
                       'converted', 'reviewsCompleted', 'conversionRate', 'reviewCompletionRate');
    }

    private function getPaginatedFounders(Request $request)
    {
        $query = User::where('is_founder', true)->orderBy('created_at', 'desc');

        $status = $request->query('status');
        if ($status === 'active') {
            $query->where('founder_trial_ends_at', '>', Carbon::now())->whereNull('founder_converted_at');
        } elseif ($status === 'expired') {
            $query->where('founder_trial_ends_at', '<=', Carbon::now())->whereNull('founder_converted_at');
        } elseif ($status === 'converted') {
            $query->whereNotNull('founder_converted_at');
        }

        return $query->paginate(25);
    }
}
