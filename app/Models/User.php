<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Paddle\Billable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, LogsActivity, Billable;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

        protected $fillable = [
        'name',
        'email',
        'phone',
        'dob',
        'avatar',
        'password',
        'socialite_id',
        'socialite_provider',
        'socialite_token',
        'socialite_refresh_token',
        'socialite_expires_at',
        'is_age_verified',
        'is_online',
        'is_agree',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_code',
        'reset_code_expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_online'             => 'boolean',
        'is_agree'              => 'boolean',
        'is_subscribed'         => 'boolean',
        'dob'                   => 'date',
        'next_billing_date'     => 'datetime',
        'email_verified_at'     => 'datetime',
        'reset_code_expires_at' => 'datetime',
        'password'              => 'hashed',
        'trial_ends_at'           => 'datetime',
        'is_founder'              => 'boolean',
        'founder_trial_ends_at'   => 'datetime',
        'founder_review_completed'=> 'boolean',
        'founder_day25_sent_at'   => 'datetime',
        'founder_day30_sent_at'   => 'datetime',
        'founder_converted_at'    => 'datetime',
        'imported_at'             => 'datetime',
        'activated_at'            => 'datetime',
    ];

    // Avatar URL accessor: builds a full publicly accessible URL.
    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        // Social login avatars or any external URL - return as-is
        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        // Stored as relative path e.g. "uploads/avatar/xxx.png"
        // asset() -> APP_URL + '/' + path
        // -> https://api.example.com/uploads/avatar/xxx.png  ✓ (no symlink needed)
        $path = ltrim($this->avatar, '/');

        // Legacy: old Storage::disk records look like "uploads/avatar/xxx.png"
        // but were served via /storage/uploads/avatar/xxx.png.
        // Strip 'storage/' prefix so old DB records still resolve correctly.
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return asset($path);
    }


    /* ------------------------------------------------------------------
     * Subscription helpers
     * ----------------------------------------------------------------*/

    /**
     * Check if the user has any active (paid) subscription.
     * Only tier_2+ are considered subscribed. tier_1 is treated as tier_1/guest.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->isFounderTrialActive()) {
            return true;
        }

        if ($this->is_founder && $this->founder_converted_at !== null) {
            return true;
        }

        if (method_exists($this, 'subscribed') && $this->subscribed()) {
            return true;
        }

        return $this->hasTier('tier_2');
    }

    /**
     * Return the resolved subscription tier.
     *
     * Only 3 tiers exist: tier_1 (guest + tier_1), tier_2, tier_3.
     * Normalizes any null value to 'tier_1'.
     */
    public function getSubscriptionTier(): string
    {
        if ($this->isFounderTrialActive()) {
            return 'tier_3';
        }

        if ($this->is_founder && $this->founder_converted_at !== null) {
            return 'tier_3';
        }

        if (method_exists($this, 'subscribed') && $this->subscribed()) {
            return $this->subscription_tier ?: 'tier_2';
        }

        // Expired unconverted founder drops to tier_1 (spec 3.15)
        if ($this->is_founder && ($this->founder_trial_ends_at === null || $this->founder_trial_ends_at->isPast())) {
            return 'tier_1';
        }

        if (!empty($this->subscription_tier)) {
            return $this->subscription_tier;
        }

        return 'tier_1';
    }

    /**
     * Helper to check if founder is currently in active 30-day trial.
     */
    public function isFounderTrialActive(): bool
    {
        return $this->is_founder
            && $this->founder_trial_ends_at !== null
            && $this->founder_trial_ends_at->isFuture();
    }

    /**
     * Check if the user's subscription is at least the given tier.
     *
     * Only valid tiers: tier_1, tier_2, tier_3.
     */
    public function hasTier(string $requiredTier): bool
    {
        $hierarchy = ['tier_1' => 0, 'tier_2' => 1, 'tier_3' => 2];

        if (!isset($hierarchy[$requiredTier])) {
            return false;
        }

        $current = $hierarchy[$this->getSubscriptionTier()] ?? 0;
        $required = $hierarchy[$requiredTier];

        return $current >= $required;
    }

    /**
     * Get the customer-facing tier name.
     * Mappings:
     * tier_1 -> Foundation (Free)
     * tier_2 -> Tier 1
     * tier_3 -> Tier 2
     */
    public function getTierDisplayName(): string
    {
        if ($this->plan_name && !in_array(strtolower($this->plan_name), ['free', 'tier 1', 'tier 2', 'tier 3', 'tier_1', 'tier_2', 'tier_3'])) {
            return $this->plan_name;
        }

        $tier = $this->getSubscriptionTier();
        return match ($tier) {
            'tier_3' => 'Tier 2',
            'tier_2' => 'Tier 1',
            default  => 'Foundation (Free)',
        };
    }

    /* ------------------------------------------------------------------
     * Relationships
     * ----------------------------------------------------------------*/

    public function notificationSettings()
    {
        return $this->hasOne(UserNotificationSetting::class);
    }

    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseToken::class);
    }

    public function cookieSetting()
    {
        return $this->hasOne(UserCookieSetting::class);
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function learningProgress()
    {
        return $this->hasMany(LearningUserProgress::class);
    }

    public function founderReview()
    {
        return $this->hasOne(FounderReviewSubmission::class);
    }
}
