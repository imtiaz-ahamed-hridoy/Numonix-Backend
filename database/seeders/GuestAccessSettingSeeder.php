<?php

namespace Database\Seeders;

use App\Models\GuestAccessSetting;
use Illuminate\Database\Seeder;

/**
 * Seed the guest_access_settings table with default values.
 *
 * These match the hardcoded defaults in ContentAccessConfig and ensure
 * that after migrate:fresh --seed the admin dashboard shows sensible
 * starting values for all tier limits.
 *
 * Three-tier system:
 *   Tier 1 - most restricted
 *   Tier 2 (partial premium) - middle ground
 *   Tier 3 (full premium) - bypass all limits
 */
class GuestAccessSettingSeeder extends Seeder
{
    private const DEFAULTS = [
        // -- Tier 1 learning limits -----------------------------------
        // How many items in each category a tier-1 user can access
        // (0 = none, high number = all)
        // These match ContentAccessConfig::DEFAULT_TIER1_LIMITS
        'tier1_limit_lesson'              => '6',
        'tier1_limit_core_theory'         => '6',
        'tier1_limit_case_study'          => '6',
        'tier1_limit_fnh_architecture'    => '6',
        'tier1_limit_advanced_insight'    => '6',

        // -- Tier 2 (partial premium) learning limits -----------------
        'tier2_limit_lesson'             => '12',
        'tier2_limit_core_theory'        => '99',
        'tier2_limit_case_study'         => '0',
        'tier2_limit_fnh_architecture'   => '99',
        'tier2_limit_advanced_insight'   => '0',

        // -- Tier 3 (full premium) learning limits --------------------
        'tier3_limit_lesson'             => '99',
        'tier3_limit_core_theory'        => '99',
        'tier3_limit_case_study'         => '99',
        'tier3_limit_fnh_architecture'   => '99',
        'tier3_limit_advanced_insight'   => '99',

        // -- Tier 1 ear training toggles ------------------------------
        // Allow only beginner ear training levels for tier 1 users (1 = yes)
        'tier1_ear_training_beginner_levels'     => '1',
        'tier1_ear_training_intermediate_levels' => '0',
        'tier1_ear_training_advanced_levels'     => '0',

        // -- Tier 2 ear training toggles ------------------------------
        'tier2_ear_training_beginner_levels'     => '1',
        'tier2_ear_training_intermediate_levels' => '1',
        'tier2_ear_training_advanced_levels'     => '1',

        // -- Tier 3 ear training toggles ------------------------------
        'tier3_ear_training_beginner_levels'     => '1',
        'tier3_ear_training_intermediate_levels' => '1',
        'tier3_ear_training_advanced_levels'     => '1',

        // -- Hidden categories ----------------------------------------
        'hidden_categories'              => '[]',
        'tier2_hidden_categories'        => '[]',
        'tier3_hidden_categories'        => '[]',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            GuestAccessSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value'       => $value,
                    'description' => self::descriptionFor($key),
                ]
            );
        }

        // Clear the cache so fresh seeds are picked up immediately
        GuestAccessSetting::clearCache();
    }

    /**
     * Human-readable description for each setting key.
     */
    private static function descriptionFor(string $key): string
    {
        return match ($key) {
            'tier1_limit_lesson'              => 'Number of tier-1 lessons a user can access',
            'tier1_limit_core_theory'         => 'Number of tier-1 core theory items a user can access',
            'tier1_limit_case_study'          => 'Number of tier-1 case studies a user can access',
            'tier1_limit_fnh_architecture'    => 'Number of tier-1 FNH architecture items a user can access',
            'tier1_limit_advanced_insight'    => 'Number of tier-1 advanced insights a user can access',

            'tier2_limit_lesson'             => 'Number of lessons a tier-2 user can access (12 per roadmap)',
            'tier2_limit_core_theory'        => 'Number of core theory items a tier-2 user can access (99 = unlimited)',
            'tier2_limit_case_study'         => 'Number of case studies a tier-2 user can access (99 = unlimited)',
            'tier2_limit_fnh_architecture'   => 'Number of FNH architecture items a tier-2 user can access (99 = unlimited)',
            'tier2_limit_advanced_insight'   => 'Number of advanced insights a tier-2 user can access (99 = unlimited)',

            'tier3_limit_lesson'             => 'Number of lessons a tier-3 user can access (99 = unlimited)',
            'tier3_limit_core_theory'        => 'Number of core theory items a tier-3 user can access (99 = unlimited)',
            'tier3_limit_case_study'         => 'Number of case studies a tier-3 user can access (99 = unlimited)',
            'tier3_limit_fnh_architecture'   => 'Number of FNH architecture items a tier-3 user can access (99 = unlimited)',
            'tier3_limit_advanced_insight'   => 'Number of advanced insights a tier-3 user can access (99 = unlimited)',

            'tier1_ear_training_beginner_levels'     => 'Number of Tier 1 beginner ear training levels accessible',
            'tier1_ear_training_intermediate_levels' => 'Number of Tier 1 intermediate ear training levels accessible',
            'tier1_ear_training_advanced_levels'     => 'Number of Tier 1 advanced ear training levels accessible',

            'tier2_ear_training_beginner_levels'     => 'Number of Tier 2 beginner ear training levels accessible (99=all)',
            'tier2_ear_training_intermediate_levels' => 'Number of Tier 2 intermediate ear training levels accessible (99=all)',
            'tier2_ear_training_advanced_levels'     => 'Number of Tier 2 advanced ear training levels accessible (99=all)',

            'tier3_ear_training_beginner_levels'     => 'Number of Tier 3 beginner ear training levels accessible (99=all)',
            'tier3_ear_training_intermediate_levels' => 'Number of Tier 3 intermediate ear training levels accessible (99=all)',
            'tier3_ear_training_advanced_levels'     => 'Number of Tier 3 advanced ear training levels accessible (99=all)',

            'hidden_categories'              => 'JSON array of categories hidden from tier-1 users',
            'tier2_hidden_categories'        => 'JSON array of categories hidden from tier-2 users',
            'tier3_hidden_categories'        => 'JSON array of categories hidden from tier-3 users',
            default                          => '',
        };
    }
}
