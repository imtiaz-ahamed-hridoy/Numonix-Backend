<?php

namespace App\Services\Auth;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service layer for user authentication.
 *
 * Handles user login with credential verification,
 * account status and email verification checks,
 * and logout (single device and all devices).
 */
class AuthenticationService
{
    use ApiResponse;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle user login
     */
    public function login(string $email, string $password): JsonResponse
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            return $this->error('The provided credentials do not match our records.', 401, [
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user->status !== 'active') {
            Auth::logout();

            return $this->error('Your account has been deactivated. Please contact the administrator for assistance.', 401);
        }

        if (! $user->email_verified_at) {
            Auth::logout();

            return $this->error('Your account has not been verified. Please verify your email address.', 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Login Successful',
            'token_type' => 'Bearer',
            'token' => $user->createToken('AuthToken')->plainTextToken,
            'is_new_account' => false,  // Existing account: Flutter must discard guest data
            'data' => $user,
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->ok('Logged out successfully.');
        } catch (\Throwable $exception) {
            return $this->error('Something went wrong.', 500);
        }
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return $this->ok('All devices have been successfully logged out.');
        } catch (\Throwable $exception) {
            return $this->error('Something went wrong.', 500);
        }
    }
}
