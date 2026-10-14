<?php

namespace App\Repositories;

use App\Models\GuestDevice;
use App\Repositories\Interfaces\GuestDeviceRepositoryInterface;

class GuestDeviceRepository implements GuestDeviceRepositoryInterface
{
    public function findByFingerprint(string $fingerprint): ?GuestDevice
    {
        return GuestDevice::where('guest_fingerprint', $fingerprint)->first();
    }

    public function findByDeviceId(string $deviceId): ?GuestDevice
    {
        return GuestDevice::where('device_id', $deviceId)->first();
    }

    public function findByToken(string $token): ?GuestDevice
    {
        return GuestDevice::where('token', $token)->first();
    }

    public function findByIdentifier(string $deviceId, string $token, string $fingerprint): ?GuestDevice
    {
        return GuestDevice::where('device_id', $deviceId)
            ->where('token', $token)
            ->where('guest_fingerprint', $fingerprint)
            ->first();
    }

    public function findByIdentifierUnmigrated(string $deviceId, string $token, string $fingerprint): ?GuestDevice
    {
        return GuestDevice::where('device_id', $deviceId)
            ->where('token', $token)
            ->where('guest_fingerprint', $fingerprint)
            ->unmigrated()
            ->first();
    }

    public function existsByIdentifier(string $deviceId, string $token): bool
    {
        return GuestDevice::where('device_id', $deviceId)
            ->where('token', $token)
            ->exists();
    }

    public function findByFingerprintLockForUpdate(string $fingerprint): ?GuestDevice
    {
        return GuestDevice::where('guest_fingerprint', $fingerprint)
            ->lockForUpdate()
            ->first();
    }

    public function findByDeviceIdLockForUpdate(string $deviceId): ?GuestDevice
    {
        return GuestDevice::where('device_id', $deviceId)
            ->lockForUpdate()
            ->first();
    }

    public function findByTokenLockForUpdate(string $token): ?GuestDevice
    {
        return GuestDevice::where('token', $token)
            ->lockForUpdate()
            ->first();
    }

    public function create(array $data): GuestDevice
    {
        return GuestDevice::create($data);
    }
}
