<?php

namespace App\Http\Controllers\API\SocialLogin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SocialLogin\SocialLoginRequest;
use App\Services\SocialLoginService;
use App\Traits\ApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API controller for social authentication (Google, Facebook, Apple).
 */
class SocialLoginController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SocialLoginService $socialLoginService
    ) {}

    /**
     * Handle the Apple Sign-In callback for identity token exchange.
     */
    public function redirectCallbackApple(): JsonResponse
    {
        return $this->ok('You are now logged in');
    }

    /**
     * Authenticate a user via a social provider token (Google, Facebook, Apple).
     */
    public function socialLogin(SocialLoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $provider = $data['provider'];

        try {
            $result = $this->socialLoginService->login(
                $provider,
                $data['token'],
                $data['device_id'] ?? null,
                $data['guest_token'] ?? null,
                $data['guest_fingerprint'] ?? null,
                $data['email'] ?? null,
                $data['name'] ?? null
            );

            return $this->success(
                "{$provider} login successful.",
                $result,
                200,
                ['token' => $result['token']]
            );
        } catch (\Throwable $e) {
            Log::error('Social Login Error: ', ['provider' => $provider, 'error' => $e->getMessage()]);

            return $this->error('Failed to authenticate with '.$provider.': '.$e->getMessage(), 401, ['error' => $e->getMessage()]);
        }
    }
}