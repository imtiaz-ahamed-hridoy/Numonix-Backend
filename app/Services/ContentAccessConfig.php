<?php

namespace App\Services;

use App\Models\EarTrainingLevel;
use App\Models\GuestAccessSetting;
use App\Models\Learning;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Central source-of-truth for content access / gating rules.
 *
 * Three-tier system controlled entirely from the admin dashboard:
 *   - Tier 1 (guest + registered tier_1 users)  - most restricted
 *   - Tier 2 (partial premium)                - middle ground
 *   - Tier 3 (full premium)                   - unlimited / bypass all gates
 *
 * ALL limits are loaded from the guest_access_settings table so admins
 * can change them via the dashboard without code deployment.
 * The hardcoded constants serve as fallback defaults.
 */
class ContentAccessConfig
{
    /* ------------------------------------------------------------------
     * Per-request in-memory cache (avoids repeated DB/Redis lookups)
     * ----------------------------------------------------------------*/
    private static ?array $tier1LimitsCache = null;

    private static ?array $tier2LimitsCache = null;

    private static ?array $tier3LimitsCache = null;

    private static ?int $tier1EarTrainingCache = null;

    private static ?int $tier1EarTrainingIntermediateCache = null;

    private static ?int $tier1EarTrainingAdvancedCache = null;

    private static ?int $tier2EarTrainingCache = null;

    private static ?int $tier2EarTrainingIntermediateCache = null;

    private static ?int $tier2EarTrainingAdvancedCache = null;

    private static ?int $tier3EarTrainingCache = null;

    private static ?int $tier3EarTrainingIntermediateCache = null;

    private static ?int $tier3EarTrainingAdvancedCache = null;

    private static ?array $tier1HiddenCategoriesCache = null;

    private static ?array $tier2HiddenCategoriesCache = null;

    private static ?array $tier3HiddenCategoriesCache = null;

    /**
     * Categories where access is controlled via group-level is_premium
     * (from learning_groups table) instead of per-item position limits.
     */
    private const GROUP_GATED_CATEGORIES = ['case_study', 'fnh_architecture'];

    private const DEFAULT_TIER1_LIMITS = [
        'lesson' => 6,
        'core_theory' => 6,
        'case_study' => 6,
        'fnh_architecture' => 6,
        'advanced_insight' => 6,
    ];

    private const DEFAULT_TIER2_LIMITS = [
        'lesson' => 12,
        'core_theory' => 99,
        'case_study' => 99,
        'fnh_architecture' => 99,
        'advanced_insight' => 6,
    ];

    private const DEFAULT_TIER3_LIMITS = [
        'lesson' => 99,
        'core_theory' => 99,
        'case_study' => 99,
        'fnh_architecture' => 99,
        'advanced_insight' => 99,
    ];

    private const DEFAULT_TIER1_EAR_TRAINING_BEGINNER_LEVELS = 99;

    private const DEFAULT_TIER1_EAR_TRAINING_INTERMEDIATE_LEVELS = 0;

    private const DEFAULT_TIER1_EAR_TRAINING_ADVANCED_LEVELS = 0;

    private const DEFAULT_TIER1_HIDDEN_CATEGORIES = [];

    private const DEFAULT_TIER2_HIDDEN_CATEGORIES = [];

    private const DEFAULT_TIER3_HIDDEN_CATEGORIES = [];

    /* ------------------------------------------------------------------
     * Dynamic getters (DB-backed with fallback)
     * ----------------------------------------------------------------*/

    /**
     * Get the tier-1 (tier_1/guest) item limits from DB settings.
     */
    public static function getTier1Limits(): array
    {
        if (self::$tier1LimitsCache !== null) {
            return self::$tier1LimitsCache;
        }

        return self::$tier1LimitsCache = [
            'lesson' => GuestAccessSetting::getInt('tier1_limit_lesson', self::DEFAULT_TIER1_LIMITS['lesson']),
            'core_theory' => GuestAccessSetting::getInt('tier1_limit_core_theory', self::DEFAULT_TIER1_LIMITS['core_theory']),
            'case_study' => GuestAccessSetting::getInt('tier1_limit_case_study', self::DEFAULT_TIER1_LIMITS['case_study']),
            'fnh_architecture' => GuestAccessSetting::getInt('tier1_limit_fnh_architecture', self::DEFAULT_TIER1_LIMITS['fnh_architecture']),
            'advanced_insight' => GuestAccessSetting::getInt('tier1_limit_advanced_insight', self::DEFAULT_TIER1_LIMITS['advanced_insight']),
        ];
    }

