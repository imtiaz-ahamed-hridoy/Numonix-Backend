<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\EmailVerifyRequest;
use App\Http\Requests\API\Auth\ForgotPasswordRequest;
use App\Http\Requests\API\Auth\RegistrationRequest;
use App\Http\Requests\API\Auth\ResendOTPRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Requests\API\Auth\VerifyOTPRequest;
use App\Services\Auth\AuthFacade;
use App\Traits\ApiResponse;
use App\Traits\HasApiException;
use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * API controller for user registration and authentication.
 */
class RegisterController extends Controller
{
    use ApiResponse, HasApiException;

    protected AuthFacade $authFacade;

    /**
     * RegisterController constructor.
     */
    public function __construct(
        AuthFacade $authFacade
    ) {
        $this->authFacade = $authFacade;
    }

    /**
     * Handle user registration
     */
    public function register(RegistrationRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Read guest credentials from headers if not in request body
            if (empty($data['device_id']) && empty($data['token'])) {
                $deviceId = $request->header('X-Guest-Device-Id');
                $token = $request->header('X-Guest-Token');
                $fingerprint = $request->header('X-Guest-Fingerprint');

                if ($deviceId && $token && $fingerprint) {
                    $data['device_id'] = $deviceId;
                    $data['token'] = $token;
                    $data['guest_fingerprint'] = $fingerprint;
                }
            }

            $result = $this->authFacade->registerUser($data);

            return $this->success('Registration completed successfully. Please verify your email.', [
                'user' => $result['user'],
                'guest_synced' => $result['guest_synced'] ?? false,
            ], 201);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Resend OTP to user
     */
    public function resendEmailVerifyOTP(ResendOTPRequest $request): JsonResponse
    {
        try {
            // Find user by email and send OTP to email
            $result = $this->authFacade->sendVerificationOtp($request->email);
            if (! $result['status']) {
                return $this->error($result['message'], $result['code']);
            }

            return $this->success('A new verification code has been sent to your email.', []);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Verify user email with OTP
     */
    public function verifyEmail(EmailVerifyRequest $request): JsonResponse
    {
        try {
            // Verify OTP and update email verification status
            $verification = $this->authFacade->verifyEmail($request->email, $request->otp);

            if ($verification['status']) {
                return $this->success($verification['message'], [
                    'token_type' => 'Bearer',
                    'token' => $verification['token'],
                    'is_new_account' => $verification['is_new_account'] ?? true,
                    'user' => $verification['user'],
                ]);
            }

            return $this->error($verification['message'], $verification['code']);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Handle forgot password request by sending an OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // send forget password otp
            $otp = $this->authFacade->processForgotPassword($request->email);

            return $this->success('Password reset OTP has been sent to your email.', []);
        } catch (\Throwable $e) {
            // handle exception
            return $this->handleException($e);
        }
    }

    /**
     * Verify an OTP for password reset.
     *
     * @param  VerifyOTPRequest  $request  The validated request containing email and OTP
     * @return JsonResponse The response with success/error message and token
     */
    public function verifyForgotPasswordOTP(VerifyOTPRequest $request): JsonResponse
    {
        try {
            $result = $this->authFacade->verifyPasswordResetOtp(
                $request->email,
                $request->otp
            );

            if ($result['status']) {
                return $this->ok(
                    $result['message'],
                    [
                        'token' => $result['token'],
                        'expired_in' => '15 minutes',
                    ]
                );
            }

            return $this->error($result['message'], $result['code']);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Reset a user's password using a valid token
     *
     * @param  ResetPasswordRequest  $request  Contains validated email, token, and new password
     * @return JsonResponse Returns success or error response
     *
     * @throws Exception When an unexpected error occurs during password reset
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $result = $this->authFacade->resetPassword(
                $request->email,
                $request->password,
                $request->token
            );

            if ($result['status']) {
                return $this->ok('Your password has been successfully reset.');
            }

            return $this->error(
                $result['message'] ?? 'Failed to reset password',
                $result['code'] ?? 400
            );
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
