<?php

namespace App\Http\Controllers\Web\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FounderReviewSubmission;
use App\Models\EmailLog;
use App\Mail\FounderReviewCompletionEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Web controller for the optional founder review form.
 *
 * Per the product spec: review is OPTIONAL and NOT a condition of receiving
 * the £24.99 checkout link. The checkout link is sent automatically at day 30
 * via the founders:process-trials scheduled command for ALL founders,
 * regardless of form completion.
 */
class FounderReviewController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user->is_founder) {
            return redirect('/')->with('error', 'Only founders can access this page.');
        }

        if ($user->founder_review_completed) {
            return redirect()->route('founder.review.thankyou');
        }

        return view('frontend.founder.review');
    }

    public function submit(Request $request)
    {
        $user = Auth::user();

        if (!$user->is_founder) {
            return redirect('/')->with('error', 'Only founders can access this page.');
        }

        if ($user->founder_review_completed) {
            return redirect()->route('founder.review.thankyou');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:2000',
        ]);

        // Save optional review for product feedback - does NOT gate the checkout link
        FounderReviewSubmission::create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'] ?? null,
            'submitted_at' => now(),
        ]);

        // Mark review as completed
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

        return redirect()->route('founder.review.thankyou');
    }

    public function thankyou()
    {
        $user = Auth::user();

        if (!$user->is_founder) {
            return redirect('/')->with('error', 'Only founders can access this page.');
        }

        return view('frontend.founder.review-thankyou', compact('user'));
    }
}
