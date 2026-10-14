<?php

namespace App\Http\Controllers\API\Founder;

use App\Http\Controllers\Controller;
use App\Models\FounderReviewSubmission;
use App\Models\EmailLog;
use App\Mail\FounderReviewCompletionEmail;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Founder Programme.
 *
 * The review form is OPTIONAL and NOT a condition of receiving the checkout link.
 * The £24.99 checkout link is sent automatically at day 30 via the scheduled command.
 */
class FounderController extends Controller
{
    use ApiResponse;

    /**
     * Get the founder status for the authenticated user.
     */
    /**
     * Activate founder trial via single-use token (spec 7.4).
     * Starts the rolling 30-day trial from the moment of activation.
     */
    public function activate(Request $request, string $token)
    {
        $user = \App\Models\User::where('activation_token', $token)->first();

        if (!$user) {
            if ($request->wantsJson() || !$request->acceptsHtml()) {
                return $this->error('Invalid or expired activation link. Your account may already be activated.', 404);
            }
            return response()->view('frontend.founder.activation_expired', [], 404);
        }

        $user->activated_at = \Carbon\Carbon::now();
        $user->founder_trial_ends_at = \Carbon\Carbon::now()->addDays(30);
        $user->subscription_tier = 'tier_3';
        $user->is_subscribed = true;
        $user->plan_name = 'Founder Trial (Tier 2 Pass)';
        $user->activation_token = null; // Invalidate single-use token
        $user->save();

        $authToken = $user->createToken('founder_activation')->plainTextToken;

        if ($request->wantsJson() || !$request->acceptsHtml()) {
            return $this->success('Founder 30-day trial activated successfully! Welcome to NumoniX Tier 2.', [
                'user' => $user->fresh(),
                'token' => $authToken,
                'trial_ends_at' => $user->founder_trial_ends_at->toIso8601String(),
            ]);
        }

        return response()->view('frontend.founder.activated', [
            'user' => $user,
            'trialEndsAt' => $user->founder_trial_ends_at,
        ]);
    }

    /**
     * Get the founder status for the authenticated user.
     */
    public function status()
    {
        $user = Auth::user();

        return $this->success('Founder status fetched.', [
            'is_founder' => $user->is_founder,
            'founder_trial_active' => $user->isFounderTrialActive(),
            'founder_trial_ends_at' => $user->founder_trial_ends_at?->toIso8601String(),
            'founder_review_completed' => $user->founder_review_completed,
            'founder_checkout_link' => $user->founder_checkout_link,
            'founder_converted_at' => $user->founder_converted_at?->toIso8601String(),
        ]);
    }

    /**
     * Submit an optional founder review.
     *
     * Completing this review does NOT gate the checkout link.
     * The £24.99 checkout link is sent automatically at day 30
     * via the founders:process-trials scheduled command, regardless
     * of whether this form is completed.
     */
    public function submitReview(Request $request)
    {
        $user = Auth::user();

        if (!$user->is_founder) {
            return $this->error('Only founders can submit this review.', 403);
        }

        if ($user->founder_review_completed) {
            return $this->error('Review already submitted.', 400);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:2000',
        ]);

        // Save the optional review (for product feedback purposes only)
        FounderReviewSubmission::create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'] ?? null,
            'submitted_at' => now(),
        ]);

        // Mark review as completed (for tracking purposes)
        $user->founder_review_completed = true;
        $user->save();

        // Send thank-you email for review completion
        try {
            $mailable = new FounderReviewCompletionEmail($user);
            Mail::to($user->email)->send($mailable);

            EmailLog::create([
                'user_id' => $user->id,
                'type' => 'founder_review_completion',
                'status' => 'sent',
                'sent_at' => now(),
                'subject' => 'Thank You for Your Feedback',
                'body' => $mailable->render(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed sending FounderReviewCompletionEmail to {$user->email}: " . $e->getMessage());
        }

        return $this->success('Thank you for your feedback! Your £24.99 checkout link will be sent at the end of your trial.', [
            'review_completed' => true,
        ]);
    }
}
