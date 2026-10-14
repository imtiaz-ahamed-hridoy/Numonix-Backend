<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\EmailLog;
use App\Mail\FounderDay25ReminderEmail;
use App\Mail\FounderCheckoutEmail;
use App\Services\PaddleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Processes founder trials daily.
 *
 * Per spec (task 3.13, 3.14, 3.15):
 * - Day 25: Send trial-ending reminder with optional review form link AND notification of the £24.99 offer.
 * - Day 30: Generate unique per-founder £24.99 checkout links via Paddle API and send to ALL founders.
 * - Review completion is NOT a condition for any of this. All founders receive these emails.
 * - Expired unconverted founders have tier_2 revoked after the 1-day grace window.
 */
class ProcessFounderTrialsCommand extends Command
{
    protected $signature = 'founders:process-trials';
    protected $description = 'Process founder trials: send Day 25 reminder and Day 30 checkout links, and downgrade expired unconverted founders';

    protected PaddleService $paddleService;

    public function __construct(PaddleService $paddleService)
    {
        parent::__construct();
        $this->paddleService = $paddleService;
    }

    public function handle()
    {
        $this->info('Starting founder trial processing...');

        $this->processDay25();
        $this->processDay30Checkout();
        $this->processExpiredTrials();

        $this->info('Finished processing founder trials.');
        return 0;
    }

    /**
     * Day 25: Send trial-ending email with optional review link + notification of the £24.99 conversion offer.
     * Sent to every founder regardless of form completion (spec 3.13).
     * Uses resilient range check so that skipped/delayed cron runs do not miss founders.
     */
    protected function processDay25()
    {
        $users = User::where('is_founder', true)
            ->whereNull('founder_converted_at')
            ->whereNull('founder_day25_sent_at')
            ->where('founder_trial_ends_at', '<=', Carbon::now()->addDays(5))
            ->where('founder_trial_ends_at', '>', Carbon::now())
            ->get();

        foreach ($users as $user) {
            try {
                $mailable = new FounderDay25ReminderEmail($user);
                Mail::to($user->email)->send($mailable);

                $user->founder_day25_sent_at = now();
                $user->save();

                EmailLog::create([
                    'user_id' => $user->id,
                    'type' => 'founder_day25',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'subject' => 'Your NumoniX Founder\'s Trial is ending soon',
                    'body' => $mailable->render(),
                ]);

                $this->info("Day 25 email sent to {$user->email}");
            } catch (\Exception $e) {
                Log::error("Failed sending Day 25 email to {$user->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Day 30: Generate unique per-founder £24.99 checkout links via Paddle API
     * and send automatically to ALL founders (spec 3.14).
     * Sent regardless of review form completion.
     */
    protected function processDay30Checkout()
    {
        $users = User::where('is_founder', true)
            ->whereNull('founder_converted_at') // Only non-converted founders need the checkout link
            ->whereNull('founder_day30_sent_at')
            ->where('founder_trial_ends_at', '<=', Carbon::now())
            ->get();

        foreach ($users as $user) {
            try {
                // If user already has a checkout link, reuse it; otherwise generate via Paddle API (spec 3.14)
                $checkoutLink = $user->founder_checkout_link ?: $this->paddleService->generateFounderCheckoutLink($user);

                if (empty($checkoutLink)) {
                    Log::warning("Skipping Day 30 checkout email for {$user->email}: Paddle checkout link generation returned empty.");
                    continue;
                }

                // Store checkout link
                $user->founder_checkout_link = $checkoutLink;
                $user->save();

                // Send checkout link email (the actual conversion email)
                $mailable = new FounderCheckoutEmail($user, $checkoutLink);
                Mail::to($user->email)->send($mailable);

                // Mark sent only after successful delivery/queueing
                $user->founder_day30_sent_at = now();
                $user->save();

                EmailLog::create([
                    'user_id' => $user->id,
                    'type' => 'founder_day30_checkout',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'subject' => 'Your NumoniX Founder\'s Checkout Link is Ready!',
                    'body' => $mailable->render(),
                ]);

                $this->info("Day 30 checkout email sent to {$user->email}");
            } catch (\Exception $e) {
                Log::error("Failed sending Day 30 checkout email to {$user->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Downgrade expired unconverted founders to tier_1 (spec 3.15).
     * Applied one day after trial end to give checkout email delivery and purchase window.
     */
    protected function processExpiredTrials()
    {
        $users = User::where('is_founder', true)
            ->whereNull('founder_converted_at')
            ->where('founder_trial_ends_at', '<', Carbon::now()->subDay())
            ->whereIn('subscription_tier', ['tier_2', 'tier_3'])
            ->get();

        foreach ($users as $user) {
            $user->subscription_tier = 'tier_1';
            $user->is_subscribed = false;
            $user->plan_name = null;
            $user->save();
            $this->info("Revoked access for expired founder {$user->email}");
        }
    }
}
