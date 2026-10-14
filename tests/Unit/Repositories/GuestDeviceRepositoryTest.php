<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\GuestDeviceRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestDeviceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GuestDeviceRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(GuestDeviceRepositoryInterface::class);
    }

    // ─── create() ────────────────────────────────────────────────────────

    public function test_create_returns_guest_device_with_given_data(): void
    {
        $device = $this->repository->create([
            'device_id' => 'device-abc-123',
            'token' => 'tok-xyz-789',
            'guest_fingerprint' => 'fp_' . str_repeat('a', 60),
        ]);

        $this->assertNotNull($device);
        $this->assertSame('device-abc-123', $device->device_id);
        $this->assertSame('tok-xyz-789', $device->token);
        $this->assertDatabaseHas('guest_devices', [
            'device_id' => 'device-abc-123',
            'token' => 'tok-xyz-789',
        ]);
    }

    public function test_create_sets_default_timestamps(): void
    {
        $device = $this->repository->create([
            'device_id' => 'device-timestamp-test',
            'token' => 'tok-timestamp-test',
            'guest_fingerprint' => 'fp_timestamp_' . str_repeat('b', 48),
        ]);

        $this->assertNotNull($device->created_at);
        $this->assertNotNull($device->updated_at);
    }

    // ─── findByFingerprint() ─────────────────────────────────────────────

    public function test_find_by_fingerprint_returns_device_when_match_exists(): void
    {
        $this->repository->create([
            'device_id' => 'dev-fp-1',
            'token' => 'tok-fp-1',
            'guest_fingerprint' => 'fp_find_test_' . str_repeat('c', 46),
        ]);

        $found = $this->repository->findByFingerprint('fp_find_test_' . str_repeat('c', 46));

        $this->assertNotNull($found);
        $this->assertSame('dev-fp-1', $found->device_id);
    }

    public function test_find_by_fingerprint_returns_null_when_no_match(): void
    {
        $result = $this->repository->findByFingerprint('nonexistent_fingerprint');

        $this->assertNull($result);
    }

    public function test_find_by_fingerprint_matches_exactly_not_partial(): void
    {
        $this->repository->create([
            'device_id' => 'dev-fp-exact',
            'token' => 'tok-fp-exact',
            'guest_fingerprint' => 'fp_exact_match_' . str_repeat('d', 44),
        ]);

        // Slightly different fingerprint should NOT match
        $result = $this->repository->findByFingerprint('fp_exact_match_' . str_repeat('d', 43) . 'x');
        $this->assertNull($result);
    }

    // ─── findByDeviceId() ────────────────────────────────────────────────

    public function test_find_by_device_id_returns_device_when_match_exists(): void
    {
        $this->repository->create([
            'device_id' => 'dev-find-did',
            'token' => 'tok-find-did',
            'guest_fingerprint' => 'fp_did_' . str_repeat('e', 53),
        ]);

        $found = $this->repository->findByDeviceId('dev-find-did');

        $this->assertNotNull($found);
        $this->assertSame('dev-find-did', $found->device_id);
    }

    public function test_find_by_device_id_returns_null_when_no_match(): void
    {
        $result = $this->repository->findByDeviceId('nonexistent-device');

        $this->assertNull($result);
    }

    // ─── findByToken() ───────────────────────────────────────────────────

    public function test_find_by_token_returns_device_when_match_exists(): void
    {
        $this->repository->create([
            'device_id' => 'dev-find-tok',
            'token' => 'tok-find-tok-unique',
            'guest_fingerprint' => 'fp_tok_' . str_repeat('f', 53),
        ]);

        $found = $this->repository->findByToken('tok-find-tok-unique');

        $this->assertNotNull($found);
        $this->assertSame('tok-find-tok-unique', $found->token);
    }

    public function test_find_by_token_returns_null_when_no_match(): void
    {
        $result = $this->repository->findByToken('nonexistent-token');

        $this->assertNull($result);
    }

    // ─── findByIdentifier() ──────────────────────────────────────────────

    public function test_find_by_identifier_returns_device_when_all_three_match(): void
    {
        $this->repository->create([
            'device_id' => 'dev-id-all',
            'token' => 'tok-id-all',
            'guest_fingerprint' => 'fp_id_all_' . str_repeat('g', 49),
        ]);

        $found = $this->repository->findByIdentifier('dev-id-all', 'tok-id-all', 'fp_id_all_' . str_repeat('g', 49));

        $this->assertNotNull($found);
        $this->assertSame('dev-id-all', $found->device_id);
    }

    public function test_find_by_identifier_returns_null_when_device_id_mismatches(): void
    {
        $this->repository->create([
            'device_id' => 'dev-id-mismatch',
            'token' => 'tok-id-mismatch',
            'guest_fingerprint' => 'fp_id_mismatch_' . str_repeat('h', 45),
        ]);

        $result = $this->repository->findByIdentifier('wrong-device', 'tok-id-mismatch', 'fp_id_mismatch_' . str_repeat('h', 45));

        $this->assertNull($result);
    }

    public function test_find_by_identifier_returns_null_when_token_mismatches(): void
    {
        $this->repository->create([
            'device_id' => 'dev-id-tok-mm',
            'token' => 'tok-id-tok-mm',
            'guest_fingerprint' => 'fp_id_tok_mm_' . str_repeat('i', 46),
        ]);

        $result = $this->repository->findByIdentifier('dev-id-tok-mm', 'wrong-token', 'fp_id_tok_mm_' . str_repeat('i', 46));

        $this->assertNull($result);
    }

    public function test_find_by_identifier_returns_null_when_fingerprint_mismatches(): void
    {
        $this->repository->create([
            'device_id' => 'dev-id-fp-mm',
            'token' => 'tok-id-fp-mm',
            'guest_fingerprint' => 'fp_id_fp_mm_' . str_repeat('j', 47),
        ]);

        $result = $this->repository->findByIdentifier('dev-id-fp-mm', 'tok-id-fp-mm', 'wrong-fingerprint');

        $this->assertNull($result);
    }

    // ─── findByIdentifierUnmigrated() ────────────────────────────────────

    public function test_find_by_identifier_unmigrated_returns_device_when_user_id_is_null(): void
    {
        $this->repository->create([
            'device_id' => 'dev-unmigrated',
            'token' => 'tok-unmigrated',
            'guest_fingerprint' => 'fp_unmigrated_' . str_repeat('k', 46),
            // user_id defaults to null
        ]);

        $found = $this->repository->findByIdentifierUnmigrated('dev-unmigrated', 'tok-unmigrated', 'fp_unmigrated_' . str_repeat('k', 46));

        $this->assertNotNull($found);
        $this->assertNull($found->user_id);
    }

    public function test_find_by_identifier_unmigrated_returns_null_when_migrated(): void
    {
        $device = $this->repository->create([
            'device_id' => 'dev-migrated',
            'token' => 'tok-migrated',
            'guest_fingerprint' => 'fp_migrated_' . str_repeat('l', 48),
        ]);

        // Simulate migration by setting user_id to an existing user
        $user = User::factory()->create();
        $device->update(['user_id' => $user->id]);

        $result = $this->repository->findByIdentifierUnmigrated('dev-migrated', 'tok-migrated', 'fp_migrated_' . str_repeat('l', 48));

        $this->assertNull($result);
    }

    // ─── existsByIdentifier() ────────────────────────────────────────────

    public function test_exists_by_identifier_returns_true_when_match_exists(): void
    {
        $this->repository->create([
            'device_id' => 'dev-exists',
            'token' => 'tok-exists',
            'guest_fingerprint' => 'fp_exists_' . str_repeat('m', 49),
        ]);

        $this->assertTrue($this->repository->existsByIdentifier('dev-exists', 'tok-exists'));
    }

    public function test_exists_by_identifier_returns_false_when_device_id_mismatches(): void
    {
        $this->repository->create([
            'device_id' => 'dev-exists-mm',
            'token' => 'tok-exists-mm',
            'guest_fingerprint' => 'fp_exists_mm_' . str_repeat('n', 47),
        ]);

        $this->assertFalse($this->repository->existsByIdentifier('wrong-device', 'tok-exists-mm'));
    }

    public function test_exists_by_identifier_returns_false_when_token_mismatches(): void
    {
        $this->repository->create([
            'device_id' => 'dev-exists-tok',
            'token' => 'tok-exists-tok',
            'guest_fingerprint' => 'fp_exists_tok_' . str_repeat('o', 46),
        ]);

        $this->assertFalse($this->repository->existsByIdentifier('dev-exists-tok', 'wrong-token'));
    }

    public function test_exists_by_identifier_returns_false_when_no_record(): void
    {
        $this->assertFalse($this->repository->existsByIdentifier('nonexistent', 'nonexistent'));
    }

    // ─── findByFingerprintLockForUpdate() ────────────────────────────────

    public function test_find_by_fingerprint_lock_for_update_returns_device(): void
    {
        $this->repository->create([
            'device_id' => 'dev-lock-fp',
            'token' => 'tok-lock-fp',
            'guest_fingerprint' => 'fp_lock_fp_' . str_repeat('p', 49),
        ]);

        $found = $this->repository->findByFingerprintLockForUpdate('fp_lock_fp_' . str_repeat('p', 49));

        $this->assertNotNull($found);
        $this->assertSame('dev-lock-fp', $found->device_id);
    }

    public function test_find_by_fingerprint_lock_for_update_returns_null_when_no_match(): void
    {
        $result = $this->repository->findByFingerprintLockForUpdate('nonexistent');

        $this->assertNull($result);
    }

    // ─── findByDeviceIdLockForUpdate() ───────────────────────────────────

    public function test_find_by_device_id_lock_for_update_returns_device(): void
    {
        $this->repository->create([
            'device_id' => 'dev-lock-did',
            'token' => 'tok-lock-did',
            'guest_fingerprint' => 'fp_lock_did_' . str_repeat('q', 48),
        ]);

        $found = $this->repository->findByDeviceIdLockForUpdate('dev-lock-did');

        $this->assertNotNull($found);
        $this->assertSame('dev-lock-did', $found->device_id);
    }

    public function test_find_by_device_id_lock_for_update_returns_null_when_no_match(): void
    {
        $result = $this->repository->findByDeviceIdLockForUpdate('nonexistent');

        $this->assertNull($result);
    }

    // ─── findByTokenLockForUpdate() ──────────────────────────────────────

    public function test_find_by_token_lock_for_update_returns_device(): void
    {
        $this->repository->create([
            'device_id' => 'dev-lock-tok',
            'token' => 'tok-lock-tok',
            'guest_fingerprint' => 'fp_lock_tok_' . str_repeat('r', 48),
        ]);

        $found = $this->repository->findByTokenLockForUpdate('tok-lock-tok');

        $this->assertNotNull($found);
        $this->assertSame('tok-lock-tok', $found->token);
    }

    public function test_find_by_token_lock_for_update_returns_null_when_no_match(): void
    {
        $result = $this->repository->findByTokenLockForUpdate('nonexistent');

        $this->assertNull($result);
    }

    // ─── Multiple records — ensure queries are scoped ────────────────────

    public function test_methods_are_scoped_correctly_when_multiple_devices_exist(): void
    {
        // Create two devices
        $this->repository->create([
            'device_id' => 'dev-alpha',
            'token' => 'tok-alpha',
            'guest_fingerprint' => 'fp_alpha_' . str_repeat('s', 50),
        ]);
        $this->repository->create([
            'device_id' => 'dev-beta',
            'token' => 'tok-beta',
            'guest_fingerprint' => 'fp_beta_' . str_repeat('t', 51),
        ]);

        // findByDeviceId should return only the matching device
        $alpha = $this->repository->findByDeviceId('dev-alpha');
        $this->assertNotNull($alpha);
        $this->assertSame('tok-alpha', $alpha->token);

        // findByToken should return only the matching device
        $beta = $this->repository->findByToken('tok-beta');
        $this->assertNotNull($beta);
        $this->assertSame('dev-beta', $beta->device_id);

        // existsByIdentifier should only match correct pair
        $this->assertTrue($this->repository->existsByIdentifier('dev-alpha', 'tok-alpha'));
        $this->assertFalse($this->repository->existsByIdentifier('dev-alpha', 'tok-beta'));
    }
}
