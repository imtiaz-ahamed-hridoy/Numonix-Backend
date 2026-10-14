<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppNotification;
use App\Repositories\Interfaces\GuestDeviceRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Service layer for social authentication.
 *
 * Handles login via Google and Apple (Sign in with Apple) using
 * Laravel Socialite, with resilient support for mobile Firebase ID tokens,
 * Google OAuth id_tokens/access tokens, and Apple RS256 JWK identity tokens.
 */
class SocialLoginService
{
    public function __construct(
        private GuestFingerprintService $fingerprintService,
        private GuestDeviceSyncService $deviceSyncService,
        private UserRepositoryInterface $userRepository,
        private GuestDeviceRepositoryInterface $deviceRepository,
    ) {}

    /**
     * Authenticate a user via a social provider token.
     *
     * Verifies the token against the chosen provider, creates or updates
     * the local user record, optionally merges guest data, and issues
     * an API token.
     *
     * @param  string  $provider  One of: google, apple
     * @param  string  $token  OAuth/identity token from the provider
     * @param  string|null  $deviceId  Guest device ID for data migration
     * @param  string|null  $guestToken  Guest token for fingerprint verification
     * @param  string|null  $guestFingerprint  Guest fingerprint for data migration
     * @param  string|null  $clientEmail  Optional email supplied by client credential
     * @param  string|null  $clientName  Optional display name supplied by client credential
     * @return array{token: string, token_type: string, expires_in: null, is_new_account: bool, user: User}
     */
    public function login(
        string $provider,
        string $token,
        ?string $deviceId = null,
        ?string $guestToken = null,
        ?string $guestFingerprint = null,
        ?string $clientEmail = null,
        ?string $clientName = null
    ): array {
        $socialUser = match ($provider) {
            'google' => $this->handleGoogle($token),
            'apple'  => $this->handleApple($token),
            default  => throw new Exception('Unsupported provider'),
        };

        // If email was not extracted from provider token, fallback to client-supplied email
        if (empty($socialUser['email']) && !empty($clientEmail)) {
            $socialUser['email'] = $clientEmail;
        }

        // If display name was default, prefer client-supplied name if present
        if (!empty($clientName) && ($socialUser['name'] === 'Google User' || $socialUser['name'] === 'Apple User')) {
            $socialUser['name'] = $clientName;
        }

        // For Apple Sign-In on subsequent logins, Apple omits email in the token.
        // Check if an existing account matches the provider + socialite_id
        if (empty($socialUser['email'])) {
            $existingUser = User::where('socialite_provider', $provider)
                ->where('socialite_id', $socialUser['id'])
                ->first();

            if ($existingUser) {
                $socialUser['email'] = $existingUser->email;
            } elseif ($provider === 'apple') {
                // Private relay fallback for novel Apple logins where email was withheld
                $socialUser['email'] = "apple_{$socialUser['id']}@privaterelay.appleid.com";
            }
        }

        if (! $socialUser || empty($socialUser['email'])) {
            throw new Exception('Email not provided or invalid token');
        }

        [$user, $isNewAccount] = $this->createOrUpdateUser($socialUser, $provider);

        if ($isNewAccount && $deviceId && $guestToken && $guestFingerprint) {
            $this->syncGuestData($user, $deviceId, $guestToken, $guestFingerprint);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => null,
            'is_new_account' => $isNewAccount,
            'user' => $user->fresh(),
        ];
    }

