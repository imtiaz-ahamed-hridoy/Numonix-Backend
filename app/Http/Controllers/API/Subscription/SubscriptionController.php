<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Services\PaddleService;
use App\Services\SubscriptionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API controller for subscription status and Paddle checkout.
 */
class SubscriptionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SubscriptionService $subscriptionService,
        private PaddleService $paddleService
    ) {}

    /**
     * Get the subscription status for the authenticated user.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();

        $data = $this->subscriptionService->getStatus($user);

        return $this->success('Subscription status fetched.', $data);
    }

    /**
     * Generate or retrieve a Paddle checkout URL for the authenticated user.
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return $this->error('Unauthenticated.', 401);
        }

        $isFounder = (bool) $user->is_founder && ($user->founder_trial_ends_at === null || $user->founder_trial_ends_at->isFuture());

        $checkoutUrl = '';
        if ($isFounder && !empty($user->founder_checkout_link)) {
            $checkoutUrl = $user->founder_checkout_link;
        } else {
            $checkoutUrl = $this->paddleService->generateCheckoutLink($user, null, [
                'is_founder_offer' => $isFounder,
            ]);

            if ($isFounder && !empty($checkoutUrl)) {
                $user->founder_checkout_link = $checkoutUrl;
                $user->save();
            }
        }

        if (empty($checkoutUrl)) {
            // Fallback to numonix membership URL if Paddle sandbox or API is offline
            $checkoutUrl = 'https://example.com/membership';
        }

        return $this->success('Checkout link generated successfully.', [
            'checkout_url' => $checkoutUrl,
            'is_founder_offer' => $isFounder,
        ]);
    }
}