<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tracks guest devices identified by device_id + token from Flutter.
 *
 * Each physical device has exactly ONE GuestDevice record with:
 *   - one guest_device_id (primary key)
 *   - one device_id (unique)
 *   - one token (unique)
 *   - one guest_fingerprint (unique, HMAC-SHA256 of device_id|token with APP_KEY)
 *
 * Once registered, these values NEVER change. If the same physical device
 * later sends different credentials, the API returns an error instead of
 * updating or creating a new record.
 *
 * When a guest registers and provides device_id + token, the GuestDevice
 * record is used to find all associated guest data, sync it to the user,
 * and then the record and related data are cleaned up.
 *
 * IMPORTANT: cleanupGuestData() must NOT delete attempt records
 * (EarTrainingQuizAttempt, QuizAttempt) because they have already been
 * reassigned to the user by GuestDeviceSyncService before cleanup runs.
 *
 * @property int         $id
 * @property string      $device_id
 * @property string      $token
 * @property string      $guest_fingerprint  HMAC-SHA256(device_id|token, APP_KEY)
 * @property int|null    $user_id
 * @property string|null $last_activity_at
 */
class GuestDevice extends Model
{
    protected $fillable = [
        'device_id',
        'token',
        'guest_fingerprint',
        'user_id',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'user_id'          => 'integer',
    ];

    /* ------------------------------------------------------------------
     * Relationships
     * ----------------------------------------------------------------*/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(GuestActivityLog::class, 'guest_device_id');
    }

    public function earTrainingUserProgress(): HasMany
    {
        return $this->hasMany(EarTrainingUserProgress::class, 'guest_device_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'guest_device_id');
    }

    public function learningUserProgress(): HasMany
    {
        return $this->hasMany(LearningUserProgress::class, 'guest_device_id');
    }

    public function learningChecklistProgress(): HasMany
    {
        return $this->hasMany(LearningChecklistProgress::class, 'guest_device_id');
    }

    /* ------------------------------------------------------------------
     * Scopes
     * ----------------------------------------------------------------*/

    /**
     * Find a guest device by device_id AND token.
     * Both must match for strict security.
     */
    public function scopeFindByIdentifier($query, string $deviceId, string $token)
    {
        return $query->where('device_id', $deviceId)
            ->where('token', $token);
    }

    /**
     * Find a guest device by its HMAC fingerprint.
     */
    public function scopeByFingerprint($query, string $fingerprint)
    {
        return $query->where('guest_fingerprint', $fingerprint);
    }

    /**
     * Unmigrated devices (not yet linked to a user).
     */
    public function scopeUnmigrated($query)
    {
        return $query->whereNull('user_id');
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------------*/

    /**
     * Touch the last_activity_at timestamp.
     */
    public function markActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Migrate this device to a user. Links all associated data.
     */
    public function migrateToUser(User $user): void
    {
        $this->update(['user_id' => $user->id]);
    }

    /**
     * Check if this device has been migrated.
     */
    public function isMigrated(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Clean up guest tracking data after successful migration.
     *
     * Before deleting the device, clears guest_device_id on all child
     * records (EarTrainingQuizAttempt, QuizAttempt, etc.) so the
     * cascadeOnDelete foreign key constraint does not delete data that
     * was already reassigned to the registered user.
     *
     * Deletes only:
     *   - Activity logs (analytics only, safe to remove)
     *   - The device record itself
     *
     * Does NOT delete EarTrainingQuizAttempt or QuizAttempt records
     * because those were already reassigned to the user by
     * GuestDeviceSyncService::sync() before this is called.
     */
    public function cleanupGuestData(): void
    {
        // Clear foreign key references on child records so cascade does
        // not delete data already reassigned to the registered user.
        $this->earTrainingUserProgress()->where('guest_device_id', $this->id)->update(['guest_device_id' => null]);
        $this->quizAttempts()->where('guest_device_id', $this->id)->update(['guest_device_id' => null]);
        $this->learningUserProgress()->where('guest_device_id', $this->id)->update(['guest_device_id' => null]);
        $this->learningChecklistProgress()->where('guest_device_id', $this->id)->update(['guest_device_id' => null]);

        // Delete activity logs for this device (analytics only, no user data impact)
        $this->activityLogs()->delete();

        // Delete the device record itself
        $this->delete();
    }
}
