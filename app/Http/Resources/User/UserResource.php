<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for the User model.
 *
 * Explicitly serializes all visible attributes from the User model so that
 * the JSON output remains identical to the default Eloquent serialization
 * while providing a stable, explicit contract for the API layer.
 */
class UserResource extends JsonResource
{
    /**
     * Transform the user model into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user = $this->resource;

        return [
            'id'                     => $user->id,
            'name'                   => $user->name,
            'email'                  => $user->email,
            'phone'                  => $user->phone,
            'dob'                    => $user->dob?->format('Y-m-d'),
            'avatar'                 => $user->avatar,
            'avatar_url'             => $user->avatar_url,
            'socialite_id'           => $user->socialite_id,
            'socialite_provider'     => $user->socialite_provider,
            'socialite_token'        => $user->socialite_token,
            'socialite_refresh_token' => $user->socialite_refresh_token,
            'socialite_expires_at'   => $user->socialite_expires_at,
            'is_age_verified'        => $user->is_age_verified,
            'role'                   => $user->role,
            'status'                 => $user->status,
            'is_subscribed'          => (bool) ($user->is_subscribed ?? false),
            'subscription_tier'      => $user->subscription_tier,
            'rc_customer_id'         => $user->rc_customer_id,
            'rc_entitlement'         => $user->rc_entitlement,
            'plan_name'              => $user->plan_name,
            'next_billing_date'      => $user->next_billing_date?->format('Y-m-d'),
            'is_online'              => (bool) ($user->is_online ?? false),
            'is_agree'               => (bool) ($user->is_agree ?? false),
            'email_verified_at'      => $user->email_verified_at?->toISOString(),
        ];
    }
}
