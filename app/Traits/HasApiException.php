<?php

namespace App\Traits;

use App\Exceptions\GuestDeviceSyncException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Provides a consistent, safe exception-to-JSON-response mapping for API controllers.
 *
 * Usage: add `use HasApiException;` to any controller, then call
 * `return $this->handleException($e);` from a catch block.
 */
trait HasApiException
{
    use ApiResponse;

    /**
     * Map a caught exception to the appropriate JSON error response.
     *
     * Sensitive detail (raw SQL, stack traces) is never exposed to the client.
     * All 500-level errors are logged before responding.
     */
    public function handleException(Exception|Throwable $exception): JsonResponse
    {
        // -- 422 Validation -----------------------------------------------
        if ($exception instanceof ValidationException) {
            return $this->error($exception->getMessage(), 422, $exception->errors());
        }

        // -- 404 Model Not Found ------------------------------------------
        if ($exception instanceof ModelNotFoundException) {
            return $this->error('Record not found.', 404);
        }

        // -- Guest sync (non-fatal) ---------------------------------------
        // Should normally be swallowed by RegistrationService, but guard here too.
        if ($exception instanceof GuestDeviceSyncException) {
            Log::warning('GuestDeviceSyncException reached controller handler', [
                'message' => $exception->getMessage(),
            ]);
            return $this->error('Guest data sync failed, but your account was created.', 200);
        }

        // -- 500 Database errors ------------------------------------------
        if ($exception instanceof QueryException) {
            Log::error('Database QueryException in API controller', [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);
            return $this->error('A database error occurred. Please try again later.', 500);
        }

        // -- Fallback: unexpected exception -------------------------------
        Log::error('Unhandled exception in API controller', [
            'class'   => get_class($exception),
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTraceAsString(),
        ]);

        return $this->error('Something went wrong. Please try again later.', 500);
    }
}
