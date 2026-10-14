<?php

namespace App\Http\Controllers\API\PushNotification;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\PushNotification\DeleteFirebaseTokenRequest;
use App\Http\Requests\API\PushNotification\UpdateFirebaseTokenRequest;
use App\Models\User;
use App\Services\PushNotification\FirebaseTokenService;
use App\Traits\ApiResponse;

/**
 * API controller for Firebase Cloud Messaging token management.
 */
class FirebaseTokenController extends Controller
{
    use ApiResponse;

    private FirebaseTokenService $firebaseTokenService;

    public function __construct(FirebaseTokenService $firebaseTokenService)
    {
        $this->firebaseTokenService = $firebaseTokenService;
    }

    /**
     * Update or create a Firebase Cloud Messaging token for the user.
     */
public function updateFirebaseToken(UpdateFirebaseTokenRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $firebaseToken = $this->firebaseTokenService->upsertToken(
            $user,
            $request->validated()['token'],
            $request->validated()['device_id']
        );

        $message = $firebaseToken->wasRecentlyCreated
            ? 'Token created successfully'
            : 'Token updated successfully';

        return $this->success($message, $firebaseToken, 200);
    }

    /**
     * Delete a Firebase Cloud Messaging token by device ID.
     */
public function deleteFirebaseToken(DeleteFirebaseTokenRequest $request)
    {
        $deleted = $this->firebaseTokenService->deleteToken($request->validated()['device_id']);

        if ($deleted) {
            return $this->success('Token deleted successfully', [], 200);
        }

        return $this->error('Token not found', 404);
    }
}
