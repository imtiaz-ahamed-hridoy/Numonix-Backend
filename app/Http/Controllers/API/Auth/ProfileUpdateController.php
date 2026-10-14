<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ChangePasswordRequest;
use App\Http\Requests\API\Auth\UpdateDetailsRequest;
use App\Services\Auth\ProfileService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

/**
 * API controller for user profile management.
 */
class ProfileUpdateController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProfileService $service) {}

    /* ------------------------------------------------------------------
     * GET /profile - Main profile page (Frame.png)
     * ----------------------------------------------------------------*/
    /**
     * Get the authenticated user's profile data.
     */
public function profile()
    {
        return $this->ok(
            'Profile loaded successfully.',
            $this->service->profilePayload(Auth::user())
        );
    }

    /* ------------------------------------------------------------------
     * GET /profile/manage-account - Manage Account page
     * ----------------------------------------------------------------*/
    /**
     * Get the manage account page data with personal info and membership details.
     */
public function manageAccount()
    {
        return $this->ok(
            'Manage account loaded',
            $this->service->manageAccountPayload(Auth::user())
        );
    }

    /* ------------------------------------------------------------------
     * POST /profile/details - Edit Profile (390w light-1.png)
     * ----------------------------------------------------------------*/
    /**
     * Update the authenticated user's profile details.
     */
public function updateDetails(UpdateDetailsRequest $request)
    {
        $user = Auth::user();

        return $this->service->updateProfile(
            $user,
            $request->validated(),
            $request->file('avatar')
        );
    }

    /* ------------------------------------------------------------------
     * POST /profile/change-password - Change Password (390w light-3.png)
     * ----------------------------------------------------------------*/
    /**
     * Change the authenticated user's password.
     */
public function changePassword(ChangePasswordRequest $request)
    {
        $validated = $request->validated();
        $currentPassword = $validated['current_password'] ?? $validated['old_password'] ?? $request->input('old_password') ?? $request->input('current_password');

        return $this->service->changePassword(
            Auth::user(),
            $currentPassword,
            $validated['password']
        );
    }

    /* ------------------------------------------------------------------
     * POST /profile/delete-account - Delete Account (390w light-7.png)
     * ----------------------------------------------------------------*/
    /**
     * Delete the authenticated user's account.
     */
public function accountDelete()
    {
        return $this->service->deleteAccount(Auth::user());
    }

    /* ------------------------------------------------------------------
     * POST /profile/download-my-data - Download My Data (390w light-8/9)
     * ----------------------------------------------------------------*/
    /**
     * Request a data export for the authenticated user.
     */
public function downloadMyData()
    {
        return $this->service->requestDataDownload(Auth::user());
    }
}
