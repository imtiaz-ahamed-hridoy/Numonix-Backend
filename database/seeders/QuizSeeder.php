<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Learning;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        QuizQuestion::truncate();
        Quiz::truncate();
        Schema::enableForeignKeyConstraints();

        // Fetch all published learnings in a consistent order
        $learnings = Learning::where('is_published', true)
            ->orderBy('category')
            ->orderBy('id')
            ->get();

        if ($learnings->isEmpty()) {
            $this->command->warn('No published learnings found. Run LearningSeeder first.');
            return;
        }

        foreach ($learnings as $learning) {
            $questions = $this->questionsForLearning($learning);
            $this->createQuizWithQuestions($learning, $questions);
        }
    }

    // -------------------------------------------------------------------------
    // Route questions to the correct set by category and title slug
    // -------------------------------------------------------------------------
    private function questionsForLearning(Learning $learning): array
    {
        return match ($learning->category) {
            'lesson'           => $this->lessonQuestions($learning->title),
            'core_theory'      => $this->coreTheoryQuestions($learning->title),
            'advanced_insight' => $this->advancedInsightQuestions($learning->title),
            'case_study'       => $this->caseStudyQuestions($learning->title),
            'fnh_architecture' => $this->fnhArchitectureQuestions($learning->title),
            default            => $this->genericQuestions($learning->title),
        };
    }

    // -------------------------------------------------------------------------
    // LESSON category questions
    // -------------------------------------------------------------------------
    private function lessonQuestions(string $title): array
    {
        if (str_contains($title, 'Part 1') || str_contains($title, 'Introduction')) {
            return [
                ['type' => 'mcq', 'question_text' => 'What is the core note of the Tonic (T) function in FNH?', 'options' => ['1', '3', '5', '6'], 'correct_answer' => 1, 'explanation' => 'The core note of Tonic (T) is always 3. This is the anchor note that defines the Tonic family.'],
                ['type' => 'mcq', 'question_text' => 'Which function feels stable, grounded, and at rest?', 'options' => ['Tonic (T)', 'Subdominant (S)', 'Dominant (D)', 'All functions'], 'correct_answer' => 0, 'explanation' => 'Tonic (T) represents stability and rest. S and D create motion and tension.'],
                ['type' => 'mcq', 'question_text' => 'In the primary cycle T -> S -> D -> T, which function follows Tonic?', 'options' => ['Subdominant (S)', 'Dominant (D)', 'Tonic (T)', 'All three'], 'correct_answer' => 0, 'explanation' => 'The primary cycle moves T -> S -> D -> T. Subdominant (S) is the first departure from home.'],
                ['type' => 'mcq', 'question_text' => 'What emotional quality does the Dominant (D) function create?', 'options' => ['Tension and anticipation', 'Stability and rest', 'Motion and floating', 'Neutrality'], 'correct_answer' => 0, 'explanation' => 'Dominant (D) creates tension that drives back to Tonic for resolution.'],
                ['type' => 'tap_order', 'question_text' => 'Place the primary functional cycle in the correct order:', 'options' => ['Dominant (D)', 'Subdominant (S)', 'Tonic (start)', 'Tonic (end)'], 'correct_answer' => [2, 1, 0, 3], 'explanation' => 'The primary cycle is T -> S -> D -> T: home -> departure -> tension -> resolution.'],
            ];
        }

        if (str_contains($title, 'Part 2') || str_contains($title, 'Fixed')) {
            return [
                ['type' => 'mcq', 'question_text' => 'How many positions are in the FNH fixed numeric row?', 'options' => ['7', '10', '12', '14'], 'correct_answer' => 2, 'explanation' => 'The FNH numeric row has 12 fixed positions from 1 to 7#. Every pitch has exactly one name.'],
                ['type' => 'mcq', 'question_text' => 'What is the core note of the Subdominant (S) function?', 'options' => ['3', '5', '6', '1'], 'correct_answer' => 2, 'explanation' => 'The core note of Subdominant (S) is always 6. It creates a floating, transitional feel.'],
                ['type' => 'mcq', 'question_text' => 'Which function has core note 5?', 'options' => ['Tonic (T)', 'Subdominant (S)', 'Dominant (D)', 'None'], 'correct_answer' => 2, 'explanation' => 'Dominant (D) has core note 5. In FNH, 5 creates tension that drives resolution back to T.'],
                ['type' => 'mcq', 'question_text' => 'Why does FNH use no enharmonic equivalents?', 'options' => ['Every pitch has one unique name', 'To simplify notation', 'To match piano keys', 'It uses traditional notation'], 'correct_answer' => 0, 'explanation' => 'FNH assigns every pitch exactly one numeric label, removing ambiguity.'],
                ['type' => 'tap_order', 'question_text' => 'Arrange the core notes of T, S, D in ascending numeric order:', 'options' => ['6 (Subdominant)', '5 (Dominant)', '3 (Tonic)'], 'correct_answer' => [2, 1, 0], 'explanation' => 'Core notes in ascending order: 3 (T), 5 (D), 6 (S). Each anchors its functional family.'],
            ];
        }

        // Part 3 - Functional Movement
        return [
            ['type' => 'mcq', 'question_text' => 'What drives the T -> S -> D -> T cycle forward?', 'options' => ['Harmonic movement and direction', 'Static chord holding', 'Random note selection', 'Melody alone'], 'correct_answer' => 0, 'explanation' => 'Harmonic movement creates direction: rest -> motion -> tension -> resolution.'],
            ['type' => 'mcq', 'question_text' => 'Which function resolves tension and brings the cycle back to stability?', 'options' => ['Tonic (T)', 'Subdominant (S)', 'Dominant (D)', 'All functions'], 'correct_answer' => 0, 'explanation' => 'Tonic (T) resolves tension by providing a stable harmonic home.'],
            ['type' => 'mcq', 'question_text' => 'What role does Subdominant (S) play between T and D?', 'options' => ['A bridge that creates motion', 'A tension point', 'A resolution point', 'A static hold'], 'correct_answer' => 0, 'explanation' => 'Subdominant (S) acts as a bridge. It departs from home without the tension of Dominant.'],
            ['type' => 'mcq', 'question_text' => 'What is the emotional arc of the full T -> S -> D -> T progression?', 'options' => ['Rest -> Motion -> Tension -> Rest', 'Tension -> Rest -> Motion -> Rest', 'Motion -> Rest -> Tension -> Motion', 'Random emotions'], 'correct_answer' => 0, 'explanation' => 'The emotional arc is Rest -> Motion -> Tension -> Rest. This cycle is the foundation of Western music.'],                ['type' => 'tap_order', 'question_text' => 'Order the emotional qualities of the functional cycle from start to finish:', 'options' => ['Tension (Dominant)', 'Rest (Tonic resolution)', 'Motion (Subdominant)', 'Rest (Tonic start)'], 'correct_answer' => [3, 2, 0, 1], 'explanation' => 'The emotional journey: Rest (T) -> Motion (S) -> Tension (D) -> Rest (T).'],
        ];
    }

    // -------------------------------------------------------------------------
    // CORE THEORY category questions
    // -------------------------------------------------------------------------
    private function coreTheoryQuestions(string $title): array
    {
        if (str_contains($title, 'Part 1') || str_contains($title, 'Chord Families')) {
            return [
                ['type' => 'mcq', 'question_text' => 'Which chords belong to the Tonic (T) family?', 'options' => ['I, iii, vi', 'IV, ii, vi', 'V, vii°, iii', 'All chords'], 'correct_answer' => 0, 'explanation' => 'The Tonic family includes I, iii, and vi - all share the core note 3.'],
                ['type' => 'mcq', 'question_text' => 'Which chords belong to the Subdominant (S) family?', 'options' => ['IV, ii', 'I, iii, vi', 'V, vii°', 'IV, V'], 'correct_answer' => 0, 'explanation' => 'The Subdominant family includes IV and ii - both share the core note 6.'],
                ['type' => 'mcq', 'question_text' => 'Which chords belong to the Dominant (D) family?', 'options' => ['V, vii°', 'I, iii', 'IV, ii', 'vi, iii'], 'correct_answer' => 0, 'explanation' => 'The Dominant family includes V and vii° - both share the core note 5.'],
                ['type' => 'mcq', 'question_text' => 'In C major, which chord is the Tonic?', 'options' => ['C major', 'F major', 'G major', 'A minor'], 'correct_answer' => 0, 'explanation' => 'C major is the Tonic chord (I) in the key of C major.'],
                ['type' => 'tap_order', 'question_text' => 'Order the chord families by their tension level (lowest to highest):', 'options' => ['Dominant (D)', 'Subdominant (S)', 'Tonic (T)'], 'correct_answer' => [2, 1, 0], 'explanation' => 'Tonic is most stable, Subdominant creates motion, Dominant creates maximum tension.'],
            ];
        }

        if (str_contains($title, 'Part 2') || str_contains($title, 'Intervals')) {
            return [
                ['type' => 'mcq', 'question_text' => 'Which interval creates the most tension in a dominant chord?', 'options' => ['Tritone (augmented 4th)', 'Perfect 5th', 'Major 3rd', 'Minor 7th'], 'correct_answer' => 0, 'explanation' => 'The tritone between the 3rd and 7th of a dominant chord creates its characteristic tension.'],
                ['type' => 'mcq', 'question_text' => 'A major 3rd above the root gives a chord which quality?', 'options' => ['Major', 'Minor', 'Diminished', 'Augmented'], 'correct_answer' => 0, 'explanation' => 'A major 3rd above the root produces a major chord quality.'],
                ['type' => 'mcq', 'question_text' => 'A minor 3rd above the root gives a chord which quality?', 'options' => ['Minor', 'Major', 'Diminished', 'Augmented'], 'correct_answer' => 0, 'explanation' => 'A minor 3rd above the root produces a minor chord quality.'],
                ['type' => 'mcq', 'question_text' => 'Two minor 3rds stacked on top of each other produce which chord type?', 'options' => ['Diminished', 'Minor', 'Major', 'Dominant 7'], 'correct_answer' => 0, 'explanation' => 'Stacking two minor 3rds produces a diminished triad.'],
                ['type' => 'tap_order', 'question_text' => 'Arrange intervals from most stable to most tense:', 'options' => ['Tritone', 'Perfect 5th', 'Major 3rd'], 'correct_answer' => [1, 2, 0], 'explanation' => 'The perfect 5th is most stable, the major 3rd mildly tense, and the tritone maximally tense.'],
            ];
        }

        // Part 3 - The Harmonic Series
        return [
            ['type' => 'mcq', 'question_text' => 'The harmonic series explains why which chord sounds most natural?', 'options' => ['The major triad', 'The minor triad', 'The diminished triad', 'The augmented triad'], 'correct_answer' => 0, 'explanation' => 'The major triad appears naturally in the low partials of the harmonic series, making it sound most consonant.'],
            ['type' => 'mcq', 'question_text' => 'Which partial of the harmonic series produces the perfect 5th?', 'options' => ['3rd partial', '2nd partial', '4th partial', '5th partial'], 'correct_answer' => 0, 'explanation' => 'The 3rd partial produces the perfect 5th, establishing the basis for tonal harmony.'],
            ['type' => 'mcq', 'question_text' => 'What interval is produced between the 4th and 5th harmonics?', 'options' => ['Major 3rd', 'Minor 3rd', 'Perfect 4th', 'Tritone'], 'correct_answer' => 0, 'explanation' => 'The interval between the 4th and 5th partials produces a major 3rd.'],
            ['type' => 'mcq', 'question_text' => 'FNH uses the harmonic series to justify which aspect of its system?', 'options' => ['The natural primacy of T, S, and D functions', 'The use of equal temperament', 'The 12-tone row', 'The circle of fifths notation'], 'correct_answer' => 0, 'explanation' => 'The natural dominance of T, S, and D is rooted in the acoustic reality of the harmonic series.'],                ['type' => 'tap_order', 'question_text' => 'Order the first three functional harmonics as they appear in the series:', 'options' => ['Dominant partial (3rd)', 'Octave (2nd)', 'Tonic fundamental (1st)'], 'correct_answer' => [2, 1, 0], 'explanation' => 'The harmonic series begins: fundamental (T), octave, then dominant partial.'],
        ];
    }

    // -------------------------------------------------------------------------
    // ADVANCED INSIGHT category questions
    // -------------------------------------------------------------------------
    private function advancedInsightQuestions(string $title): array
    {
        if (str_contains($title, 'Part 1') || str_contains($title, 'Secondary')) {
            return [
                ['type' => 'mcq', 'question_text' => 'What is a secondary dominant?', 'options' => ['V of V', 'ii of I', 'IV of IV', 'vi of IV'], 'correct_answer' => 0, 'explanation' => 'A secondary dominant is a dominant-type chord (V or V7) of a chord other than the tonic.'],
                ['type' => 'mcq', 'question_text' => 'In C major, what is the secondary dominant of G (V)?', 'options' => ['D7', 'A7', 'B7', 'E7'], 'correct_answer' => 0, 'explanation' => 'D7 is V/V (V of the Dominant G) in the key of C major.'],
                ['type' => 'mcq', 'question_text' => 'A secondary dominant resolves with what type of motion?', 'options' => ['Perfect 5th downward', 'Perfect 4th downward', 'Minor 3rd upward', 'Whole step downward'], 'correct_answer' => 0, 'explanation' => 'Secondary dominants resolve by descending a perfect fifth, just like primary dominant resolutions.'],
                ['type' => 'mcq', 'question_text' => 'V/IV is the secondary dominant of which chord in a major key?', 'options' => ['The IV chord', 'The V chord', 'The ii chord', 'The vi chord'], 'correct_answer' => 0, 'explanation' => 'V/IV resolves to the IV chord (the subdominant).'],
                ['type' => 'tap_order', 'question_text' => 'Order the tension levels in a V/V -> V -> I progression:', 'options' => ['Maximum tension (V)', 'Resolution (I)', 'Heightened tension (V/V)'], 'correct_answer' => [2, 0, 1], 'explanation' => 'V/V adds a pre-dominant tension before the full dominant drives to resolution.'],
            ];
        }

        if (str_contains($title, 'Part 2') || str_contains($title, 'Modal')) {
            return [
                ['type' => 'mcq', 'question_text' => 'Modal interchange borrows chords from which source?', 'options' => ['Parallel modes', 'Relative modes', 'Random keys', 'Enharmonic equivalents'], 'correct_answer' => 0, 'explanation' => 'Modal interchange borrows chords from parallel modes (same root, different scale).'],
                ['type' => 'mcq', 'question_text' => 'Which borrowed chord adds a dark colour to a major key verse?', 'options' => ['bVI (from parallel minor)', 'V/V', 'ii7', 'I#'], 'correct_answer' => 0, 'explanation' => 'The bVI chord borrowed from the parallel minor creates a dark, cinematic quality.'],
                ['type' => 'mcq', 'question_text' => 'The Lydian mode adds which characteristic tension note?', 'options' => ['#4 (sharp eleventh)', 'b7', 'b3', '#5'], 'correct_answer' => 0, 'explanation' => 'The Lydian mode raises the 4th degree, creating a bright, floating quality.'],
                ['type' => 'mcq', 'question_text' => 'The Phrygian mode is characterised by which interval?', 'options' => ['b2 (flat second)', '#4', 'b6', 'b7'], 'correct_answer' => 0, 'explanation' => 'The flat 2nd degree in Phrygian creates its characteristic dark, Spanish-sounding quality.'],
                ['type' => 'tap_order', 'question_text' => 'Order modes from brightest to darkest (relative brightness):', 'options' => ['Phrygian (darkest)', 'Ionian (Major)', 'Lydian (brightest)'], 'correct_answer' => [2, 1, 0], 'explanation' => 'Lydian is the brightest mode; Phrygian is the darkest due to its b2 degree.'],
            ];
        }

        // Part 3 - Chromatic Mediant
        return [
            ['type' => 'mcq', 'question_text' => 'A chromatic mediant relationship exists between two chords that share which quality?', 'options' => ['Same quality (major/minor), roots a 3rd apart', 'Same root, different quality', 'Tritone interval between roots', 'Perfect 5th between roots'], 'correct_answer' => 0, 'explanation' => 'Chromatic mediants share the same quality but have roots a major or minor 3rd apart.'],
            ['type' => 'mcq', 'question_text' => 'In C major, which chord is the chromatic mediant above C major?', 'options' => ['E major', 'E minor', 'A major', 'Ab major'], 'correct_answer' => 0, 'explanation' => 'E major is the chromatic mediant above C major - same quality, a major 3rd up.'],
            ['type' => 'mcq', 'question_text' => 'Chromatic mediant progressions are most associated with which style?', 'options' => ['Late Romantic / Film Music', 'Bebop Jazz', 'Folk Music', 'Gregorian Chant'], 'correct_answer' => 0, 'explanation' => 'Chromatic mediant relations are characteristic of late Romantic composers and modern film scores.'],
            ['type' => 'mcq', 'question_text' => 'Which voice-leading rule do chromatic mediants often break?', 'options' => ['Parallel 5ths are common', 'No shared tones between chords', 'Tritone must resolve stepwise', 'Root always leaps a 5th'], 'correct_answer' => 0, 'explanation' => 'Chromatic mediant motion often features parallel 5ths, which are avoided in classical voice leading.'],                ['type' => 'tap_order', 'question_text' => 'Order these chromatic mediants around C major from closest to furthest root distance:', 'options' => ['Ab major (minor 3rd below)', 'E major (major 3rd above)', 'A major (major 6th above)'], 'correct_answer' => [0, 1, 2], 'explanation' => 'Distance is measured by root interval: m3 < M3 < M6.'],
        ];
    }

    // -------------------------------------------------------------------------
    // CASE STUDY category questions
    // -------------------------------------------------------------------------
    private function caseStudyQuestions(string $title): array
    {
        if (str_contains($title, 'Part 1') || str_contains($title, 'Pop')) {
            return [
                ['type' => 'mcq', 'question_text' => 'The "4-chord pop progression" I-V-vi-IV is an example of which functional pattern?', 'options' => ['T-D-T-S', 'T-S-D-T', 'D-T-S-D', 'S-D-T-S'], 'correct_answer' => 0, 'explanation' => 'I is T, V is D, vi is also T-family, IV is S - giving a T-D-T-S functional reading.'],
                ['type' => 'mcq', 'question_text' => 'In a pop chorus, the IV chord typically functions as?', 'options' => ['Subdominant (S)', 'Dominant (D)', 'Tonic (T)', 'Mediant'], 'correct_answer' => 0, 'explanation' => 'IV is the archetypal Subdominant chord - it departs from home without creating tension.'],
                ['type' => 'mcq', 'question_text' => 'Which pop progression ending creates the strongest final resolution?', 'options' => ['V -> I', 'IV -> I', 'vi -> I', 'ii -> I'], 'correct_answer' => 0, 'explanation' => 'V -> I is the authentic cadence - the strongest resolution in tonal harmony.'],
                ['type' => 'mcq', 'question_text' => 'A pop song that ends on vi instead of I creates what type of mood?', 'options' => ['Unresolved, introspective', 'Fully resolved, uplifting', 'Dark and tense', 'Neutral and static'], 'correct_answer' => 0, 'explanation' => 'Ending on vi (a Tonic-family substitute) avoids full resolution, leaving a reflective quality.'],
                ['type' => 'tap_order', 'question_text' => 'Order the chords in the classic I-V-vi-IV progression:', 'options' => ['IV (Subdominant)', 'vi (Tonic sub)', 'V (Dominant)', 'I (Tonic)'], 'correct_answer' => [3, 2, 1, 0], 'explanation' => 'The classic pop progression moves I -> V -> vi -> IV creating T-D-T-S motion.'],
            ];
        }

        if (str_contains($title, 'Part 2') || str_contains($title, 'Jazz')) {
            return [
                ['type' => 'mcq', 'question_text' => 'The jazz ii-V-I is the FNH equivalent of which functional movement?', 'options' => ['S -> D -> T', 'T -> S -> D', 'D -> T -> S', 'S -> T -> D'], 'correct_answer' => 0, 'explanation' => 'ii is Subdominant-family, V is Dominant, I is Tonic - giving S -> D -> T.'],
                ['type' => 'mcq', 'question_text' => 'In a jazz ii-V-I in C, what are the three chords?', 'options' => ['Dm7 - G7 - Cmaj7', 'Am7 - D7 - Gmaj7', 'Fm7 - Bb7 - Ebmaj7', 'Em7 - A7 - Dmaj7'], 'correct_answer' => 0, 'explanation' => 'In C major, ii = Dm7, V = G7, I = Cmaj7.'],
                ['type' => 'mcq', 'question_text' => 'What makes jazz ii-V-I more sophisticated than basic V-I?', 'options' => ['Adding the ii7 strengthens the approach via S function', 'Removing the dominant', 'Using parallel 5ths', 'Playing in a different mode'], 'correct_answer' => 0, 'explanation' => 'The ii7 adds a Subdominant approach, creating a three-function story (S-D-T) rather than just D-T.'],
                ['type' => 'mcq', 'question_text' => 'A "tritone substitution" in a jazz ii-V-I replaces which chord?', 'options' => ['V7 with bII7', 'ii7 with bVI', 'Imaj7 with bIII', 'ii7 with V/V'], 'correct_answer' => 0, 'explanation' => 'The tritone substitution replaces V7 with bII7, creating chromatic half-step bass descent.'],
                ['type' => 'tap_order', 'question_text' => 'Order the jazz ii-V-I chords from subdominant to resolution:', 'options' => ['Imaj7 (Tonic)', 'G7 (Dominant)', 'Dm7 (Subdominant)'], 'correct_answer' => [2, 1, 0], 'explanation' => 'ii-V-I follows the S -> D -> T functional arc.'],
            ];
        }

        // Part 3 - Blues
        return [
            ['type' => 'mcq', 'question_text' => 'The 12-bar blues has the Dominant (IV) returning at which bar?', 'options' => ['Bar 5', 'Bar 3', 'Bar 7', 'Bar 9'], 'correct_answer' => 0, 'explanation' => 'In the standard 12-bar blues, the IV chord first arrives at bar 5.'],
            ['type' => 'mcq', 'question_text' => 'In blues, the tonic I chord is often played as a dominant 7. Why?', 'options' => ['To create a constant blues tension and forward motion', 'Because it is a mistake', 'To avoid the tonic function', 'To borrow from a parallel key'], 'correct_answer' => 0, 'explanation' => 'Blues harmony uses I7 to maintain a constant sense of drive and tension, even at rest.'],
            ['type' => 'mcq', 'question_text' => 'The "quick change" in blues goes to IV at bar 2 and back to I at bar 3. This creates?', 'options' => ['Early S departure for added energy', 'Full T resolution', 'D function at bar 2', 'Modal interchange'], 'correct_answer' => 0, 'explanation' => 'The quick change visits S (IV) at bar 2, adding energy and movement before returning to T.'],
            ['type' => 'mcq', 'question_text' => 'The V chord in blues typically appears at which bar?', 'options' => ['Bar 9', 'Bar 5', 'Bar 1', 'Bar 12'], 'correct_answer' => 0, 'explanation' => 'In the 12-bar blues, V (the Dominant) first appears at bar 9.'],                ['type' => 'tap_order', 'question_text' => 'Order the functional movement in the classic 12-bar blues ending (bars 9-12):', 'options' => ['I (Tonic resolution)', 'IV (Subdominant)', 'V (Dominant)'], 'correct_answer' => [2, 1, 0], 'explanation' => 'The blues turnaround typically moves V -> IV -> I, reversing the S-D-T direction.'],
        ];
    }

    // -------------------------------------------------------------------------
    // FNH ARCHITECTURE category questions
    // -------------------------------------------------------------------------
    private function fnhArchitectureQuestions(string $title): array
    {
        if (str_contains($title, 'Part 1') || str_contains($title, 'Centre')) {
            return [
                ['type' => 'mcq', 'question_text' => 'In FNH, what does "Centre Identity" refer to?', 'image_url' => '/storage/learnings/elements/images/Gi56sVlBYg9rdGxU1ZMd3dyXKYojuxfKSJhYQuY0.jpg', 'image_size' => 'medium', 'options' => ['The harmonic tonal centre of a key', 'The physical centre of the piano', 'The middle note of a scale', 'The root of the dominant chord'], 'correct_answer' => 0, 'explanation' => 'Centre Identity defines the tonal home - which note and mode the music revolves around.'],
                ['type' => 'mcq', 'question_text' => 'What is the T1 form in FNH?', 'options' => ['Major Tonic (bright, stable)', 'Minor Tonic (dark, stable)', 'Dominant Major', 'Subdominant Minor'], 'correct_answer' => 0, 'explanation' => 'T1 is the major Tonic form - the bright, stable foundation of a major key.'],
                ['type' => 'mcq', 'question_text' => 'What is the T6 form in FNH?', 'options' => ['Relative Minor Tonic', 'Major Tonic', 'Dominant Minor', 'Subdominant Major'], 'correct_answer' => 0, 'explanation' => 'T6 is the Relative Minor Tonic form - the same tonal area as T1 but with a darker character.'],
                ['type' => 'mcq', 'question_text' => 'T# forms in FNH refer to which tonal territory?', 'options' => ['Parallel key territory', 'Relative key territory', 'Dominant key territory', 'Subdominant territory'], 'correct_answer' => 0, 'explanation' => 'T# forms denote the parallel key - sharing the same root but a different mode.'],
                ['type' => 'tap_order', 'question_text' => 'Order the FNH form identities from most stable (T) to shifted (T#):', 'options' => ['T#6 (parallel minor relative)', 'T1 (major tonic)', 'T6 (relative minor)'], 'correct_answer' => [1, 2, 0], 'explanation' => 'T1 is most stable; T6 is the relative minor; T#6 is the most distant parallel form.'],
            ];
        }

        if (str_contains($title, 'Part 2') || str_contains($title, 'Functional Flow')) {
            return [
                ['type' => 'mcq', 'question_text' => 'In FNH, "Functional Flow" refers to?', 'image_url' => '/storage/lessons/contents/images/0cbiglmBaOwqewFbJTp3s9oiKXpVNQ8pCEkVsReu.png', 'image_size' => 'small', 'options' => ['The directional movement between T, S, and D functions', 'The tempo of a piece', 'The volume dynamics', 'The melodic contour'], 'correct_answer' => 0, 'explanation' => 'Functional Flow maps the direction of harmonic movement between functions.'],
                ['type' => 'mcq', 'question_text' => 'Which flow pattern represents a direct home-to-tension movement?', 'options' => ['T -> D', 'S -> T', 'D -> S', 'T -> T'], 'correct_answer' => 0, 'explanation' => 'T -> D skips the Subdominant, going directly from rest to maximum tension.'],
                ['type' => 'mcq', 'question_text' => 'What does the flow T# -> D -> T create emotionally?', 'options' => ['A shift from parallel territory into full resolution', 'A departure from home', 'A borrowed chord sequence', 'A modal mixture pattern'], 'correct_answer' => 0, 'explanation' => 'Moving through T# territory before Dominant resolution creates a dramatic colour shift.'],
                ['type' => 'mcq', 'question_text' => 'The "Tension Climb" flow pattern in FNH is best described as?', 'options' => ['S# -> D -> T with heightened pre-dominant energy', 'T -> S -> T', 'D -> D -> T', 'T -> T -> D -> T'], 'correct_answer' => 0, 'explanation' => 'The Tension Climb uses the S# territory to intensify the dominant approach.'],
                ['type' => 'tap_order', 'question_text' => 'Order these FNH flow patterns from most direct to most elaborate:', 'options' => ['T# -> S# -> D -> T (full arc)', 'D -> T (direct resolution)', 'T -> D -> T (skipping S)'], 'correct_answer' => [1, 2, 0], 'explanation' => 'Direct resolution has fewest steps; the full arc traverses the most harmonic territory.'],
            ];
        }

        // Part 3 - Template Form Tables
        return [
            ['type' => 'mcq', 'question_text' => 'What does the "Template Form Table" show in FNH?', 'image_url' => '/storage/lessons/contents/images/2Mg2ZbB8DIX8DJ5tXJrtiuClOGLB2pwHSrXdL70R.png', 'image_size' => 'medium', 'options' => ['The mapping between harmonic templates and T/T# form identities', 'A chord progression chart', 'A list of melodies', 'A rhythm pattern guide'], 'correct_answer' => 0, 'explanation' => 'The Template Form Table maps structural harmonic templates to their T and T# form identities.'],
            ['type' => 'mcq', 'question_text' => 'In the Template Form Table, what does D1 represent?', 'options' => ['Dominant Major form', 'Subdominant Major form', 'Tonic Major form', 'Dominant Minor form'], 'correct_answer' => 0, 'explanation' => 'D1 is the major Dominant form in FNH - the bright tension function.'],
            ['type' => 'mcq', 'question_text' => 'Which form identity occupies the S (Subdominant) position for a major template?', 'options' => ['S2 (minor 7th quality)', 'S1 (major quality)', 'D2 (minor 7th)', 'T# (parallel major)'], 'correct_answer' => 0, 'explanation' => 'In a major key template, S2 (minor 7th chord, e.g., ii7) occupies the Subdominant position.'],
            ['type' => 'mcq', 'question_text' => 'What does reading the Template Form Table tell a musician?', 'options' => ['Which chords fulfil each function in any key', 'Only the chord voicings', 'The bpm of each chord', 'The note names in traditional notation'], 'correct_answer' => 0, 'explanation' => 'The table makes the functional role of every chord visible regardless of the key centre.'],                ['type' => 'tap_order', 'question_text' => 'Order these FNH form identities in the standard template sequence (T -> S -> D):', 'options' => ['D1 (Dominant Major)', 'S2 (Subdominant Minor 7)', 'T1 (Tonic Major)'], 'correct_answer' => [2, 1, 0], 'explanation' => 'The standard template sequence follows T1 -> S2 -> D1 matching the T -> S -> D functional flow.'],
        ];
    }

    // -------------------------------------------------------------------------
    // Generic fallback questions (safety net)
    // -------------------------------------------------------------------------
    private function genericQuestions(string $title): array
    {
        return [
            ['type' => 'mcq', 'question_text' => 'Which function in FNH represents the concept of "home" and rest?', 'options' => ['Tonic (T)', 'Subdominant (S)', 'Dominant (D)', 'Mediant'], 'correct_answer' => 0, 'explanation' => 'The Tonic function represents home, stability, and rest in functional harmony.'],
            ['type' => 'mcq', 'question_text' => 'What creates the strongest sense of tension in a progression?', 'options' => ['Dominant (D)', 'Tonic (T)', 'Subdominant (S)', 'A repeated chord'], 'correct_answer' => 0, 'explanation' => 'The Dominant function creates maximum tension that drives resolution back to the Tonic.'],
            ['type' => 'mcq', 'question_text' => 'Which function acts as a bridge between rest and tension?', 'options' => ['Subdominant (S)', 'Tonic (T)', 'Dominant (D)', 'None'], 'correct_answer' => 0, 'explanation' => 'Subdominant (S) bridges the stable Tonic and the tense Dominant, creating forward motion.'],
            ['type' => 'mcq', 'question_text' => 'The T -> S -> D -> T cycle ends with which functional quality?', 'options' => ['Rest and resolution', 'Tension and departure', 'Motion and floating', 'Ambiguity'], 'correct_answer' => 0, 'explanation' => 'The cycle ends with Tonic, bringing rest and resolution after the tension of the Dominant.'],
            ['type' => 'tap_order', 'question_text' => 'Place the three core functions in order of increasing tension:', 'options' => ['Dominant (D) - most tense', 'Subdominant (S) - transitional', 'Tonic (T) - most stable'], 'correct_answer' => [2, 1, 0], 'explanation' => 'Tonic is most stable, Subdominant creates motion, and Dominant holds maximum tension.'],
        ];
    }

    // -------------------------------------------------------------------------
    // Helper: create the quiz and its questions
    // -------------------------------------------------------------------------
    private function createQuizWithQuestions(Learning $learning, array $questions): void
    {
        $quiz = Quiz::create([
            'learning_id' => $learning->id,
            'status'      => 'published',
        ]);

        foreach ($questions as $q) {
            QuizQuestion::create([
                'quiz_id'        => $quiz->id,
                'type'           => $q['type'],
                'question_text'  => $q['question_text'],
                'image_url'      => $q['image_url'] ?? null,
                'image_size'     => $q['image_size'] ?? null,
                'options'        => $q['options'],
                'correct_answer' => $q['correct_answer'],
                'explanation'    => $q['explanation'],
            ]);
        }
    }
}
