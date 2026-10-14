<?php

namespace App\Services\Socialite;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

/**
 * Custom Apple OAuth2 / Identity Token Provider for Laravel Socialite.
 *
 * Implements Apple Sign-In verification for both web OAuth and native mobile
 * JWT identity tokens via Apple RS256 JWKs and Firebase Auth token verification.
 */
class AppleProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     */
    protected $scopes = ['name', 'email'];

    /**
     * Get the authentication URL for the provider.
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://appleid.apple.com/auth/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     */
    protected function getTokenUrl(): string
    {
        return 'https://appleid.apple.com/auth/token';
    }

    /**
     * Get the raw user for the given token.
     *
     * Validates the token against Firebase Auth (if applicable) or directly
     * against Apple's public RS256 JWKs keyset.
     *
     * @param  string  $token
     * @return array{sub: string, email: string|null, name: string}
     */
    public function getUserByToken($token): array
    {
        // 1. Try Firebase Auth verification if token is a Firebase ID token
        try {
            $firebaseCreds = config('firebase.projects.app.credentials') ?? config('services.firebase.credentials_file');
            if (!empty($firebaseCreds) && file_exists($firebaseCreds) && class_exists(\Kreait\Laravel\Firebase\Facades\Firebase::class)) {
                $verifiedIdToken = \Kreait\Laravel\Firebase\Facades\Firebase::auth()->verifyIdToken($token);
                $claims = $verifiedIdToken->claims();
                $email = $claims->get('email');
                $sub = (string) $claims->get('sub');
                if (!empty($sub) || !empty($email)) {
                    return [
                        'sub' => $sub,
                        'email' => $email ?? null,
                        'name' => $claims->get('name') ?? 'Apple User',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Not a Firebase token, proceed to Apple direct verification
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid Apple token format.');
        }

        $header = json_decode(JWT::urlsafeB64Decode($parts[0]));
        $kid = $header->kid ?? null;
        if (! $kid) {
            throw new Exception('Apple token missing "kid" in header.');
        }

        $appleKeys = Cache::remember('apple_public_keys', 3600, function () {
            return Http::timeout(10)->get('https://appleid.apple.com/auth/keys')->json();
        });

        if (! isset($appleKeys['keys'])) {
            throw new Exception('Unable to fetch Apple public keys.');
        }

        $publicKeys = JWK::parseKeySet($appleKeys);
        $publicKey = $publicKeys[$kid] ?? null;

        if (! $publicKey) {
            Cache::forget('apple_public_keys');
            $appleKeys = Http::timeout(10)->get('https://appleid.apple.com/auth/keys')->json();
            $publicKeys = JWK::parseKeySet($appleKeys);
            $publicKey = $publicKeys[$kid] ?? null;
        }

        if (! $publicKey) {
            throw new Exception('No matching Apple public key found.');
        }

        try {
            $decoded = JWT::decode($token, $publicKey, ['RS256']);
        } catch (ExpiredException $ex) {
            throw new Exception('Apple token expired.');
        } catch (SignatureInvalidException $ex) {
            throw new Exception('Invalid Apple token signature.');
        } catch (\Throwable $ex) {
            throw new Exception('Failed to decode Apple token: ' . $ex->getMessage());
        }

        if (($decoded->iss ?? '') !== 'https://appleid.apple.com') {
            throw new Exception('Invalid Apple token issuer.');
        }

        $appleClientId = config('services.apple.client_id');
        $allowedAudiences = array_filter(array_unique([
            $appleClientId,
            config('services.apple.bundle_id', 'com.example.app'),
            config('services.apple.service_id', 'com.example.app.service'),
        ]));
        $aud = $decoded->aud ?? $decoded->audience ?? null;
        $audArray = is_array($aud) ? $aud : [$aud];
        if (!empty($allowedAudiences)) {
            $hasMatch = false;
            foreach ($audArray as $a) {
                if (in_array($a, $allowedAudiences, true)) {
                    $hasMatch = true;
                    break;
                }
            }
            if (!$hasMatch) {
                Log::warning("Apple audience mismatch: expected one of " . json_encode($allowedAudiences) . ", got " . json_encode($audArray));
                throw new Exception('Invalid Apple token audience.');
            }
        }

        return [
            'sub' => (string) $decoded->sub,
            'email' => $decoded->email ?? null,
            'name' => 'Apple User',
        ];
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => null,
            'name' => $user['name'] ?? 'Apple User',
            'email' => $user['email'] ?? null,
            'avatar' => null,
        ]);
    }
}