    /**
     * Get the tier-2 (partial premium) item limits from DB settings.
     */
    public static function getTier2Limits(): array
    {
        if (self::$tier2LimitsCache !== null) {
            return self::$tier2LimitsCache;
        }

        return self::$tier2LimitsCache = [
            'lesson' => GuestAccessSetting::getInt('tier2_limit_lesson', self::DEFAULT_TIER2_LIMITS['lesson']),
            'core_theory' => GuestAccessSetting::getInt('tier2_limit_core_theory', self::DEFAULT_TIER2_LIMITS['core_theory']),
            'case_study' => GuestAccessSetting::getInt('tier2_limit_case_study', self::DEFAULT_TIER2_LIMITS['case_study']),
            'fnh_architecture' => GuestAccessSetting::getInt('tier2_limit_fnh_architecture', self::DEFAULT_TIER2_LIMITS['fnh_architecture']),
            'advanced_insight' => GuestAccessSetting::getInt('tier2_limit_advanced_insight', self::DEFAULT_TIER2_LIMITS['advanced_insight']),
        ];
    }

    /**
     * Get the tier-3 (full premium) item limits from DB settings.
     */
    public static function getTier3Limits(): array
    {
        if (self::$tier3LimitsCache !== null) {
            return self::$tier3LimitsCache;
        }

        return self::$tier3LimitsCache = [
            'lesson' => GuestAccessSetting::getInt('tier3_limit_lesson', self::DEFAULT_TIER3_LIMITS['lesson']),
            'core_theory' => GuestAccessSetting::getInt('tier3_limit_core_theory', self::DEFAULT_TIER3_LIMITS['core_theory']),
            'case_study' => GuestAccessSetting::getInt('tier3_limit_case_study', self::DEFAULT_TIER3_LIMITS['case_study']),
            'fnh_architecture' => GuestAccessSetting::getInt('tier3_limit_fnh_architecture', self::DEFAULT_TIER3_LIMITS['fnh_architecture']),
            'advanced_insight' => GuestAccessSetting::getInt('tier3_limit_advanced_insight', self::DEFAULT_TIER3_LIMITS['advanced_insight']),
        ];
    }

    // -- Ear Training Toggles ------------------------------------------------

    /**
     * Get whether tier_1 has access to beginner ear training.
     */
    public static function hasTier1EarTrainingBeginnerAccess(): bool
    {
        if (self::$tier1EarTrainingCache !== null) {
            return (bool) self::$tier1EarTrainingCache;
        }

        return (bool) (self::$tier1EarTrainingCache = GuestAccessSetting::getInt(
            'tier1_ear_training_beginner_levels',
            self::DEFAULT_TIER1_EAR_TRAINING_BEGINNER_LEVELS
        ));
    }

    /**
     * Get whether tier_1 has access to intermediate ear training.
     */
    public static function hasTier1EarTrainingIntermediateAccess(): bool
    {
        if (self::$tier1EarTrainingIntermediateCache !== null) {
            return (bool) self::$tier1EarTrainingIntermediateCache;
        }

        return (bool) (self::$tier1EarTrainingIntermediateCache = GuestAccessSetting::getInt(
            'tier1_ear_training_intermediate_levels',
            self::DEFAULT_TIER1_EAR_TRAINING_INTERMEDIATE_LEVELS
        ));
    }