    /**
     * Verify a Google OAuth token and return user profile data.
     * Supports Firebase ID tokens, Google OAuth id_tokens, and Google access tokens via Laravel Socialite.
     *
     * @return array{id: string, email: string|null, name: string, avatar: string|null}
     */
    private function handleGoogle(string $token): array
    {
        // 1. Try Firebase Auth verification if configured
        try {
            $firebaseCreds = FirebaseCredentialResolver::resolve();
            if (!empty($firebaseCreds) && file_exists($firebaseCreds) && class_exists(\Kreait\Laravel\Firebase\Facades\Firebase::class)) {
                $verifiedIdToken = \Kreait\Laravel\Firebase\Facades\Firebase::auth()->verifyIdToken($token);
                $claims = $verifiedIdToken->claims();
                $email = $claims->get('email');
                if (!empty($email)) {
                    return [
                        'id' => (string) $claims->get('sub'),
                        'email' => $email,
                        'name' => $claims->get('name') ?? 'Google User',
                        'avatar' => $claims->get('picture') ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Not a Firebase ID token or verification error, fallback to Google tokeninfo and Socialite
        }

        // 2. Check if token is a Google JWT id_token (format: header.payload.signature)
        if (substr_count($token, '.') === 2) {
            try {
                $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $token,
                ]);

                if ($response->successful()) {
                    $payload = $response->json();
                    if (!empty($payload['email'])) {
                        return [
                            'id' => $payload['sub'] ?? Str::uuid()->toString(),
                            'email' => $payload['email'],
                            'name' => $payload['name'] ?? $payload['given_name'] ?? 'Google User',
                            'avatar' => $payload['picture'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Google id_token verification failed, falling back to Socialite: ' . $e->getMessage());
            }
        }

        // 3. Try standard Laravel Socialite
        try {
            $user = Socialite::driver('google')->stateless()->userFromToken($token);
            if ($user && $user->getEmail()) {
                return [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName() ?? 'Google User',
                    'avatar' => $user->getAvatar() ?? null,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Socialite google driver failed, falling back to tokeninfo: ' . $e->getMessage());
        }

        // 4. Try tokeninfo with access_token
        try {
            $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'access_token' => $token,
            ]);
            if ($response->successful()) {
                $payload = $response->json();
                if (!empty($payload['email'])) {
                    return [
                        'id' => $payload['sub'] ?? $payload['user_id'] ?? Str::uuid()->toString(),
                        'email' => $payload['email'],
                        'name' => $payload['name'] ?? 'Google User',
                        'avatar' => null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        throw new Exception('Invalid Google authentication token.');
    }

    /**
     * Verify a Sign in with Apple identity token using Laravel Socialite Apple driver.
     *
     * @return array{id: string, email: string|null, name: string, avatar: null}
     */
    private function handleApple(string $token): array
    {
        try {
            $user = Socialite::driver('apple')->stateless()->userFromToken($token);

            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName() ?? 'Apple User',
                'avatar' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Socialite Apple authentication failed: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Create a new user or update an existing one from social profile data.
     *
     * @param  array{id: string, email: string|null, name: string, avatar: string|null}  $socialUser
     * @return array{0: User, 1: bool} [user, isNewAccount]
     */
    private function createOrUpdateUser(array $socialUser, string $provider): array
    {
        return DB::transaction(function () use ($socialUser, $provider) {
            // First check by email
            $user = !empty($socialUser['email']) ? $this->userRepository->findByEmail($socialUser['email']) : null;

            // If not found by email, check by socialite_provider + socialite_id
            if (!$user && !empty($socialUser['id'])) {
                $user = User::where('socialite_provider', $provider)
                    ->where('socialite_id', $socialUser['id'])
                    ->first();
            }

            if ($user) {
                $this->userRepository->update($user, [
                    'socialite_id' => $socialUser['id'],
                    'socialite_provider' => $provider,
                    'avatar' => $socialUser['avatar'] ?? $user->avatar,
                ]);

                return [$user, false];
            }

            $password = Str::random(16);
            $userData = [
                'name' => $socialUser['name'],
                'email' => $socialUser['email'],
                'password' => $password,
                'avatar' => $socialUser['avatar'] ?? null,
                'socialite_id' => $socialUser['id'],
                'socialite_provider' => $provider,
                'status' => 'active',
                'role' => 'User',
                'is_age_verified' => true,
                'is_agree' => true,
                'email_verified_at' => now(),
            ];

            $user = $this->userRepository->create($userData);

            $user->notify(new AppNotification(
                'Welcome!',
                "Welcome to NumoniX, {$user->name}!",
                [],
                'welcome_notification'
            ));

            return [$user, true];
        });
    }

    /**
     * Migrate guest progress data to the authenticated user's account
     * if the guest fingerprint verifies successfully.
     */
    private function syncGuestData(User $user, string $deviceId, string $guestToken, string $guestFingerprint): void
    {
        if (! $this->fingerprintService->matches($deviceId, $guestToken, $guestFingerprint)) {
            Log::warning('Social login guest sync skipped: fingerprint mismatch', [
                'device_id' => $deviceId,
            ]);

            return;
        }

        $serverFingerprint = $this->fingerprintService->compute($deviceId, $guestToken);

        $device = $this->deviceRepository->findByIdentifierUnmigrated($deviceId, $guestToken, $serverFingerprint);

        if ($device) {
            $this->deviceSyncService->sync($user, $device);
        }
    }
}
