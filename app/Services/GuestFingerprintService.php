<?php

namespace App\Services;

/**
 * -------------------------------------------------------------
 *  SINGLE SOURCE OF TRUTH for guest fingerprint operations
 * -------------------------------------------------------------
 *
 * This service is the ONLY place in the entire project where
 * guest fingerprints are computed, validated, and compared.
 *
 * == RULES ==
 *
 * 1. ONLY POST /api/guest/identify may generate guest identities.
 * 2. The fingerprint is ALWAYS computed server-side via HMAC-SHA256
 *    using device_id + token + APP_KEY.
 * 3. NEVER trust a fingerprint sent by the client.
 * 4. NEVER use IP, User-Agent, random hashes, or any fallback.
 * 5. ALWAYS use hash_equals() for constant-time comparison.
 * 6. Compare BOTH the client-provided AND stored fingerprints
 *    against the authoritative server computation.
 *
 * == ALGORITHM ==
 *
 *   fingerprint = hash_hmac('sha256', device_id . '|' . token, APP_KEY)
 *
 * This is deterministic, non-forgeable, and independent of
 * network context (IP, User-Agent, headers, etc.).
 */
class GuestFingerprintService
{
    /**
     * Compute the HMAC-SHA256 fingerprint for a given device_id and token.
     *
     * Pure function: same inputs always produce the same 64-char hex output,
     * regardless of request context.
     *
     * The returned value is lowercase hex (hash_hmac always returns lowercase).
     *
     * @param  string  $deviceId  The device identifier
     * @param  string  $token  The device token
     * @return string  The 64-character lowercase hex HMAC-SHA256 fingerprint
     */
    public function compute(string $deviceId, string $token): string
    {
        $deviceId = $this->normalize($deviceId);
        $token = $this->normalize($token);

        return hash_hmac('sha256', $deviceId.'|'.$token, config('app.key'));
    }

    /**
     * Validate a client-provided fingerprint against the server-computed one.
     *
     * Both values are lowercased and trimmed before comparison so that
     * minor transport-layer variations (case, whitespace) are absorbed here
     * rather than scattered across callers.
     *
     * Uses hash_equals() for timing-attack-resistant comparison.
     *
     * @param  string  $deviceId  The device_id sent by the client.
     * @param  string  $token  The token sent by the client.
     * @param  string  $providedFingerprint  The fingerprint sent by the client (or stored in DB).
     * @return bool True if the provided fingerprint matches the server computation.
     */
    public function matches(string $deviceId, string $token, string $providedFingerprint): bool
    {
        $serverFingerprint = $this->compute($deviceId, $token);
        $normalizedProvided = strtolower(trim($providedFingerprint));

        return hash_equals($serverFingerprint, $normalizedProvided);
    }

    /**
     * Normalize a credential value: trim whitespace, null -> empty string.
     *
     * @param  string|null  $value  The raw credential value to normalize
     * @return string  The trimmed string, or empty string if null
     */
    private function normalize(?string $value): string
    {
        return trim((string) ($value ?? ''));
    }
}