    /**
     * Get whether tier_1 has access to advanced ear training.
     */
    public static function hasTier1EarTrainingAdvancedAccess(): bool
    {
        if (self::$tier1EarTrainingAdvancedCache !== null) {
            return (bool) self::$tier1EarTrainingAdvancedCache;
        }

        return (bool) (self::$tier1EarTrainingAdvancedCache = GuestAccessSetting::getInt(
            'tier1_ear_training_advanced_levels',
            self::DEFAULT_TIER1_EAR_TRAINING_ADVANCED_LEVELS
        ));
    }

    /**
     * Get whether tier-2 has access to beginner ear training.
     */
    public static function hasTier2EarTrainingBeginnerAccess(): bool
    {
        if (self::$tier2EarTrainingCache !== null) {
            return (bool) self::$tier2EarTrainingCache;
        }

        return (bool) (self::$tier2EarTrainingCache = GuestAccessSetting::getInt(
            'tier2_ear_training_beginner_levels',
            1
        ));
    }

    /**
     * Get whether tier-2 has access to intermediate ear training.
     */
    public static function hasTier2EarTrainingIntermediateAccess(): bool
    {
        if (self::$tier2EarTrainingIntermediateCache !== null) {
            return (bool) self::$tier2EarTrainingIntermediateCache;
        }

        return (bool) (self::$tier2EarTrainingIntermediateCache = GuestAccessSetting::getInt(
            'tier2_ear_training_intermediate_levels',
            1
        ));
    }

    /**
     * Get whether tier-2 has access to advanced ear training.
     */
    public static function hasTier2EarTrainingAdvancedAccess(): bool
    {
        if (self::$tier2EarTrainingAdvancedCache !== null) {
            return (bool) self::$tier2EarTrainingAdvancedCache;
        }

        return (bool) (self::$tier2EarTrainingAdvancedCache = GuestAccessSetting::getInt(
            'tier2_ear_training_advanced_levels',
            1
        ));
    }

    /**
     * Get the list of categories hidden from tier-1 (tier_1/guest) users.
     */
    public static function getTier1HiddenCategories(): array
    {
        if (self::$tier1HiddenCategoriesCache !== null) {
            return self::$tier1HiddenCategoriesCache;
        }

        return self::$tier1HiddenCategoriesCache = GuestAccessSetting::getArray(
            'hidden_categories',
            self::DEFAULT_TIER1_HIDDEN_CATEGORIES
        );
    }

    /**
     * Get the list of categories hidden from tier-2 users.
     */
    public static function getTier2HiddenCategories(): array
    {
        if (self::$tier2HiddenCategoriesCache !== null) {
            return self::$tier2HiddenCategoriesCache;
        }

        return self::$tier2HiddenCategoriesCache = GuestAccessSetting::getArray(
            'tier2_hidden_categories',
            self::DEFAULT_TIER2_HIDDEN_CATEGORIES
        );
    }

    // -- TIER 3 Ear Training ------------------------------------------------

    /**
     * Get whether tier-3 has access to beginner ear training.
     */
    public static function hasTier3EarTrainingBeginnerAccess(): bool
    {
        if (self::$tier3EarTrainingCache !== null) {
            return (bool) self::$tier3EarTrainingCache;
        }

        return (bool) (self::$tier3EarTrainingCache = GuestAccessSetting::getInt(
            'tier3_ear_training_beginner_levels',
            1
        ));
    }

    /**
     * Get whether tier-3 has access to intermediate ear training.
     */
    public static function hasTier3EarTrainingIntermediateAccess(): bool
    {
        if (self::$tier3EarTrainingIntermediateCache !== null) {
            return (bool) self::$tier3EarTrainingIntermediateCache;
        }

        return (bool) (self::$tier3EarTrainingIntermediateCache = GuestAccessSetting::getInt(
            'tier3_ear_training_intermediate_levels',
            1
        ));
    }

    /**
     * Get whether tier-3 has access to advanced ear training.
     */
    public static function hasTier3EarTrainingAdvancedAccess(): bool
    {
        if (self::$tier3EarTrainingAdvancedCache !== null) {
            return (bool) self::$tier3EarTrainingAdvancedCache;
        }

        return (bool) (self::$tier3EarTrainingAdvancedCache = GuestAccessSetting::getInt(
            'tier3_ear_training_advanced_levels',
            1
        ));
    }

