<?php

namespace App\Services\Auth;

use App\Events\EmailVerified;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Exception;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Service layer for email verification.
 *
 * Handles OTP generation and sending for email verification,
 * OTP validation, and marking user emails as verified.
 * Email delivery failures are non-fatal to keep registration robust.
 */
class EmailVerificationService
{
    use ApiResponse;

    protected Otp $otp;

    protected UserRepositoryInterface $userRepository;

    /**
     * EmailVerificationService constructor.
     */
    public function __construct(
        Otp $otp,
        UserRepositoryInterface $userRepository
    ) {
        $this->otp = $otp;
        $this->userRepository = $userRepository;
    }

    /**
     * Generate OTP and send verification email
     *
     * @throws Exception For other unexpected errors
     */
    public function sendVerificationOtp(string $email): array
    {
        // Find a user by email
        $user = $this->userRepository->findByEmail($email);

        // Check user if empty throw exception
        if (! $user) {
            return [
                'status' => false,
                'message' => 'User not found',
                'code' => 404,
            ];
        }

        // Check if user already verified throw validation exception
        if ($user->hasVerifiedEmail()) {
            return [
                'status' => false,
                'message' => 'User already verified',
                'code' => 403,
            ];
        }

        $otp = $this->sendOtp($user);

        return [
            'status' => true,
            'message' => 'OTP sent to your email',
            'code' => 200,
            'otp' => $otp,
        ];
    }

    /**
     * Generate OTP and attempt to send it via email.
     *
     * Email delivery failure is NON-FATAL - the OTP is still stored in the
     * database (by the ichtrojan/laravel-otp package), so the email-verify
     * endpoint can validate it regardless.  This keeps registration working
     * even when the configured SMTP server is temporarily unreachable.
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

        // Attempt to send the email - failure is non-fatal
        try {
            Mail::to($user->email)->send(new \App\Mail\OTP($otp->token, $user, 'Verify Your Email Address', 'verify'));
        } catch (\Throwable $e) {
            Log::warning('Failed to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return $otp->token;
    }

    /**
     * Verify user email with OTP
     */
    public function verifyEmail(string $email, string $otp): array
    {

        // find user by email
        $user = $this->userRepository->findByEmail($email);
        if (! $user) {
            return [
                'status' => false,
                'message' => 'User not found',
                'code' => 404,
            ];
        }
        // Return true if the user is already verified
        if ($user->hasVerifiedEmail()) {
            return [
                'status' => false,
                'message' => 'Email already verified',
                'code' => 403,
            ];
        }

        // Find valid OTP and validate it
        $verify = (new Otp)->validate($user->email, $otp);

        // If OTP is invalid, throw validation exception
        if (! $verify->status) {
            return [
                'status' => false,
                'message' => $verify->message,
                'code' => 400,
            ];
        }

        // Mark email as verified
        $verified = $this->userRepository->markEmailAsVerified($user);

        // Dispatch event if verified successfully
        if ($verified) {
            event(new EmailVerified($user));
        }

        // Generate token after successful verification
        $token = $user->createToken('auth_token')->plainTextToken;

        // Refresh user data
        $user->refresh();

        return [
            'status' => true,
            'message' => 'Email verified successfully',
            'code' => 200,
            'token' => $token,
            'is_new_account' => true,  // Registration flow: Flutter should migrate guest data
            'user' => $user,
        ];
    }
}
