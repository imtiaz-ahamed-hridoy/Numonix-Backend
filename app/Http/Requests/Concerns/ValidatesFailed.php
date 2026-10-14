<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Provides consistent validation failure handling for both
 * web (admin panel) and API form request classes.
 *
 * - Web requests: redirects back with errors + validation messages
 *   so the global toastr handler in scripts.blade.php can display them.
 * - API requests: returns structured 422 JSON matching ApiResponse format.
 */
trait ValidatesFailed
{
    /**
     * Handle a failed validation attempt.
     *
     * For web requests, throws a standard ValidationException which
     * Laravel converts into a redirect-back with $errors in the session.
     * For API/AJAX requests, returns a 422 JSON response.
     */
    protected function failedValidation(Validator $validator): void
    {
        // API or AJAX request -> return JSON
        if ($this->expectsJson()) {
            $errors = $validator->errors();

            throw new HttpResponseException(
                response()->json([
                    'status' => 'error',
                    'message' => $errors->first(),
                    'errors' => $errors->toArray(),
                    'meta' => [
                        'errors' => $errors->toArray(),
                    ],
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        // Web request -> redirect back with errors (toastr will pick them up)
        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