    /**
     * Get the list of categories hidden from tier-3 users.
     */
    public static function getTier3HiddenCategories(): array
    {
        if (self::$tier3HiddenCategoriesCache !== null) {
            return self::$tier3HiddenCategoriesCache;
        }

        return self::$tier3HiddenCategoriesCache = GuestAccessSetting::getArray(
            'tier3_hidden_categories',
            self::DEFAULT_TIER3_HIDDEN_CATEGORIES
        );
    }

    /* ------------------------------------------------------------------
     * Resolve user tier
     * ----------------------------------------------------------------*/

    /**
     * Resolve the effective subscription tier for a user (or guest).
     *
     * Returns 'tier_1' for both guests and tier_1 users.
     * Only 3 tiers exist: tier_1, tier_2, tier_3.
     */
    public static function resolveUserTier(?User $user): string
    {
        if (! $user) {
            return 'tier_1';
        }

        return $user->getSubscriptionTier();
    }

    /* ------------------------------------------------------------------
     * Which tier-level getters to use
     * ----------------------------------------------------------------*/

    /**
     * Get the appropriate limits for a given user tier.
     */
    private static function getLimitsForTier(string $tier): array
    {
        return match ($tier) {
            'tier_2' => self::getTier2Limits(),
            'tier_3' => self::getTier3Limits(),
            default => self::getTier1Limits(), // tier_1, guest
        };
    }

    /**
     * Get the appropriate ear training access toggles for a given user tier.
     */
    private static function getEarTrainingForTier(string $tier): array
    {
        return match ($tier) {
            'tier_2' => [
                'beginner' => self::hasTier2EarTrainingBeginnerAccess(),
                'intermediate' => self::hasTier2EarTrainingIntermediateAccess(),
                'advanced' => self::hasTier2EarTrainingAdvancedAccess(),
            ],
            'tier_3' => [
                'beginner' => self::hasTier3EarTrainingBeginnerAccess(),
                'intermediate' => self::hasTier3EarTrainingIntermediateAccess(),
                'advanced' => self::hasTier3EarTrainingAdvancedAccess(),
            ],
            default => [
                'beginner' => self::hasTier1EarTrainingBeginnerAccess(),
                'intermediate' => self::hasTier1EarTrainingIntermediateAccess(),
                'advanced' => self::hasTier1EarTrainingAdvancedAccess(),
            ],
        };
    }

    /**
     * Get the appropriate hidden categories for a given user tier.
     */
    public static function getHiddenForTier(string $tier): array
    {
        return match ($tier) {
            'tier_2' => self::getTier2HiddenCategories(),
            'tier_3' => self::getTier3HiddenCategories(),
            default => self::getTier1HiddenCategories(),
        };
    }

    /* ------------------------------------------------------------------
     * Learning access
     * ----------------------------------------------------------------*/

