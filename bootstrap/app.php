<?php

use App\Exceptions\GuestDeviceSyncException;
use App\Helpers\Helper;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\TrackGuestActivity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

// Fix mbstring encoding detection on Windows/Herd.
// mb_convert_encoding() without a source encoding returns false when PHP's
// mbstring has no detect order set, causing Dotenv's Str::utf8() to throw
// an InvalidEncodingException even on a perfectly valid UTF-8 .env file.
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('mb_detect_order')) {
    mb_detect_order('UTF-8, ISO-8859-1, Windows-1252');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware(['web', 'auth', 'Admin'])
                ->prefix('admin')
                ->group(base_path('routes/backend.php'));

            Route::middleware(['web', 'auth', 'Admin'])
                ->prefix('admin/settings')
                ->group(base_path('routes/settings.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'paddle/webhook',
        ]);

        $middleware->alias([
            'Admin'                    => AdminMiddleware::class,
            'track.guest.activity'     => TrackGuestActivity::class,
            'validate.guest.headers'   => \App\Http\Middleware\ValidateGuestHeaders::class,
            'maintenance.token'        => \App\Http\Middleware\MaintenanceTokenMiddleware::class,
            'paddle.payload.validate'  => \App\Http\Middleware\ValidatePaddleWebhookPayload::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (Throwable $e, Request $request) {

            // Only handle API routes
            if (! $request->is('api/*')) {
                return null;
            }

            // ── 422 Validation ───────────────────────────────────────────
            if ($e instanceof ValidationException) {
                $firstError = collect($e->errors())->flatten()->first() ?? $e->getMessage();
                return Helper::jsonErrorResponse($firstError, 422, $e->errors());
            }

            // ── 404 Model Not Found ──────────────────────────────────────
            if ($e instanceof ModelNotFoundException) {
                return Helper::jsonErrorResponse('Record not found.', 404);
            }

            // ── 401 Unauthenticated ──────────────────────────────────────
            if ($e instanceof AuthenticationException) {
                return Helper::jsonErrorResponse('Unauthenticated. Please log in.', 401);
            }

            // ── 403 Unauthorized ─────────────────────────────────────────
            if ($e instanceof AuthorizationException) {
                return Helper::jsonErrorResponse('You do not have permission to perform this action.', 403);
            }

            // ── Guest sync (non-fatal domain exception) ──────────────────
            // Should normally be caught by RegistrationService, but act as
            // a last-resort safety net here too.
            if ($e instanceof GuestDeviceSyncException) {
                Log::warning('Unhandled GuestDeviceSyncException reached global handler', [
                    'message' => $e->getMessage(),
                ]);
                return Helper::jsonErrorResponse('Guest data sync failed, but your account was created.', 200);
            }

            // ── 500 Database errors ──────────────────────────────────────
            // Never expose raw SQL / driver messages to the client.
            if ($e instanceof QueryException) {
                Log::error('Database query exception', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                return Helper::jsonErrorResponse('A database error occurred. Please try again later.', 500);
            }

            // ── Generic HTTP exceptions ──────────────────────────────────
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            if ($statusCode >= 500) {
                Log::error('Unhandled server exception', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                return Helper::jsonErrorResponse('An unexpected error occurred. Please try again later.', 500);
            }

            return Helper::jsonErrorResponse($e->getMessage(), $statusCode);
        });
    })
    ->create();
