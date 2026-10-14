<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates that an incoming Paddle webhook request has the minimum required
 * structure before passing it to Cashier's WebhookController.
 *
 * This prevents a 500 from Cashier when the payload is malformed (e.g. missing
 * the event_type key). Malformed requests are rejected with 400 Bad Request.
 *
 * When PADDLE_WEBHOOK_SECRET is configured, signature verification is already
 * handled by Cashier's VerifyWebhookSignature middleware. This middleware runs
 * first to guard against malformed JSON causing uncaught ErrorExceptions.
 */
class ValidatePaddleWebhookPayload
{
    public function handle(Request $request, Closure $next): Response
    {
        // Decode the raw JSON body (Paddle webhooks are always JSON)
        $rawBody = $request->getContent();

        if (empty($rawBody)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid Paddle webhook payload: empty request body.',
            ], 400);
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid Paddle webhook payload: not valid JSON.',
            ], 400);
        }

        // Paddle v2 webhooks always include event_type at the top level
        if (! array_key_exists('event_type', $payload)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid Paddle webhook payload: missing event_type.',
            ], 400);
        }

        if (! is_string($payload['event_type']) || empty(trim($payload['event_type']))) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid Paddle webhook payload: event_type must be a non-empty string.',
            ], 400);
        }

        return $next($request);
    }
}