    /**
     * Determine whether a Learning is accessible to a given user.
     *
     * @param  Learning  $learning  The learning item
     * @param  User|null  $user  Authenticated user, or null for guest
     * @param  int  $positionInCategory  1-based position within its category
     * @return array{is_locked: bool, lock_reason: string|null, is_guest_accessible: bool, is_hidden: bool}
     */
    public static function isLearningAccessible(
        Learning $learning,
        ?User $user,
        int $positionInCategory
    ): array {
        $tier = self::resolveUserTier($user);

        // --- Step 0: Admin explicit guest override ---------------------------
        // Only applies to guests (consistent with the "Guest Access Override" label
        // in the admin dashboard). Authenticated users fall through to the
        // is_premium / category-rule checks below.
        //   is_guest_accessible = null  -> use category rules (below)
        //   is_guest_accessible = true  -> explicitly accessible for guests
        //   is_guest_accessible = false -> explicitly locked for guests
        if (! $user) {
            $rawGuest = $learning->getRawOriginal('is_guest_accessible');
            if ($rawGuest !== null) {
                if ((bool) $rawGuest === true) {
                    return [
                        'is_locked' => false,
                        'lock_reason' => null,
                        'is_guest_accessible' => true,
                        'is_hidden' => false,
                    ];
                }

                return [
                    'is_locked' => true,
                    'lock_reason' => 'subscription_required',
                    'is_guest_accessible' => false,
                    'is_hidden' => false,
                ];
            }
        }

        // --- Step 1: Admin individual override (is_premium) -----------
        $rawPremium = $learning->getRawOriginal('is_premium');

        if ($rawPremium === true || $rawPremium === 1 || $rawPremium === '1') {
            // Tier 2+ bypass premium lock
            if ($user && $user->hasTier('tier_2')) {
                return [
                    'is_locked' => false,
                    'lock_reason' => null,
                    'is_guest_accessible' => false,
                    'is_hidden' => false,
                ];
            }

            return [
                'is_locked' => true,
                'lock_reason' => 'subscription_required',
                'is_guest_accessible' => false,
                'is_hidden' => false,
            ];
        }

        // --- is_premium = false directly forces unlock ----------------
        if ($rawPremium === false || $rawPremium === 0 || $rawPremium === '0') {
            return [
                'is_locked' => false,
                'lock_reason' => null,
                'is_guest_accessible' => true,
                'is_hidden' => false,
            ];
        }

        // --- is_premium is null - use category-level rules ------------
        $category = $learning->category;
        $limits = self::getLimitsForTier($tier);
        $hiddenCategories = self::getHiddenForTier($tier);
        $isHidden = in_array($category, $hiddenCategories, true);

        // Group-gated categories
        if (in_array($category, self::GROUP_GATED_CATEGORIES, true)) {
            $group = $learning->group;
            if (! $group) {
                return [
                    'is_locked' => false,
                    'lock_reason' => null,
                    'is_guest_accessible' => true,
                    'is_hidden' => false,
                ];
            }

            $isLockedForTier = false;

            if ($tier === 'tier_3') {
                $isLockedForTier = $group->tier3_premium;
            } elseif ($tier === 'tier_2') {
                $isLockedForTier = $group->tier2_premium;
            } else {
                $isLockedForTier = $group->is_premium; // Tier 1 / Guest
            }

            if ($isLockedForTier) {
                return [
                    'is_locked' => true,
                    'lock_reason' => 'subscription_required',
                    'is_guest_accessible' => false,
                    'is_hidden' => false,
                ];
            }

            return [
                'is_locked' => false,
                'lock_reason' => null,
                'is_guest_accessible' => true,
                'is_hidden' => false,
            ];
        }

        $limit = $limits[$category] ?? 0;

        // Fully locked categories (limit = 0)
        if ($limit === 0) {
            return [
                'is_locked' => true,
                'lock_reason' => 'subscription_required',
                'is_guest_accessible' => false,
                'is_hidden' => $isHidden,
            ];
        }

        // Within limit -> accessible
        if ($positionInCategory <= $limit) {
            return [
                'is_locked' => false,
                'lock_reason' => null,
                'is_guest_accessible' => true,
                'is_hidden' => false,
            ];
        }

        // Beyond limit -> locked
        return [
            'is_locked' => true,
            'lock_reason' => 'tier_limit_exceeded',
            'is_guest_accessible' => false,
            'is_hidden' => $isHidden,
        ];
    }

    /* ------------------------------------------------------------------
     * Ear Training access
     * ----------------------------------------------------------------*/

