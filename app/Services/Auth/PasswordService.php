<?php

namespace App\Services\Auth;

use App\Http\Requests\API\Auth\ChangePasswordRequest;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

/**
 * Service layer for password management.
 *
 * Handles forget-password OTP generation and sending,
 * OTP verification, password reset token creation, and
 * password change operations.
 */
class PasswordService
{
    use ApiResponse;

    protected Otp $otp;

    protected UserRepositoryInterface $userRepository;

    /**
     * PasswordService constructor.
     */
    public function __construct(
        Otp $otp,
        UserRepositoryInterface $userRepository
    ) {
        $this->otp = $otp;
        $this->userRepository = $userRepository;
    }

    /**
     * Generate OTP and send forget password notification
     *
     * @throws Exception
     */
    public function sendForgetPasswordOTP(User $user): string
    {
        return $this->sendOtp($user);
    }

    /**
     * Generate OTP and send notification
     *
     * @param  string  $notificationType  The notification class to use
     * @param  int  $digits  Number of OTP digits
     * @param  int  $expiry  Expiry time in seconds
     *
     * @throws Exception
     */
    protected function sendOtp(User $user, int $digits = 4, int $expiry = 10): string
    {
        // Generate OTP
        $otp = $this->otp->generate($user->email, 'numeric', $digits, $expiry);

        // Send mail directly
        Mail::to($user->email)->send(new \App\Mail\OTP($otp->token, $user, 'Reset Your Password', 'forget-password'));

        return $otp->token;
    }

    /**
     * Process forgot password request
     *
     * @throws Exception
     */
    public function processForgotPassword(string $email): string
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            throw new Exception('User not found', 404);
        }

        // Send forget password OTP
        return $this->sendForgetPasswordOTP($user);
    }

    /**
     * Verify password reset OTP and generate a reset token.
     *
     * @param  string  $email  Email address of the user
     * @param  string  $otp  One-time password to verify
     * @return array Response with status, message, code and token
     */
    public function verifyPasswordResetOtp(string $email, string $otp): array
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            return $this->createErrorResponse('User not found', 404);
        }

        // Validate OTP
        $otpVerification = (new Otp)->validate($user->email, $otp);

        // Check if OTP is valid
        if (! $otpVerification->status) {
            return $this->createErrorResponse($otpVerification->message, 400);
        }

        // First, delete any existing password reset tokens for this email
        $this->userRepository->deleteResetPasswordTokenByEmail($email);

        // Create reset token
        $resetToken = $this->userRepository->createResetPasswordToken($user->email);

        return [
            'status' => true,
            'message' => 'OTP verified successfully',
            'code' => 200,
            'token' => $resetToken,
        ];
    }

    /**
     * Create a standardized error response.
     *
     * @param  string  $message  Error message
     * @param  int  $code  HTTP status code
     * @return array Formatted error response
     */
    private function createErrorResponse(string $message, int $code): array
    {
        return [
            'status' => false,
            'message' => $message,
            'code' => $code,
            'token' => '',
        ];
    }

    /**
     * Reset a user's password using a valid token
     *
     * @param  string  $email  User's email address
     * @param  string  $password  New password to set
     * @param  string  $token  Password reset token
     * @return array Response containing success/error status, message and HTTP status code
     */
    public function resetPassword(string $email, string $password, string $token): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (! $user) {
            return $this->createErrorResponse('User not found', 404);
        }

        $tokenRecord = $this->userRepository->findResetPasswordToken($email, $token);
        if (! $tokenRecord) {
            return $this->createErrorResponse('Token not found', 404);
        }

        // Check if token has expired (15 minutes)
        $tokenCreatedAt = Carbon::parse($tokenRecord->created_at);
        $now = Carbon::now();
        $tokenAgeInMinutes = $now->diffInMinutes($tokenCreatedAt);

        if ($tokenAgeInMinutes > 15) {
            return $this->createErrorResponse('Password reset token has expired', 400);
        }

        // Update user password
        $result = $this->userRepository->update($user, ['password' => $password]);
        if (! $result) {
            return $this->createErrorResponse('Failed to update password', 500);
        }

        // Delete the used token
        $this->userRepository->deleteResetPasswordTokenByEmail($email);

        // Return success response
        return [
            'success' => true,
            'message' => 'Password has been reset successfully',
            'status' => 200,
        ];
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $this->userRepository->update($user, ['password' => $request->password]);
        $user->tokens()->delete();

        return $this->ok('Password Changed successfully.');
    }
}
