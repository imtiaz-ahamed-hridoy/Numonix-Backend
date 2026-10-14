<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\FounderRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FounderRepository implements FounderRepositoryInterface
{
    /**
     * Get statistics about the founder programme for the admin dashboard.
     *
     * @return array
     */
    public function getFounderStats(): array
    {
        $totalFounders = User::where('is_founder', true)->count();
        $activeTrials = User::where('is_founder', true)
            ->whereNotNull('founder_trial_ends_at')
            ->where('founder_trial_ends_at', '>', Carbon::now())
            ->count();
        $expiredTrials = User::where('is_founder', true)
            ->whereNotNull('founder_trial_ends_at')
            ->where('founder_trial_ends_at', '<=', Carbon::now())
            ->whereNull('founder_converted_at')
            ->count();
        $convertedFounders = User::where('is_founder', true)
            ->whereNotNull('founder_converted_at')
            ->count();

        $conversionRate = $totalFounders > 0 
            ? round(($convertedFounders / $totalFounders) * 100, 1) . '%' 
            : '0%';

        $completedReviews = User::where('is_founder', true)
            ->where('founder_review_completed', true)
            ->count();

        $reviewRate = $totalFounders > 0 
            ? round(($completedReviews / $totalFounders) * 100, 1) . '%' 
            : '0%';

        $day25Sent = User::where('is_founder', true)->whereNotNull('founder_day25_sent_at')->count();
        $day30Sent = User::where('is_founder', true)->whereNotNull('founder_day30_sent_at')->count();

        return [
            'total_founders' => $totalFounders,
            'active_trials' => $activeTrials,
            'expired_trials' => $expiredTrials,
            'converted_founders' => $convertedFounders,
            'conversion_rate' => $conversionRate,
            'review_completion_rate' => $reviewRate,
            'day25_emails_sent' => $day25Sent,
            'day30_emails_sent' => $day30Sent,
        ];
    }
}