    /**
     * Determine whether an Ear Training Level is accessible.
     *
     * @param  EarTrainingLevel  $level  The level object
     * @param  User|null  $user  Authenticated user, or null for guest
     * @return array{is_locked: bool, lock_reason: string|null, is_guest_accessible: bool, hide_sublisting: bool}
     */
    public static function isEarTrainingLevelAccessible(
        EarTrainingLevel $level,
        ?User $user
    ): array {
        $tier = self::resolveUserTier($user);
        
        // Ensure stage is loaded
        $stage = $level->stage;
        $difficulty = $stage ? strtolower($stage->title ?? '') : '';
        // Map 'advance' to 'advanced' if necessary, since our arrays use 'advanced'
        if ($difficulty === 'advance') $difficulty = 'advanced';

        // --- Step 1: Admin explicit guest override --------------------------
        // If the user is a guest, check if guest_access_override is explicitly set.
        if (! $user) {
            $rawGuestLevel = $level->guest_access_override;
            $rawGuestStage = $stage ? $stage->guest_access_override : 'Use Level Rule';
            
            // Check Level-specific override first
            if ($rawGuestLevel === 'Unlocked') {
                return [
                    'is_locked' => false,
                    'lock_reason' => null,
                    'is_guest_accessible' => true,
                    'hide_sublisting' => false,
                ];
            } elseif ($rawGuestLevel === 'Locked') {
                return [
                    'is_locked' => true,
                    'lock_reason' => 'subscription_required',
                    'is_guest_accessible' => false,
                    'hide_sublisting' => true,
                ];
            }
            
            // Fallback to Stage override if Level is 'Use Level Rule'
            if ($rawGuestStage === 'Unlocked') {
                return [
                    'is_locked' => false,
                    'lock_reason' => null,
                    'is_guest_accessible' => true,
                    'hide_sublisting' => false,
                ];
            } elseif ($rawGuestStage === 'Locked') {
                return [
                    'is_locked' => true,
                    'lock_reason' => 'subscription_required',
                    'is_guest_accessible' => false,
                    'hide_sublisting' => true,
                ];
            }
        }

        // --- Step 2: Admin individual override (is_premium on the level/stage) ----
        // This has HIGH priority - overrides tier toggles.
        $levelPremium = $level->is_premium;
        $stagePremium = $stage ? $stage->is_premium : false;

        if ($levelPremium || $stagePremium) {
            // Admin explicitly locked this level or stage
            if ($user && $user->hasTier('tier_2')) {
                return [
                    'is_locked' => false,
                    'lock_reason' => null,
                    'is_guest_accessible' => false,
                    'hide_sublisting' => false,
                ];
            }

            return [
                'is_locked' => true,
                'lock_reason' => 'subscription_required',
                'is_guest_accessible' => false,
                'hide_sublisting' => true,
            ];
        }

        // --- Step 3: Dashboard tier toggle --------------------------------------
        // The tier toggle value = "does this tier have access to this stage?".
        $earTrainingLimits = self::getEarTrainingForTier($tier);
        
        $isAccessible = $earTrainingLimits[$difficulty] ?? false;

        if (! $isAccessible) {
            return [
                'is_locked' => true,
                'lock_reason' => 'subscription_required',
                'is_guest_accessible' => false,
                'hide_sublisting' => ! in_array($difficulty, ['beginner'], true),
            ];
        }

        // Tier has access to this stage -> accessible
        return [
            'is_locked' => false,
            'lock_reason' => null,
            'is_guest_accessible' => true,
            'hide_sublisting' => false,
        ];
    }

    /**
     * Determine whether an ear training difficulty tier is marked as premium
     * for a given user tier.
     *
     * @param  string  $difficulty  'beginner', 'intermediate', or 'advanced'
     * @param  string  $userTier  'tier_1', 'tier_2', 'tier_3' (default: 'tier_1')
     */
    public static function isEarTrainingLevelPremium(string $difficulty, string $userTier = 'tier_1'): bool
    {
        if ($difficulty === 'advance') $difficulty = 'advanced';

        $isAccessible = match ($userTier) {
            'tier_2' => match (strtolower($difficulty)) {
                'beginner' => self::hasTier2EarTrainingBeginnerAccess(),
                'intermediate' => self::hasTier2EarTrainingIntermediateAccess(),
                'advanced' => self::hasTier2EarTrainingAdvancedAccess(),
                default => false,
            },
            'tier_3' => match (strtolower($difficulty)) {
                'beginner' => self::hasTier3EarTrainingBeginnerAccess(),
                'intermediate' => self::hasTier3EarTrainingIntermediateAccess(),
                'advanced' => self::hasTier3EarTrainingAdvancedAccess(),
                default => false,
            },
            default => match (strtolower($difficulty)) {
                'beginner' => self::hasTier1EarTrainingBeginnerAccess(),
                'intermediate' => self::hasTier1EarTrainingIntermediateAccess(),
                'advanced' => self::hasTier1EarTrainingAdvancedAccess(),
                default => false,
            },
        };

        return !$isAccessible;
    }

