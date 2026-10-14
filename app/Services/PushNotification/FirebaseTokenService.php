<?php

namespace App\Services\PushNotification;

use App\Models\FirebaseToken;
use App\Models\User;
use App\Repositories\Interfaces\FirebaseTokenRepositoryInterface;

/**
 * Service layer for Firebase Cloud Messaging token management.
 *
 * Handles upserting and deleting Firebase tokens associated
 * with user devices for push notification delivery.
 */
class FirebaseTokenService
{
    protected FirebaseTokenRepositoryInterface $firebaseTokenRepository;

    public function __construct(FirebaseTokenRepositoryInterface $firebaseTokenRepository)
    {
        $this->firebaseTokenRepository = $firebaseTokenRepository;
    }

    /**
     * Update or create a Firebase token for a user.
     */
    public function upsertToken(User $user, string $token, string $deviceId): FirebaseToken
    {
        $firebaseToken = $this->firebaseTokenRepository->findByDeviceIdOrToken($deviceId, $token);

        if ($firebaseToken) {
            $this->firebaseTokenRepository->update($firebaseToken, [
                'user_id' => $user->id,
                'token' => $token,
                'device_id' => $deviceId,
            ]);
        } else {
            $firebaseToken = $this->firebaseTokenRepository->create([
                'user_id' => $user->id,
                'token' => $token,
                'device_id' => $deviceId,
            ]);
        }

        return $firebaseToken;
    }

    /**
     * Delete a Firebase token by device_id.
     */
    public function deleteToken(string $deviceId): bool
    {
        $firebaseToken = $this->firebaseTokenRepository->findByDeviceId($deviceId);

        if ($firebaseToken) {
            $firebaseToken->delete();

            return true;
        }

        return false;
    }
}
