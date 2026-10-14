<?php

namespace App\Http\Requests\Web\Backend\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Validation\Rule;

/**
 * Validate an update request for a user.
 */
class UpdateUserRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => 'nullable|string|max:100',
            'email' => ['nullable', 'string', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => 'required|max:20',
            'password' => 'nullable|string',
            'dob' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'required|in:User,Admin',
            'is_subscribed' => 'nullable|boolean',
            'subscription_tier' => 'nullable|in:tier_1,tier_2,tier_3',
        ];
    }
}
