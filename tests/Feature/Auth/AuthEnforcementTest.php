<?php

namespace Tests\Feature\Auth;

use App\Models\GuestDevice;
use App\Models\User;
use App\Services\GuestFingerprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Comprehensive authentication enforcement tests for ALL routes in routes/api.php.
 *
 * Tests verify that every endpoint correctly enforces its authentication group:
 *
 *   Group 1: Guest Device  (2)  — POST /guest/identify: no auth; GET /guest/device: guest headers
 *   Group 2: Public        (13) — No auth required at all
 *   Group 3: Guest-Aware   (22) — Guest headers OR auth:sanctum
 *   Group 4: Cookie        (2)  — Guest headers only
 *   Group 5: Webhook       (1)  — Server-to-server auth (no user auth)
 *   Group 6: Guest Auth    (7)  — Only unauthenticated users (guest:sanctum)
 *   Group 7: Authenticated (22) — auth:sanctum required
 *
 * Each test sends a minimal request (empty JSON body for POST, dummy IDs for URL params)
 * and asserts the AUTH RESPONSE, not the controller logic. A 422 validation error means
 * "you passed the auth gate but the request data is invalid" — which is exactly what we
 * want for auth enforcement testing.
 */
class AuthEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private GuestFingerprintService $fingerprintService;
    private User $user;
    private string $deviceId;
    private string $token;
    private string $guestFingerprint;
    private array $guestHeaders;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fingerprintService = app(GuestFingerprintService::class);

        // Create a reusable authenticated user
        $this->user = User::factory()->create();

        // Create a reusable guest device
        $this->deviceId        = 'enforce_device_' . uniqid();
        $this->token           = 'enforce_token_' . uniqid();
        $this->guestFingerprint = $this->fingerprintService->compute($this->deviceId, $this->token);

        GuestDevice::create([
            'device_id'         => $this->deviceId,
            'token'             => $this->token,
            'guest_fingerprint' => $this->guestFingerprint,
        ]);

        $this->guestHeaders = [
            'X-Guest-Device-Id'   => $this->deviceId,
            'X-Guest-Token'       => $this->token,
            'X-Guest-Fingerprint' => $this->guestFingerprint,
        ];
    }

    /* ==================================================================
     * HELPERS
     * ================================================================*/

    /**
     * Send a minimal request to a route. POST routes get empty JSON body.
     * URL parameters like {id} are replaced with "1".
     */
    private function request(string $method, string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        // Replace URL parameter placeholders with dummy values
        $uri = preg_replace('/\{[^}]+\}/', '1', $uri);

        // Strip leading /api/ if present (routes are defined without /api/ prefix)
        $uri = ltrim($uri, '/');

        // Ensure the route references are correct by checking if uri starts with 'api/'
        if (!str_starts_with($uri, 'api/')) {
            // Check if just a path without /api prefix
            $uri = $uri;
        }

        $server = $this->transformHeadersToServerVars($headers);

        return $this->call($method, $uri, [], [], [], $server);
    }



    /* ==================================================================
     * GROUP 1: Guest Device Routes
     *
     * POST /guest/identify — No auth (public)
     * GET  /guest/device   — Requires guest headers
     * ================================================================*/

    /** @test */
    public function guest_identify_works_without_any_auth(): void
    {
        // No auth, no headers — should work (returns 422 validation because no body)
        $response = $this->postJson('/api/guest/identify', []);
        // Should NOT be 401 (no auth needed)
        $this->assertNotEquals(401, $response->getStatusCode(), 'POST /guest/identify should not require auth');
        // Should be 422 (validation error — missing device_id/token)
        $this->assertEquals(422, $response->getStatusCode());
    }

    /** @test */
    public function guest_device_endpoint_requires_guest_headers(): void
    {
        // Without headers → should be blocked by middleware
        $response = $this->getJson('/api/guest/device');
        $this->assertEquals(422, $response->getStatusCode(), 'GET /guest/device without headers should return 422');
    }

    /** @test */
    public function guest_device_endpoint_works_with_valid_headers(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->getJson('/api/guest/device');

        // Should pass the auth gate (may return 200 or other status, but not 401/header-422)
        $this->assertNotEquals(401, $response->getStatusCode(),
            'GET /guest/device with valid headers should not return 401');
        $body = $response->json();
        if ($response->getStatusCode() === 422) {
            $headersErr = $body['errors']['headers'] ?? null;
            $this->assertNull($headersErr, 'GET /guest/device with valid headers should not get header error');
        }
    }

    /* ==================================================================
     * GROUP 2: Public Routes
     *
     * These routes MUST work without any authentication at all.
     * ================================================================*/

    public static function publicRoutesProvider(): array
    {
        return [
            'GET  /system-setting'            => ['GET', '/api/system-setting'],
            'GET  /faq'                        => ['GET', '/api/faq'],
            'GET  /reviews'                    => ['GET', '/api/reviews'],
            'POST /social/login'               => ['POST', '/api/social/login'],
            'POST /social/apple/callback'      => ['POST', '/api/social/apple/callback'],
            'POST /support/contact'            => ['POST', '/api/support/contact'],
            'POST /support/bug-report'         => ['POST', '/api/support/bug-report'],
            'POST /support/feature-suggestion' => ['POST', '/api/support/feature-suggestion'],
            'GET  /cms/user-guide'             => ['GET', '/api/cms/user-guide'],
            'GET  /cms/terms-conditions'       => ['GET', '/api/cms/terms-conditions'],
            'GET  /cms/privacy-policy'         => ['GET', '/api/cms/privacy-policy'],
            'GET  /cms/glossary'               => ['GET', '/api/cms/glossary'],
            'GET  /cms/complete-glossary'      => ['GET', '/api/cms/complete-glossary'],
            'GET  /cms/about-numonix'          => ['GET', '/api/cms/about-numonix'],
        ];
    }

    /**
     * @test
     * @dataProvider publicRoutesProvider
     */
    public function public_route_works_without_auth(string $method, string $uri): void
    {
        $response = $this->request($method, $uri);

        // Must NOT be 401 — these routes are completely open
        $this->assertNotEquals(401, $response->getStatusCode(),
            "Public route [{$method} {$uri}] should not require auth");

        // Must NOT be 422 for missing guest headers (no headers required)
        $body = $response->json();
        if ($response->getStatusCode() === 422 && $body) {
            $headersErr = $body['errors']['headers'] ?? null;
            $this->assertNull($headersErr,
                "Public route [{$method} {$uri}] returned 422 with guest header error");
        }
    }

    /* ==================================================================
     * GROUP 3: Guest-Aware Routes (22 endpoints)
     * GROUP 4: Cookie Settings Routes (2 endpoints)
     *
     * These routes:
     *   - WITHOUT headers + WITHOUT auth  → 422 (missing guest headers)
     *   - WITH valid guest headers        → pass (may return 200 or 422 validation)
     *   - WITH auth:sanctum token         → pass (bypasses header check)
     * ================================================================*/

    public static function guestAwareRoutesProvider(): array
    {
        return [
            // Home
            'GET  /home'                              => ['GET', '/api/home'],
            // Learning listing
            'GET  /learnings'                         => ['GET', '/api/learnings'],
            'GET  /learnings/lessons'                 => ['GET', '/api/learnings/lessons'],
            'GET  /learnings/core-theory'             => ['GET', '/api/learnings/core-theory'],
            'GET  /learnings/advanced-insights'       => ['GET', '/api/learnings/advanced-insights'],
            'GET  /learnings/case-studies'            => ['GET', '/api/learnings/case-studies'],
            'GET  /learnings/fnh-architecture'        => ['GET', '/api/learnings/fnh-architecture'],
            'GET  /learnings/show/{id}'               => ['GET', '/api/learnings/show/{id}'],
            'GET  /learnings/summary/{id}'            => ['GET', '/api/learnings/summary/{id}'],
            // Learning progress
            'POST /learnings/completed'               => ['POST', '/api/learnings/completed'],
            'POST /learnings/checklist/update'        => ['POST', '/api/learnings/checklist/update'],
            // Ear training
            'GET  /ear-training/stages'                => ['GET', '/api/ear-training/stages'],
            // Quiz
            'GET  /quiz/by-learning/{id}'             => ['GET', '/api/quiz/by-learning/{id}'],
            'POST /quiz/check-answer'                 => ['POST', '/api/quiz/check-answer'],
            'POST /quiz/submit/{id}'                  => ['POST', '/api/quiz/submit/{id}'],
            'GET  /quiz/review/{id}'                  => ['GET', '/api/quiz/review/{id}'],
            'GET  /quiz/review-by-learning/{id}'      => ['GET', '/api/quiz/review-by-learning/{id}'],
            'GET  /quiz/attempt-details/{id}'         => ['GET', '/api/quiz/attempt-details/{id}'],
            'POST /quiz/retry/{id}'                   => ['POST', '/api/quiz/retry/{id}'],
            // User progress
            'GET  /user/progress'                     => ['GET', '/api/user/progress'],
            // Cookie settings (Group 4 — separate middleware group but same header check)
            'GET  /cookie-settings-show'              => ['GET', '/api/cookie-settings-show'],
            'POST /cookie-settings-update'            => ['POST', '/api/cookie-settings-update'],
        ];
    }

    /**
     * @test
     * @dataProvider guestAwareRoutesProvider
     */
    public function guest_aware_route_blocks_without_headers(string $method, string $uri): void
    {
        // No headers, no auth → should be blocked (422) by guest middleware
        $response = $this->request($method, $uri);
        $this->assertEquals(422, $response->getStatusCode(),
            "[{$method} {$uri}] without guest headers should return 422");
    }

    /**
     * @test
     * @dataProvider guestAwareRoutesProvider
     */
    public function guest_aware_route_works_with_valid_headers(string $method, string $uri): void
    {
        // With valid guest headers → should pass the middleware
        $response = $this->request($method, $uri, $this->guestHeaders);

        // Should not be 401 for invalid credentials
        $this->assertNotEquals(401, $response->getStatusCode(),
            "[{$method} {$uri}] with valid guest headers should not return 401");

        // Should NOT be 422 for missing headers
        $body = $response->json();
        if ($response->getStatusCode() === 422 && $body) {
            $headersErr = $body['errors']['headers'] ?? null;
            $this->assertNull($headersErr,
                "[{$method} {$uri}] with valid guest headers returned 422 for missing headers");
        }
    }

    /**
     * @test
     * @dataProvider guestAwareRoutesProvider
     */
    public function guest_aware_route_works_with_auth_token(string $method, string $uri): void
    {
        // Authenticated users bypass guest header validation
        Sanctum::actingAs($this->user);

        $response = $this->request($method, $uri);

        // Auth users should never get 401 or header-related 422
        $this->assertNotEquals(401, $response->getStatusCode(),
            "[{$method} {$uri}] with auth token should not return 401");

        $body = $response->json();
        if ($response->getStatusCode() === 422 && $body) {
            $headersErr = $body['errors']['headers'] ?? null;
            $this->assertNull($headersErr,
                "[{$method} {$uri}] with auth token returned 422 for missing guest headers");
        }
    }

    /* ==================================================================
     * GROUP 5: Webhook Route
     *
     * POST /paddle/webhook — Server-to-server (no user auth)
     * ================================================================*/

    /** @test */
    public function webhook_route_works_without_auth(): void
    {
        $response = $this->postJson('/paddle/webhook', []);
        $this->assertNotEquals(401, $response->getStatusCode(),
            'POST /paddle/webhook should not require user auth');
    }

    /* ==================================================================
     * GROUP 6: Guest Auth Routes (guest:sanctum)
     *
     * These routes are ONLY accessible when NOT authenticated.
     * Authenticated users get 401 from the guest:sanctum middleware.
     * ================================================================*/

    public static function guestAuthRoutesProvider(): array
    {
        return [
            'POST /login'                      => ['POST', '/api/login'],
            'POST /register'                   => ['POST', '/api/register'],
            'POST /email/verify/resend'        => ['POST', '/api/email/verify/resend'],
            'POST /email/verify'               => ['POST', '/api/email/verify'],
            'POST /password/forgot'            => ['POST', '/api/password/forgot'],
            'POST /password/forgot/verify'     => ['POST', '/api/password/forgot/verify'],
            'POST /password/reset'             => ['POST', '/api/password/reset'],
        ];
    }

    /**
     * @test
     * @dataProvider guestAuthRoutesProvider
     */
    public function guest_auth_route_works_without_token(string $method, string $uri): void
    {
        // Without auth token → should pass the guest:sanctum middleware
        $response = $this->request($method, $uri);

        // Should NOT be 401/302 (guest:sanctum only blocks authenticated users)
        $this->assertNotContains($response->getStatusCode(), [401, 302],
            "[{$method} {$uri}] without auth should not return 401/302 — guest:sanctum only blocks authenticated users");
    }

    /**
     * @test
     * @dataProvider guestAuthRoutesProvider
     */
    public function guest_auth_route_blocks_authenticated_users(string $method, string $uri): void
    {
        // With auth token → should be BLOCKED by guest:sanctum
        // (returns 302 redirect to web login or 401 JSON depending on Accept header)
        Sanctum::actingAs($this->user);

        $response = $this->request($method, $uri);

        $this->assertContains($response->getStatusCode(), [302, 401],
            "[{$method} {$uri}] should return 302 or 401 when user is already authenticated (guest:sanctum middleware)");
    }

    /* ==================================================================
     * GROUP 7: Authenticated Routes (auth:sanctum)
     *
     * These routes REQUIRE a valid Sanctum Bearer token.
     * ================================================================*/

    public static function authenticatedRoutesProvider(): array
    {
        return [
            'GET  /profile'                          => ['GET', '/api/profile'],
            'POST /profile/download-my-data'         => ['POST', '/api/profile/download-my-data'],
            'POST /logout'                           => ['POST', '/api/logout'],
            'POST /logout/all'                       => ['POST', '/api/logout/all'],
            'GET  /profile/manage-account'           => ['GET', '/api/profile/manage-account'],
            'POST /profile/details'                  => ['POST', '/api/profile/details'],
            'POST /profile/change-password'          => ['POST', '/api/profile/change-password'],
            'POST /profile/delete-account'           => ['POST', '/api/profile/delete-account'],
            'GET  /notification-settings-show'       => ['GET', '/api/notification-settings-show'],
            'POST /notification-settings-update'     => ['POST', '/api/notification-settings-update'],
            'POST /learnings/next'                   => ['POST', '/api/learnings/next'],
            'POST /learnings/previous'               => ['POST', '/api/learnings/previous'],
            'GET  /quiz/questions'                   => ['GET', '/api/quiz/questions'],
            'GET  /subscription/status'              => ['GET', '/api/subscription/status'],
            'POST /subscription/checkout'            => ['POST', '/api/subscription/checkout'],
            'GET  /founder/status'                   => ['GET', '/api/founder/status'],
            'POST /founder/review'                   => ['POST', '/api/founder/review'],
            'GET  /dashboard'                        => ['GET', '/api/dashboard'],
            'GET  /preferences'                      => ['GET', '/api/preferences'],
            'POST /preferences'                      => ['POST', '/api/preferences'],
            'POST /firebase-token/add'               => ['POST', '/api/firebase-token/add'],
            'POST /firebase-token/delete'            => ['POST', '/api/firebase-token/delete'],
            'POST /notifications/send'               => ['POST', '/api/notifications/send'],
        ];
    }

    /**
     * @test
     * @dataProvider authenticatedRoutesProvider
     */
    public function authenticated_route_blocks_without_token(string $method, string $uri): void
    {
        // Without auth token → should be blocked (401)
        $response = $this->request($method, $uri);

        $this->assertEquals(401, $response->getStatusCode(),
            "[{$method} {$uri}] without auth token should return 401");
    }

    /**
     * @test
     * @dataProvider authenticatedRoutesProvider
     */
    public function authenticated_route_works_with_valid_token(string $method, string $uri): void
    {
        // With valid auth token → should pass
        Sanctum::actingAs($this->user);

        $response = $this->request($method, $uri);

        $this->assertNotEquals(401, $response->getStatusCode(),
            "[{$method} {$uri}] with valid token should not return 401");

        // Should NOT be a guest-header error
        $json = $response->json();
        $message = $json['message'] ?? '';
        if ($response->getStatusCode() === 422 && str_contains($message, 'guest headers')) {
            $this->fail("Route [{$method} {$uri}] with auth token returned 422 for missing guest headers — authenticated routes should not require guest headers.");
        }
    }

    /* ==================================================================
     * EDGE CASES
     * ================================================================*/

    /** @test */
    public function guest_aware_route_rejects_wrong_fingerprint(): void
    {
        // Valid device_id + token, but WRONG fingerprint
        $wrongHeaders = [
            'X-Guest-Device-Id'   => $this->deviceId,
            'X-Guest-Token'       => $this->token,
            'X-Guest-Fingerprint' => 'this_is_a_tampered_fingerprint',
        ];

        $response = $this->withHeaders($wrongHeaders)
            ->getJson('/api/learnings');

        $response->assertStatus(422);
        $response->assertJsonPath('status', 422);
    }

    /** @test */
    public function guest_aware_route_rejects_nonexistent_device(): void
    {
        $response = $this->withHeaders([
            'X-Guest-Device-Id'   => 'nonexistent_device',
            'X-Guest-Token'       => 'nonexistent_token',
            'X-Guest-Fingerprint' => $this->fingerprintService->compute('nonexistent_device', 'nonexistent_token'),
        ])->getJson('/api/learnings');

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Invalid guest credentials.');
    }

    /** @test */
    public function authenticated_user_bypasses_all_guest_aware_routes_without_headers(): void
    {
        Sanctum::actingAs($this->user);

        // Test a sample of guest-aware routes — all should pass without headers
        $routes = [
            '/api/home',
            '/api/learnings',
            '/api/ear-training/stages',
            '/api/user/progress',
            '/api/cookie-settings-show',
        ];

        foreach ($routes as $uri) {
            $response = $this->getJson($uri);
            $this->assertNotEquals(422, $response->getStatusCode(),
                "Authenticated user blocked on [GET {$uri}] with 422 — should bypass guest header check");
            $this->assertNotEquals(401, $response->getStatusCode(),
                "Authenticated user blocked on [GET {$uri}] with 401");
        }
    }

    /** @test */
    public function all_public_routes_are_accessible_without_any_headers(): void
    {
        $routes = [
            '/api/system-setting',
            '/api/faq',
            '/api/reviews',
            '/api/support/contact',
            '/api/cms/user-guide',
            '/api/cms/terms-conditions',
            '/api/cms/privacy-policy',
            '/api/cms/glossary',
            '/api/cms/complete-glossary',
            '/api/cms/about-numonix',
        ];

        foreach ($routes as $uri) {
            $response = $this->getJson($uri);
            $status = $response->getStatusCode();
            $this->assertNotEquals(401, $status,
                "Public route [GET {$uri}] returned 401 but should be open");
            $this->assertNotEquals(422, $status,
                "Public route [GET {$uri}] returned 422 but should be open (no headers required)");
        }
    }

    /** @test */
    public function guest_identify_rejects_empty_body(): void
    {
        $response = $this->postJson('/api/guest/identify', []);
        $response->assertStatus(422); // Validation failure, not auth failure
        $response->assertJsonValidationErrors(['device_id', 'token']);
    }

    /** @test */
    public function guest_identify_accepts_valid_body(): void
    {
        $response = $this->postJson('/api/guest/identify', [
            'device_id' => 'fresh_device',
            'token'     => 'fresh_token',
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.is_new', true);
    }
}
