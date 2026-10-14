<?php

namespace App\Repositories\Interfaces;

use App\Models\GuestDevice;

interface GuestDeviceRepositoryInterface
{
    /** Find by HMAC fingerprint (unique). */
    public function findByFingerprint(string $fingerprint): ?GuestDevice;

    /** Find by device_id (unique). */
    public function findByDeviceId(string $deviceId): ?GuestDevice;

    /** Find by token (unique). */
    public function findByToken(string $token): ?GuestDevice;

    /** Find by all three identifiers (device_id AND token AND guest_fingerprint). */
    public function findByIdentifier(string $deviceId, string $token, string $fingerprint): ?GuestDevice;

    /** Find by all three identifiers where user_id IS NULL (unmigrated). */
    public function findByIdentifierUnmigrated(string $deviceId, string $token, string $fingerprint): ?GuestDevice;

    /** Check if a record exists matching device_id AND token. */
    public function existsByIdentifier(string $deviceId, string $token): bool;

    /** Find by fingerprint with FOR UPDATE lock (for race-condition-safe create). */
    public function findByFingerprintLockForUpdate(string $fingerprint): ?GuestDevice;

    /** Find by device_id with FOR UPDATE lock. */
    public function findByDeviceIdLockForUpdate(string $deviceId): ?GuestDevice;

    /** Find by token with FOR UPDATE lock. */
    public function findByTokenLockForUpdate(string $token): ?GuestDevice;

    /** Create a new GuestDevice record. */
    public function create(array $data): GuestDevice;
}
