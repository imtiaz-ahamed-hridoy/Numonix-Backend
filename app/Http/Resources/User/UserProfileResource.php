<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for the authenticated user's profile payload.
 *
 * Wraps the user, membership, and action-sections data returned by
 * the profile and manage-account endpoints.  The exact structure
 * mirrors what ProfileService currently builds so the API contract
 * is unchanged.
 */
class UserProfileResource extends JsonResource
{
    /**
     * @param  \App\Models\User  $resource  The authenticated user model
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the user model into the main profile payload.
     *
     * @return array{show_unlock_banner: bool, unlock_banner: array|null, user: array, membership: array, sections: array}
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user = $this->resource;

        $isSubscribed = (bool) ($user->is_subscribed ?? false);

        return [
            'show_unlock_banner' => ! $isSubscribed,
            'unlock_banner'      => $isSubscribed
                ? null
                : [
                    'title'       => 'Unlock Full Access',
                    'description' => 'Unlock the full learning path, composition tools, and ear training.',
                ],
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'dob'        => $user->dob?->format('Y-m-d'),
                'avatar_url' => $user->avatar_url,
                'tier'       => $user->plan_name ?? 'Tier 1',
            ],
            'membership' => [
                'current_plan'     => $user->plan_name ?? 'Tier 1',
                'next_billing_date' => $user->next_billing_date,
                'is_subscribed'    => $isSubscribed,
            ],
            'sections' => [
                [
                    'key'   => 'account',
                    'label' => 'ACCOUNT',
                    'items' => [
                        ['key' => 'edit_profile',   'label' => 'Edit Profile',   'description' => 'Name, email, photo',          'api_endpoint' => '/profile/details',            'method' => 'POST'],
                        ['key' => 'manage_account', 'label' => 'Manage Account', 'description' => 'Membership, data, privacy',   'api_endpoint' => '/profile/manage-account',     'method' => 'GET'],
                        ['key' => 'notifications',  'label' => 'Notifications',  'description' => 'Email and push preferences',  'api_endpoint' => '/notification-settings-show', 'method' => 'GET'],
                    ],
                ],
                [
                    'key'   => 'support',
                    'label' => 'SUPPORT',
                    'items' => [
                        ['key' => 'help_support', 'label' => 'Help & Support', 'description' => 'Contact, bug reports, feedback', 'api_endpoint' => '/support/contact', 'method' => 'POST'],
                    ],
                ],
                [
                    'key'   => 'other',
                    'label' => 'OTHER',
                    'items' => [
                        ['key' => 'rate_app', 'label' => 'Rate the App', 'description' => null, 'api_endpoint' => null,       'method' => null],
                        ['key' => 'logout',   'label' => 'Log Out',      'description' => null, 'api_endpoint' => '/logout',  'method' => 'POST'],
                    ],
                ],
            ],
        ];
    }
}
