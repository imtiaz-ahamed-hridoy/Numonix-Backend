<?php

namespace App\Services\Auth;

use App\Jobs\UserDataExportJob;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\UploadStorageHelper;

/**
 * Service layer for user profile management.
 *
 * Handles profile retrieval, account management, profile updates
 * (including avatar upload), password changes, account deletion,
 * and data export requests for the mobile app UI.
 */
class ProfileService
{
    use ApiResponse;

    /**
     * Build the main profile payload for the authenticated user.
     *
     * Returns user details, membership info, and available
     * action sections (account, support, other).
     *
     * @param  User  $user  The authenticated user
     * @return array{show_unlock_banner: bool, unlock_banner: array|null, user: array, membership: array, sections: array}
     */
    public function profilePayload(User $user): array
    {
        $user = $user->fresh();

        $isSubscribed = (bool) ($user->is_subscribed ?? false);

        $unlockBanner = $isSubscribed
            ? null
            : [
                'title' => 'Unlock Full Access',
                'description' => 'Unlock the full learning path, composition tools, and ear training.',
            ];

        return [
            'show_unlock_banner' => ! $isSubscribed,
            'unlock_banner' => $unlockBanner,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob' => $user->dob,
                'avatar_url' => $user->avatar_url,
                'tier' => $user->getTierDisplayName(),
            ],
            'membership' => [
                'current_plan' => $user->getTierDisplayName(),
                'next_billing_date' => $user->next_billing_date ?? null,
                'is_subscribed' => (bool) ($user->is_subscribed ?? false),
            ],
            'sections' => [
                [
                    'key' => 'account',
                    'label' => 'ACCOUNT',
                    'items' => [
                        ['key' => 'edit_profile',   'label' => 'Edit Profile',   'description' => 'Name, email, photo',          'api_endpoint' => '/profile/details',              'method' => 'POST'],
                        ['key' => 'manage_account', 'label' => 'Manage Account', 'description' => 'Membership, data, privacy',   'api_endpoint' => '/profile/manage-account',       'method' => 'GET'],
                        ['key' => 'notifications',  'label' => 'Notifications',  'description' => 'Email and push preferences',  'api_endpoint' => '/notification-settings-show',   'method' => 'GET'],
                    ],
                ],
                [
                    'key' => 'support',
                    'label' => 'SUPPORT',
                    'items' => [
                        ['key' => 'help_support', 'label' => 'Help & Support', 'description' => 'Contact, bug reports, feedback', 'api_endpoint' => '/support/contact', 'method' => 'POST'],
                    ],
                ],
                [
                    'key' => 'other',
                    'label' => 'OTHER',
                    'items' => [
                        ['key' => 'rate_app', 'label' => 'Rate the App', 'description' => null, 'api_endpoint' => null,             'method' => null],
                        ['key' => 'logout',   'label' => 'Log Out',      'description' => null, 'api_endpoint' => '/logout',    'method' => 'POST'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Build the manage-account payload for the authenticated user.
     *
     * Includes personal information, membership details, and
     * data privacy actions.
     *
     * @param  User  $user  The authenticated user
     * @return array{personal_information: array, membership: array, data_privacy: array}
     */
    public function manageAccountPayload(User $user): array
    {
        $user = $user->fresh();

        return [
            'personal_information' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob' => $user->dob,
                'avatar_url' => $user->avatar_url,
                'tier' => $user->getTierDisplayName(),
                'actions' => [
                    ['key' => 'edit_profile',    'label' => 'Edit Profile',    'api_endpoint' => '/profile/details',        'method' => 'POST'],
                    ['key' => 'change_password', 'label' => 'Change Password', 'api_endpoint' => '/profile/change-password', 'method' => 'POST'],
                ],
            ],
            'membership' => [
                'current_plan' => $user->getTierDisplayName(),
                'next_billing_date' => $user->next_billing_date ?? null,
                'is_subscribed' => (bool) ($user->is_subscribed ?? false),
            ],
            'data_privacy' => [
                ['key' => 'download_my_data', 'label' => 'Download My Data',  'api_endpoint' => '/profile/download-my-data', 'method' => 'POST'],
                ['key' => 'delete_account',   'label' => 'Delete Account',    'api_endpoint' => '/profile/delete-account',   'method' => 'POST'],
                ['key' => 'privacy_policy',   'label' => 'Privacy Policy',    'api_endpoint' => '/cms/privacy-policy',       'method' => 'GET'],
            ],
        ];
    }

    /**
     * Update the authenticated user's profile.
     *
     * Updates non-file fields from the data array and handles
     * avatar upload, replacement, and storage.
     *
     * @param  User  $user  The authenticated user
     * @param  array  $data  Updated profile fields (excluding avatar)
     * @param  UploadedFile|null  $avatar  Optional new avatar file
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null)
    {
        DB::beginTransaction();

        try {
            // Update non-file fields
            $user->update(collect($data)->except('avatar')->toArray());

            // If avatar uploaded, replace it
            if ($avatar) {
                // 1) delete old avatar file
                $this->deleteOldAvatar($user->avatar);

                // 2) store new avatar in both storage/app/public and public/ (dual storage)
                $relativePath = UploadStorageHelper::store($avatar, 'uploads/avatar');

                // 3) Save RELATIVE path in DB (e.g. uploads/avatar/xxx.png)
                //    getAvatarUrlAttribute() on User turns this into a full URL.
                $user->avatar = $relativePath;
                $user->save();
            }

            DB::commit();

            // Return slim payload matching the Edit Profile UI
            return $this->ok('Profile updated successfully', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob' => optional($user->dob)->format('Y-m-d'),
                'avatar_url' => $user->avatar_url,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Change the authenticated user's password.
     *
     * Validates the old password, ensures the new password is
     * different, and persists the update.
     *
     * @param  User  $user  The authenticated user
     * @param  string  $oldPassword  Current password for verification
     * @param  string  $newPassword  New password to set
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword)
    {
        if (! Hash::check($oldPassword, $user->password)) {
            return $this->error('Incorrect current password', 422);
        }

        if (Hash::check($newPassword, $user->password)) {
            return $this->error('New password cannot be the same as current password', 422);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return $this->ok('Password changed successfully');
    }

    /**
     * Delete the authenticated user's account.
     *
     * Removes avatar file, revokes API tokens, and deletes
     * the user record within a database transaction.
     *
     * @param  User  $user  The authenticated user
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(User $user)
    {
        DB::beginTransaction();

        try {
            // delete avatar file if exists
            $this->deleteOldAvatar($user->avatar);

            // revoke tokens
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            $user->delete();

            DB::commit();

            return $this->ok('Account deleted successfully');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Request a data export for the authenticated user.
     *
     * Dispatches a background job that prepares the user's data
     * and sends it via email.
     *
     * @param  User  $user  The authenticated user
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestDataDownload(User $user)
    {
        UserDataExportJob::dispatch($user);

        return $this->ok('Your data is being prepared. Check your email shortly.');
    }

    /**
     * Delete an old avatar file.
     *
     * Handles:
     *  - relative paths  (uploads/avatar/xxx.png)  -> deletes from public/
     *  - legacy full URL (https://.../.../xxx.png) -> extracts path from URL
     *
     * @param  string|null  $avatar  The avatar path or URL to delete
     */
    private function deleteOldAvatar(?string $avatar): void
    {
        if (empty($avatar)) {
            return;
        }

        // Social-login avatar (external CDN) - do not delete
        if (str_starts_with($avatar, 'http')) {
            $parsed = parse_url($avatar, PHP_URL_PATH) ?? '';
            $parsed = ltrim($parsed, '/');

            // Only attempt deletion if it looks like one of our own uploads
            if (!str_starts_with($parsed, 'uploads/') && !str_starts_with($parsed, 'storage/')) {
                return;
            }

            // Strip /storage/ prefix if it was an old Storage::disk URL
            if (str_starts_with($parsed, 'storage/')) {
                $parsed = substr($parsed, strlen('storage/'));
            }

            $avatar = $parsed; // fall through to public_path deletion below
        }

        UploadStorageHelper::delete($avatar);
    }
}
