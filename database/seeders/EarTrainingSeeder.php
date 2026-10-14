<?php

namespace Database\Seeders;

use App\Models\EarTrainingStage;
use App\Models\EarTrainingLevel;
use App\Models\EarTrainingUserProgress;
use App\Models\EarTrainingUserAnswer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EarTrainingSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('ear_training_explanation_templates')->truncate();
        EarTrainingUserAnswer::truncate();
        EarTrainingUserProgress::truncate();
        EarTrainingLevel::truncate();
        EarTrainingStage::truncate();
        Schema::enableForeignKeyConstraints();

        $this->seedLevels();
        $this->seedExplanationTemplates();
    }

    /* ------------------------------------------------------------------
     * Stages (Categories) + Levels (Quizzes)
     * ------------------------------------------------------------------ */

    private function seedLevels(): void
    {
        // -- BEGINNER ------------------------------------------------------
        $beginner = EarTrainingStage::create([
            'title'              => 'Beginner',
            'subtitle'           => 'Core chord movements for beginners',
            'description'        => 'Recognise core functional movements: T-S-T, T-D-T, S-T-S. Short progressions with limited chords.',
            'accent_colour'      => '#3B82F6',
            'patterns_label'     => 'T-S-T, T-D-T, S-D-T',
            'next_stage_teaser'  => 'Unlock Intermediate to explore secondary dominants and borrowed chords.',
            'is_premium'         => false,
            'is_locked_by_default'=> false,
            'is_active'          => true,
            'sort_order'         => 1,
        ]);

        $beginner->levels()->createMany([
            [
                'title'            => 'T and S Basics',
                'description'      => 'Hear the difference between home (T) and departure (S).',
                'level_number'     => 1,
                'is_premium'       => false,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_function',
                'function_patterns'=> [['T', null, 'S', null], ['T', null, 'S', 'T']],
                'tempo_bpm'        => 72,
                'midi_program'     => 0,
                'chord_voicing'    => 'block',
                'chord_size'       => '3',
                'chord_purity'     => 'pure',
                'answer_display'   => 'leadership_only',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => false,
                'repetitions'      => 2,
                'exercise_count'   => 10,
                'completion_heading' => 'Level 1 Complete',
                'completion_body'  => 'You can now hear T and S. The tonic feels like gravity; S is the first step away.',
                'completion_footer' => 'Next: add D to the mix.',
            ],
            [
                'title'            => 'T, S and D',
                'description'      => 'Hear home, departure, tension, and resolution.',
                'level_number'     => 2,
                'is_premium'       => false,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_function',
                'function_patterns'=> [['T', null, 'S', 'D'], ['T', null, 'D', null]],
                'tempo_bpm'        => 72,
                'midi_program'     => 0,
                'chord_voicing'    => 'block',
                'chord_size'       => '3',
                'chord_purity'     => 'pure',
                'answer_display'   => 'leadership_only',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => true,
                'repetitions'      => 2,
                'exercise_count'   => 10,
                'completion_heading' => 'Level 2 Complete',
                'completion_body'  => 'You now hear the three core functions. T grounds, S departs, D creates tension that pulls back home.',
                'completion_footer' => 'Next: hear the full T-S-D-T cycle.',
            ],
            [
                'title'            => 'Four-Step Chains',
                'description'      => 'Hear and identify the full T-S-D-T arc.',
                'level_number'     => 3,
                'is_premium'       => false,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_progression',
                'function_patterns'=> [['T', 'S', 'D', 'T'], ['T', null, 'S', 'D']],
                'tempo_bpm'        => 76,
                'midi_program'     => 0,
                'chord_voicing'    => 'block',
                'chord_size'       => '3',
                'chord_purity'     => 'pure',
                'answer_display'   => 'leadership_only',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => true,
                'repetitions'      => 1,
                'exercise_count'   => 10,
                'completion_heading' => 'Beginner Stage Complete',
                'completion_body'  => 'You hear the complete T-S-D-T journey. This is the foundation of all tonal music.',
                'completion_footer' => 'You are ready for Intermediate.',
            ],
        ]);

        // -- INTERMEDIATE --------------------------------------------------
        $intermediate = EarTrainingStage::create([
            'title'              => 'Intermediate',
            'subtitle'           => 'Secondary dominants and borrowed chords',
            'description'        => 'Add secondary dominants and borrowed chords. Focus on voice leading and extended progressions.',
            'accent_colour'      => '#8B5CF6',
            'patterns_label'     => 'D#, Db, S#',
            'next_stage_teaser'  => 'Unlock Advanced to explore modulations and jazz harmony.',
            'is_premium'         => true,
            'is_locked_by_default'=> true,
            'is_active'          => true,
            'sort_order'         => 2,
        ]);

        $intermediate->levels()->createMany([
            [
                'title'            => 'Secondary Dominants',
                'description'      => 'Hear added tension introduced before a resolution.',
                'level_number'     => 4,
                'is_premium'       => true,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_progression',
                'function_patterns'=> [['D#', null, 'D', 'T'], ['T', 'D#', 'D', 'T']],
                'tempo_bpm'        => 80,
                'midi_program'     => 0,
                'chord_voicing'    => 'block',
                'chord_size'       => '4',
                'chord_purity'     => 'mixed',
                'answer_display'   => 'with_influences',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => true,
                'repetitions'      => 1,
                'exercise_count'   => 10,
                'completion_heading' => 'Level 4 Complete',
                'completion_body'  => 'You now hear the double-tension approach of V/V before V. Secondary dominants intensify the D function.',
                'completion_footer' => 'Next: borrowed chords.',
            ],
            [
                'title'            => 'Borrowed Chords',
                'description'      => 'Recognise chords borrowed from the parallel minor key.',
                'level_number'     => 5,
                'is_premium'       => true,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_function',
                'function_patterns'=> [['T', null, 'Db', 'S'], ['T', 'Db', null, 'S']],
                'tempo_bpm'        => 78,
                'midi_program'     => 0,
                'chord_voicing'    => 'block',
                'chord_size'       => '3',
                'chord_purity'     => 'mixed',
                'answer_display'   => 'with_influences',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => false,
                'repetitions'      => 2,
                'exercise_count'   => 10,
                'completion_heading' => 'Level 5 Complete',
                'completion_body'  => 'Borrowed chords create dark modal colour inside a major key. Your ear now hears the difference.',
                'completion_footer' => 'Next: combine both techniques.',
            ],
            [
                'title'            => 'Extended Progressions',
                'description'      => 'Combine secondary dominants and borrowed chords in longer progressions.',
                'level_number'     => 6,
                'is_premium'       => true,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_progression',
                'function_patterns'=> [['T', 'D#', 'D', 'S#']],
                'tempo_bpm'        => 82,
                'midi_program'     => 0,
                'chord_voicing'    => 'block',
                'chord_size'       => '4',
                'chord_purity'     => 'mixed',
                'answer_display'   => 'with_influences',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => true,
                'repetitions'      => 1,
                'exercise_count'   => 10,
                'completion_heading' => 'Intermediate Stage Complete',
                'completion_body'  => 'You can now hear complex chromatic progressions and identify their functions within the tonal framework.',
                'completion_footer' => 'You are ready for Advanced.',
            ],
        ]);

        // -- ADVANCED ------------------------------------------------------
        $advanced = EarTrainingStage::create([
            'title'              => 'Advanced',
            'subtitle'           => 'Modulations and jazz harmony',
            'description'        => 'Complex modulations, tritone substitutions, and advanced jazz harmony ear training.',
            'accent_colour'      => '#F59E0B',
            'patterns_label'     => 'S-D#-T, Db',
            'next_stage_teaser'  => 'Advanced - the final frontier of tonal ear training.',
            'is_premium'         => true,
            'is_locked_by_default'=> true,
            'is_active'          => true,
            'sort_order'         => 3,
        ]);

        $advanced->levels()->createMany([
            [
                'title'            => 'Modulations',
                'description'      => 'Hear key changes and tonal centre shifts in context.',
                'level_number'     => 7,
                'is_premium'       => true,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_progression',
                'function_patterns'=> [['T', null, 'd#', 'D']],
                'tempo_bpm'        => 84,
                'midi_program'     => 0,
                'chord_voicing'    => 'block',
                'chord_size'       => '4',
                'chord_purity'     => 'any',
                'answer_display'   => 'with_influences',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => true,
                'repetitions'      => 1,
                'exercise_count'   => 10,
                'completion_heading' => 'Level 7 Complete',
                'completion_body'  => 'You now hear tonal centre shifts. The ear that hears modulation hears the full architecture of music.',
                'completion_footer' => 'Next: tritone substitutions.',
            ],
            [
                'title'            => 'Tritone Substitutions',
                'description'      => 'Identify tritone substitutions and chromatic bass movement.',
                'level_number'     => 8,
                'is_premium'       => true,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_function',
                'function_patterns'=> [['S', 'Db', 'T', null]],
                'tempo_bpm'        => 80,
                'midi_program'     => 0,
                'chord_voicing'    => 'arpeggiated',
                'chord_size'       => '4',
                'chord_purity'     => 'any',
                'answer_display'   => 'with_influences',
                'chord_regularity' => 'regular',
                'bar_length'       => 4,
                'closes_on_following_one' => true,
                'repetitions'      => 2,
                'exercise_count'   => 10,
                'completion_heading' => 'Level 8 Complete',
                'completion_body'  => 'Tritone substitution - same guide tones, chromatic bass descent. You can now hear the smoothest dominant substitute in jazz.',
                'completion_footer' => 'Next: full jazz harmony.',
            ],
            [
                'title'            => 'Jazz Harmony',
                'description'      => 'Hear extensions, altered dominants, and ii-V-I turnarounds.',
                'level_number'     => 9,
                'is_premium'       => true,
                'guest_access_override' => 'Use Level Rule',
                'is_active'        => true,
                'exercise_type'    => 'identify_progression',
                'function_patterns'=> [['S', 'D#', 'T', null]],
                'tempo_bpm'        => 88,
                'midi_program'     => 0,
                'chord_voicing'    => 'arpeggiated',
                'chord_size'       => '5',
                'chord_purity'     => 'any',
                'answer_display'   => 'with_influences',
                'chord_regularity' => 'irregular',
                'bar_length'       => 4,
                'closes_on_following_one' => true,
                'repetitions'      => 1,
                'exercise_count'   => 10,
                'completion_heading' => 'Advanced Stage Complete',
                'completion_body'  => 'You have completed the full ear training programme. The language of harmony is yours.',
                'completion_footer' => 'Keep practising. Every listen is a lesson.',
            ],
        ]);
    }

    /* ------------------------------------------------------------------
     * Explanation Templates (new automated model - engine reads these)
     * ------------------------------------------------------------------ */

    private function seedExplanationTemplates(): void
    {
        // -- Transition templates -------------------------------------------
        $transitions = [
            ['T', 'S', 'From rest into gentle departure - the tonic releases and the subdominant opens outward.'],
            ['T', 'D', 'From rest straight into tension - the tonic jumps directly to the dominant pull.'],
            ['S', 'T', 'Departure resolves softly back home - the subdominant eases back to the tonic.'],
            ['S', 'D', 'Departure builds into tension - subdominant energy accumulates toward the dominant.'],
            ['D', 'T', 'Maximum tension resolves to rest - this is the most powerful cadence in tonal music.'],
            ['D', 'S', 'Tension deflects away from home - a deceptive resolution that avoids the tonic.'],
            ['T', 'T', 'The tonic rests on itself - stability prolonged with no departure.'],
            ['S', 'S', 'Departure extends - the subdominant floats before committing to a direction.'],
            ['D', 'D', 'Tension sustains - the dominant holds its pull before resolving.'],
        ];

        $rows = [];
        foreach ($transitions as [$from, $to, $phrase]) {
            $rows[] = [
                'type'          => 'transition',
                'from_function' => $from,
                'to_function'   => $to,
                'phrase'        => $phrase,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        DB::table('ear_training_explanation_templates')->insert($rows);

        // -- Pattern templates ----------------------------------------------
        $patterns = [
            ['T-S-T',       'Departure and Return',          'A short journey from home, a moment of floating, and a gentle return. The most common short phrase shape in tonal music.'],
            ['T-D-T',       'Tension and Immediate Relief',  'Direct leap into the dominant\'s pull, then immediate resolution. Urgent and decisive.'],
            ['T-S-D-T',     'The Primary Cycle',             'The complete functional arc - rest, departure, tension, resolution. The backbone of Western harmony.'],
            ['S-D-T',       'The Resolution Arc',            'Departure builds into tension and then resolves home. The most satisfying three-chord progression.'],
            ['T-S-D',       'Unresolved Journey',            'The progression departs and builds tension but does not resolve - it ends on an open question.'],
            ['D#-D-T',     'Double Tension Approach',       'A secondary dominant intensifies the primary dominant. Extra urgency before the final resolution.'],
            ['T-Db-S',   'Epic Rock Cadence',             'A borrowed flat-seven creates a modal, anthemic sound before the plagal subdominant.'],
            ['S-D-T','Jazz ii-V-I',                   'The fundamental jazz cadence. Smooth voice-leading from minor seventh through dominant to major seventh tonic.'],
            ['S-Db-T',  'Tritone Substitution Cadence',  'The dominant is replaced by its tritone substitute - chromatic half-step bass descent creates silky resolution.'],
        ];

        $rows = [];
        foreach ($patterns as [$pattern, $title, $body]) {
            $rows[] = [
                'type'       => 'pattern',
                'pattern'    => $pattern,
                'title'      => $title,
                'body'       => $body,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('ear_training_explanation_templates')->insert($rows);

        // -- Contrast templates ---------------------------------------------
        $contrasts = [
            ['T-S-T', 'T-D-T', 'Gentle Return vs. Decisive Tension',       'T-S-T floats away and drifts home. T-D-T leaps into urgency and snaps back. Both resolve - but with very different energy.'],
            ['S-D-T', 'T-S-T', 'The Complete Arc vs. The Short Return',    'S-D-T is a directed journey with a powerful resolution. T-S-T is a simpler oscillation without the tension of D.'],
            ['D-T',   'S-T',   'Powerful vs. Gentle Resolution',           'D resolves to T with maximum force - the authentic cadence. S to T is softer and more contemplative - the plagal cadence.'],
        ];

        $rows = [];
        foreach ($contrasts as [$p1, $p2, $title, $body]) {
            $rows[] = [
                'type'    => 'contrast',
                'pattern' => $p1 . '|' . $p2,
                'title'   => $title,
                'body'    => $body,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('ear_training_explanation_templates')->insert($rows);

        // -- Hint templates -------------------------------------------------
        $hints = [
            ['T', 'Hear the gravity', 'The tonic feels like home. It is stable, settled, and complete. Nothing pulls away from it.'],
            ['S', 'Hear the departure', 'The subdominant floats slightly - not tense, but it has left the comfort of home. It has motion but no urgency.'],
            ['D', 'Hear the pull', 'The dominant creates strong tension. It has an almost physical pull back toward the tonic. It cannot rest here - it must resolve.'],
            ['D#', 'Hear the build-up to tension', 'The secondary dominant adds extra intensity before the primary dominant arrives. A double-layered approach.'],
            ['S#', 'Hear the dark colour', 'The flat-six is borrowed from the parallel minor. It adds cinematic darkness inside a major-key context.'],
            ['Db', 'Hear the modal flavour', 'The flat-seven is common in rock and folk. It sounds like a half-step drop from the tonic - broad and anthemic.'],
        ];

        $rows = [];
        foreach ($hints as [$display, $hint, $description]) {
            $rows[] = [
                'type'          => 'hint',
                'from_function' => $display,
                'hint_text'     => $hint,
                'display_value' => $description,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        DB::table('ear_training_explanation_templates')->insert($rows);

        // -- Prompt templates (default question text per exercise type) -----
        $prompts = [
            ['identify_function',    'Which function do you hear?'],
            ['identify_progression', 'Which progression do you hear?'],
        ];

        $rows = [];
        foreach ($prompts as [$exerciseType, $promptText]) {
            $rows[] = [
                'type'          => 'prompt',
                'exercise_type' => $exerciseType,
                'prompt_text'   => $promptText,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        DB::table('ear_training_explanation_templates')->insert($rows);
    }
}
