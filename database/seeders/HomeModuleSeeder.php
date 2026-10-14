<?php

namespace Database\Seeders;

use App\Models\HomeModule;
use Illuminate\Database\Seeder;

class HomeModuleSeeder extends Seeder
{
    public function run(): void
    {
        HomeModule::truncate();

        $modules = [
            [
                'title'       => 'Learn Lesson',
                'description' => 'Structured lessons in Functional Numeric Harmony - from the fixed chromatic map to advanced harmonic motion.',
                'icon'        => 'uploads/home-modules/icon_lesson.svg',
                'badge_text'  => 'Lesson',
                'cta_text'    => 'Start',
                'module_type' => 'learning_category',
                'module_key'  => 'lesson',
                'is_premium'  => false,
                'is_active'   => true,
            ],
            [
                'title'       => 'Case Study Analysis',
                'description' => 'See FNH applied to real compositions. Analyse how harmonic flow shapes great music.',
                'icon'        => 'uploads/home-modules/icon_case_study.svg',
                'badge_text'  => 'Analysis',
                'cta_text'    => 'Explore',
                'module_type' => 'learning_category',
                'module_key'  => 'case_study',
                'is_premium'  => false,
                'is_active'   => true,
            ],
            [
                'title'       => 'Core Theory',
                'description' => 'Read the foundational framework behind Functional Numeric Harmony in depth.',
                'icon'        => 'uploads/home-modules/icon_core_theory.svg',
                'badge_text'  => 'Reference',
                'cta_text'    => 'Read',
                'module_type' => 'learning_category',
                'module_key'  => 'core_theory',
                'is_premium'  => false,
                'is_active'   => true,
            ],
            [
                'title'       => 'Harmonic Builder',
                'description' => 'Build harmonic progressions sentence by sentence using the FNH framework.',
                'icon'        => 'uploads/home-modules/icon_harmonic_builder.svg',
                'badge_text'  => 'Studio',
                'cta_text'    => 'Open',
                'module_type' => 'external',
                'module_key'  => 'harmonic_builder',
                'is_premium'  => false,
                'is_active'   => true,
            ],
            [
                'title'       => 'FNH Architecture',
                'description' => 'Guided templates and creative tools for songwriters, composers and producers to design song structure through FNH.',
                'icon'        => 'uploads/home-modules/icon_fnh_architecture.svg',
                'badge_text'  => 'Creative',
                'cta_text'    => 'Begin',
                'module_type' => 'learning_category',
                'module_key'  => 'fnh_architecture',
                'is_premium'  => false,
                'is_active'   => true,
            ],
            [
                'title'       => 'Functional Ear Training',
                'description' => 'Train your ear to recognise T, S, D and their variations by sound alone.',
                'icon'        => 'uploads/home-modules/icon_ear_training.svg',
                'badge_text'  => 'Ear Training',
                'cta_text'    => 'Start',
                'module_type' => 'ear_training',
                'module_key'  => 'ear_training',
                'is_premium'  => false,
                'is_active'   => true,
            ],
            [
                'title'       => 'Advanced Insights',
                'description' => 'Deeper discoveries behind Functional Numeric Harmony. Uncover what traditional theory left out.',
                'icon'        => 'uploads/home-modules/icon_advanced_insights.svg',
                'badge_text'  => 'Advanced',
                'cta_text'    => 'Discover',
                'module_type' => 'learning_category',
                'module_key'  => 'advanced_insight',
                'is_premium'  => false,
                'is_active'   => true,
            ],
        ];

        foreach ($modules as $module) {
            HomeModule::create($module);
        }
    }
}
