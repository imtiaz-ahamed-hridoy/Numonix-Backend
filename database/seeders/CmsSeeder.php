<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cms\UserGuide;
use App\Models\Cms\TermsConditions;
use App\Models\Cms\PrivacyPolicy;
use App\Models\Cms\Glossary;
use App\Models\Cms\CompleteGlossary;
use App\Models\Cms\CompleteGlossarySection;
use App\Models\Cms\AboutNumoniX;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // ---- USER GUIDE ----------------------------------------------------
        UserGuide::query()->delete();

        $welcome = UserGuide::create([
            'tab'        => 'getting_started',
            'heading'    => 'Welcome to NumoniX',
            'intro_text' => 'FNH teaches you to see the relationships between sounds -- the gravitational pull of each function within music.',
        ]);

        $welcomeSection = $welcome->sections()->create([
            'heading'    => 'How to Approach Learning FNH',
        ]);

        $welcomeItems = [
            ['title' => 'Start with the Tier 1 foundational lessons.', 'body' => 'These will show you the fixed numeric system and the concept of functions: Tonic, Subdominant, and Dominant.'],
            ['title' => 'Follow the lessons in order.',              'body' => 'FNH is cumulative - each lesson builds on the last. Develop understanding progressively.'],
            ['title' => 'Listen actively.',                          'body' => 'Each example has an audio demonstration. Notice how each function feels: stable, moving, or resolving.'],
            ['title' => 'Visualise the flow.',                       'body' => 'Pay attention to the Sentence Skeleton diagrams - they show how functions move across beats.'],
        ];

        foreach ($welcomeItems as $i => $item) {
            $welcomeSection->items()->create([
                'title'      => $item['title'],
                'body'       => $item['body'],
            ]);
        }

        $harmonic = UserGuide::create([
            'tab'        => 'harmonic_builder',
            'heading'    => 'Composition Builder',
            'intro_text' => 'When you unlock the Composition Builder -- a visual tool for constructing harmonic progressions using FNH.',
        ]);

        $harmonicSection = $harmonic->sections()->create([
            'heading'    => 'Getting Started with the Builder',
        ]);

        $harmonicItems = [
            [
                'title' => 'Access via Library',
                'body' => 'Tap beats on the sentence skeleton grid to place chords.',
                'keys' => ['1', '2'],
                'images' => [
                    ['src' => 'cms/user-guide/sample1.png']
                ]
            ],
            [
                'title' => 'Browse Chords',
                'body' => 'Browse the chord library filtered by function, purity, and note count.',
                'keys' => ['3'],
                'images' => []
            ],
            [
                'title' => 'Analysis',
                'body' => 'Long-press any chord to view its full harmonic analysis.',
                'keys' => ['4'],
                'images' => []
            ],
            [
                'title' => 'Playback',
                'body' => 'Hear your compositions played back in real time via the piano engine.',
                'keys' => ['5'],
                'images' => []
            ],
        ];

        foreach ($harmonicItems as $i => $item) {
            $harmonicSection->items()->create([
                'title'      => $item['title'],
                'body'       => $item['body'],
                'keys'       => $item['keys'],
                'images'     => $item['images'],
            ]);
        }

        // ---- TERMS & CONDITIONS ---------------------------------------------
        TermsConditions::query()->delete();

        $terms = TermsConditions::create([
            'title'              => 'Terms & Conditions',
            'last_updated_label' => 'January 2025',
        ]);

        $termsSections = [
            ['title' => 'Acceptance of Terms',   'body' => 'By accessing or using NumoniX, you agree to be bound by these Terms and Conditions. If you do not agree, please do not use the app.'],
            ['title' => 'Membership',             'body' => 'NumoniX offers tiered membership plans that unlock access to lessons, tools, and features. Membership can be cancelled at any time through the NumoniX website.'],
            ['title' => 'Use of Content',         'body' => 'All content within NumoniX is the intellectual property of NumoniX and its creators. You may not reproduce, distribute, or create derivative works without explicit written permission.'],
            ['title' => 'User Conduct',           'body' => 'You agree to use NumoniX for personal, non-commercial purposes only. Any attempt to reverse-engineer, scrape, or misuse the platform is strictly prohibited.'],
            ['title' => 'Account Responsibility', 'body' => 'You are responsible for maintaining the confidentiality of your account credentials. NumoniX is not liable for any loss resulting from unauthorised access to your account.'],
            ['title' => 'Termination',            'body' => 'NumoniX reserves the right to suspend or terminate accounts that violate these terms or engage in fraudulent behaviour.'],
            ['title' => 'Changes to Terms',       'body' => 'We may update these Terms from time to time. Continued use of NumoniX after changes are posted constitutes acceptance of the revised terms.'],
            ['title' => 'Contact',                'body' => 'For questions about these Terms, please contact us through the Help & Support section of the app.'],
        ];

        foreach ($termsSections as $i => $section) {
            $terms->sections()->create([
                'section_number' => $i + 1,
                'title'          => $section['title'],
                'body'           => $section['body'],
            ]);
        }

        // ---- PRIVACY POLICY ------------------------------------------------
        PrivacyPolicy::query()->delete();

        $privacy = PrivacyPolicy::create([
            'title'              => 'Privacy Policy',
            'last_updated_label' => 'January 2025',
        ]);

        $privacySections = [
            ['title' => 'Information We Collect',    'body' => 'We collect information you provide when creating an account (name, email), your usage data within the app (lessons completed, progress), and device information for app functionality.'],
            ['title' => 'How We Use Your Information', 'body' => 'Your data is used to provide and improve the NumoniX experience, personalise your learning journey, and send you relevant notifications with your consent.'],
            ['title' => 'Data Sharing',              'body' => 'We do not sell your personal data. We may share anonymised, aggregated data for analytics. Payment processing is handled securely via the NumoniX website.'],
            ['title' => 'Data Retention',            'body' => 'We retain your data for as long as your account is active. You may request deletion at any time through the Manage Account section.'],
            ['title' => 'Your Rights',               'body' => 'You have the right to access, correct, or delete your personal data. You may opt out of non-essential communications via your Notification settings.'],
            ['title' => 'Security',                  'body' => 'We implement industry-standard security measures to protect your data. No method of transmission over the internet is completely secure.'],
            ['title' => 'Children',                  'body' => 'NumoniX is not intended for users under the age of 13. We do not knowingly collect data from children.'],
            ['title' => 'Contact',                   'body' => 'For privacy-related questions or data requests, please contact us through the Help & Support section.'],
        ];

        foreach ($privacySections as $i => $section) {
            $privacy->sections()->create([
                'section_number' => $i + 1,
                'title'          => $section['title'],
                'body'           => $section['body'],
            ]);
        }

        // ---- GLOSSARY -------------------------------------------------------
        Glossary::query()->delete();

        $glossary = Glossary::create([
            'title' => 'Glossary',
        ]);

        $glossaryTerms = [
            ['term' => 'Fixed Numeric Row',  'definition' => '1, 2b, 2, 3, 3#, 4, 4#, 5, 6, 6#, 7, 7# -- no enharmonics.'],
            ['term' => 'T (Tonic)',          'definition' => 'Core = 3. Foundation chords: 1-3-5, 3-5-7. Resolution and rest.'],
            ['term' => 'S (Subdominant)',    'definition' => 'Core = 6. Foundation chords: 4-6-1, 6-1-3. Bridges T and D.'],
            ['term' => 'D (Dominant)',       'definition' => 'Core = 2. Foundation chord: 7-2-4 (preferred). Drives tension.'],
            ['term' => 'Db (2b)',            'definition' => 'Dual identity: home (short hold) or tension (long hold). Reversal pivot.'],
            ['term' => 'D# Realm (4#)',      'definition' => 'Dominant pivot and temporary home on 1.'],
            ['term' => 'Sentence',           'definition' => '8-beat cycle; strong beats = 1, 3, 5, 7.'],
            ['term' => 'Announcement',       'definition' => 'Function declared on a strong beat, held for 1+ beat.'],
            ['term' => 'Noodling',           'definition' => 'Decoration on weak beats; does not change function.'],
            ['term' => 'Gravitational Flow', 'definition' => 'T -> S -> D -> T (basic). T -> S -> D -> # -> Db -> T (expanded). Reversible.'],
            ['term' => 'Go Bigger / Go Home', 'definition' => 'Each move climbs the hierarchy (bigger) or lands on a natural function (home).'],
            ['term' => 'Reframing',          'definition' => 'Same notes, new function as time passes.'],
            ['term' => 'Super-Modulation',   'definition' => 'Reframing a note or chord as a new function through confirmation in time.'],
        ];

        foreach ($glossaryTerms as $i => $t) {
            $glossary->terms()->create([
                'term'       => $t['term'],
                'definition' => $t['definition'],
            ]);
        }

        // ---- COMPLETE GLOSSARY ----------------------------------------------
        CompleteGlossary::query()->delete();

        $completeGlossary = CompleteGlossary::create([
            'title' => 'Complete Glossary',
        ]);

        $completeSections = [
            [
                'label' => 'I. FIXED NUMERIC SYSTEM',
                'items' => [
                    ['title' => '', 'body' => '1, 2b, 2, 3, 3#, 4, 4#, 5, 6, 6#, 7, 7# -- The 12-note numeric framework of FNH. Each number represents a fixed chromatic position with no enharmonic equivalence.'],
                ],
            ],
            [
                'label' => 'II. CORE FUNCTIONAL FAMILIES',
                'items' => [
                    ['title' => 'T (Tonic)',       'body' => 'Core: 3. Foundation Chords: 1-3-5, 3-5-7. Resolution point or resting centre.'],
                    ['title' => 'S (Subdominant)', 'body' => 'Core: 6. Foundation Chords: 4-6-1, 6-1-3. Bridges T and D.'],
                    ['title' => 'D (Dominant)',    'body' => 'Core: 2. Foundation Chords: 2-4-6, 5-7-2, 7-2-4. Drives tension toward T.'],
                ],
            ],
            [
                'label' => 'III. EXTENDED FUNCTIONAL VARIANTS',
                'items' => [
                    ['title' => '', 'body' => 'Raised forms that push beyond natural gravitational flow. Temporary expansions or heightened versions of their cores.'],
                ],
            ],
            [
                'label' => 'IV. SENTENCE ARCHITECTURE',
                'items' => [
                    ['title' => '', 'body' => 'The full 8-beat harmonic phrase. Strong beats: 1, 3, 5, 7. Beat Hierarchy defines rhythmic weight and functional importance.'],
                ],
            ],
            [
                'label' => 'V. GRAVITATIONAL FLOW',
                'items' => [
                    ['title' => '', 'body' => 'The universal FNH path: T -> S -> D -> T. Expanded version includes polarity and reversals.'],
                ],
            ],
            [
                'label' => 'VI. REFRAMING AND RELATIVITY',
                'items' => [
                    ['title' => '', 'body' => 'When a chord or note maintains the same pitch but adopts a new functional identity as time passes.'],
                ],
            ],
            [
                'label' => 'VII. FUNCTIONAL CYCLES',
                'items' => [
                    ['title' => '', 'body' => 'A progressive flow through raised functions: T -> t# -> S# -> D -> D# -> T. Used to extend energy and brightness.'],
                ],
            ],
        ];

        foreach ($completeSections as $si => $sectionData) {
            $section = $completeGlossary->sections()->create([
                'label'      => $sectionData['label'],
            ]);

            foreach ($sectionData['items'] as $ii => $item) {
                $section->items()->create([
                    'title'      => $item['title'],
                    'body'       => $item['body'],
                ]);
            }
        }

        // ---- ABOUT NUMONIX --------------------------------------------------
        AboutNumoniX::query()->delete();

        $about = AboutNumoniX::create([
            'title'   => 'About NumoniX',
            'heading' => 'Welcome to NumoniX',
        ]);

        $aboutSections = [
            [
                'heading'     => 'Functional Numeric Harmony (FNH)',
                'description' => "Functional Numeric Harmony (FNH) is a revolutionary approach to understanding music -- one that replaces years of complex traditional study with a clear, intuitive system based on function and flow.\n\nWhere traditional theory focuses on memorising scales, chord names, rules and exceptions, FNH teaches you to feel and see harmony as movement -- how each sound functions within a progression.\n\nBy focusing on why music moves rather than what the notes are, FNH empowers you to:",
                'items'       => [
                    ['body' => 'Compose and improvise with confidence and clarity.'],
                    ['body' => 'Understand harmony visually and intuitively.'],
                    ['body' => 'Analyse songs and create smooth, expressive transitions.'],
                ],
            ],
            [
                'heading'     => 'The Future of NumoniX',
                'description' => '',
                'items'       => [
                    ['body' => 'The Songwriting Lab -- apply FNH directly in creative composition.'],
                    ['body' => 'AI Flow Analyser -- upload songs to see their harmonic flow visualised.'],
                ],
            ],
        ];

        foreach ($aboutSections as $si => $sectionData) {
            $section = $about->sections()->create([
                'heading'     => $sectionData['heading'],
                'description' => $sectionData['description'] ?? null,
            ]);

            foreach ($sectionData['items'] as $ii => $item) {
                $section->items()->create([
                    'title'      => '',
                    'body'       => $item['body'],
                ]);
            }
        }
    }
}
