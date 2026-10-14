<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Requires either:
     * 1. A valid MAINTENANCE_TOKEN query parameter or X-Maintenance-Token header.
     * 2. An authenticated Admin user session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceToken = env('MAINTENANCE_TOKEN');
        $tokenParam = $request->query('token') ?? $request->header('X-Maintenance-Token');

        $isAuthorized = (
            (!empty($maintenanceToken) && $tokenParam === $maintenanceToken) ||
            (Auth::check() && Auth::user()->role === 'Admin')
        );

        if (!$isAuthorized) {
            abort(403, 'Unauthorized maintenance route access.');
        }

        return $next($request);
    }
}
