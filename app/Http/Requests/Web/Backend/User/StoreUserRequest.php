<?php

namespace App\Http\Requests\Web\Backend\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate and persist a new user.
 */
class StoreUserRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'phone' => 'nullable|max:20',
            'dob' => 'nullable|date',
            'password' => 'required|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'required|in:User,Admin',
            'is_subscribed' => 'nullable|boolean',
            'subscription_tier' => 'nullable|in:tier_1,tier_2,tier_3',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your Name.',
            'name.max' => 'The Name must not exceed 255 characters.',
            'email.required' => 'Please enter your Email.',
            'email.email' => 'Please enter a valid email address for Email.',
            'email.unique' => 'This Email is already taken. Please use a different one.',
            'phone.max' => 'The Phone must not exceed 20 characters.',
            'dob.date' => 'Please enter a valid date for Dob.',
            'password.required' => 'Please enter your Password.',
            'avatar.image' => 'The Avatar must be an image file.',
            'avatar.mimes' => 'The Avatar must be a file of type: jpeg, png, jpg, gif, svg.',
            'avatar.max' => 'The Avatar must not exceed 2048 characters.',
            'role.required' => 'Please enter your Role.',
            'role.in' => 'The Role must be one of: User, Admin.',
            'subscription_tier.in' => 'The Subscription Tier must be one of: tier_1, tier_2, tier_3.',
        ];
    }

}
