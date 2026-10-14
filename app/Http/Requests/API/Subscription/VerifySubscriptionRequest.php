<?php

namespace App\Http\Requests\API\Subscription;

use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate a subscription verification request.
 */
class VerifySubscriptionRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rc_customer_id' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'rc_customer_id.required' => 'A subscription customer ID is required to verify your subscription.',
        ];
    }
}