    /* ------------------------------------------------------------------
     * Home Module access
     * ----------------------------------------------------------------*/

    /**
     * Determine whether a HomeModule is accessible to a given user.
     *
     * HomeModule premium status is simpler than learning/ear-training:
     * it's a direct on/off toggle on the module record itself, not
     * position-based or tier-limits-based.
     *
     * @param  bool  $moduleIsPremium  The HomeModule->is_premium value
     * @return array{is_locked: bool, lock_reason: string|null}
     */
    public static function isHomeModuleAccessible(bool $moduleIsPremium, ?User $user): array
    {
        if (! $moduleIsPremium) {
            return [
                'is_locked' => false,
                'lock_reason' => null,
            ];
        }

        // Module is premium - tier_2+ users bypass, tier_1/guest are locked
        if ($user && $user->hasTier('tier_2')) {
            return [
                'is_locked' => false,
                'lock_reason' => null,
            ];
        }

        return [
            'is_locked' => true,
            'lock_reason' => 'subscription_required',
        ];
    }

    /**
     * Clear the per-request in-memory cache and also flush
     * the underlying GuestAccessSetting cache entries.
     * Useful in tests to reset state between test cases.
     */
    public static function clearCache(): void
    {
        self::$tier1LimitsCache = null;
        self::$tier2LimitsCache = null;
        self::$tier3LimitsCache = null;
        self::$tier1EarTrainingCache = null;
        self::$tier1EarTrainingIntermediateCache = null;
        self::$tier1EarTrainingAdvancedCache = null;
        self::$tier2EarTrainingCache = null;
        self::$tier2EarTrainingIntermediateCache = null;
        self::$tier2EarTrainingAdvancedCache = null;
        self::$tier3EarTrainingCache = null;
        self::$tier3EarTrainingIntermediateCache = null;
        self::$tier3EarTrainingAdvancedCache = null;
        self::$tier1HiddenCategoriesCache = null;
        self::$tier2HiddenCategoriesCache = null;
        self::$tier3HiddenCategoriesCache = null;

        // Also flush the underlying GuestAccessSetting Laravel cache entries
        // so tests with RefreshDatabase get a clean state.
        $knownKeys = [
            'tier1_limit_lesson',
            'tier1_limit_core_theory',
            'tier1_limit_case_study',
            'tier1_limit_fnh_architecture',
            'tier1_limit_advanced_insight',
            'tier2_limit_lesson',
            'tier2_limit_core_theory',
            'tier2_limit_case_study',
            'tier2_limit_fnh_architecture',
            'tier2_limit_advanced_insight',
            'tier3_limit_lesson',
            'tier3_limit_core_theory',
            'tier3_limit_case_study',
            'tier3_limit_fnh_architecture',
            'tier3_limit_advanced_insight',
            'tier1_ear_training_beginner_levels',
            'tier1_ear_training_intermediate_levels',
            'tier1_ear_training_advanced_levels',
            'tier2_ear_training_beginner_levels',
            'tier2_ear_training_intermediate_levels',
            'tier2_ear_training_advanced_levels',
            'tier3_ear_training_beginner_levels',
            'tier3_ear_training_intermediate_levels',
            'tier3_ear_training_advanced_levels',
            'hidden_categories',
            'tier2_hidden_categories',
            'tier3_hidden_categories',
        ];

        foreach ($knownKeys as $key) {
            Cache::forget("guest_access_setting:{$key}");
        }
    }

    // All deprecated getFree* methods have been removed.
    // Use getTier1*() methods instead.
}
