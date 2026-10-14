<?php

namespace Database\Seeders;

use App\Models\Learning;
use App\Models\LearningPage;
use App\Models\LearningPageElement;
use App\Models\LearningGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LearningSeeder extends Seeder
{
    private const CATEGORY_META = [
        'lesson' => [
            'group_label'  => 'Module 1: Core Lessons',
            'group_desc'   => 'Foundational lessons on harmonic function.',
            'titles'       => ['Introduction to Functions', 'The Fixed Numeric Row', 'Functional Movement'],
            'subtitles'    => ['Understanding T, S, and D', 'Mapping Notes to Numbers', 'How Harmony Creates Motion'],
        ],
        'core_theory' => [
            'group_label'  => 'Module 1: Core Theory',
            'group_desc'   => 'Essential music theory through the FNH lens.',
            'titles'       => ['Chord Families Explained', 'Intervals and Their Functions', 'The Harmonic Series'],
            'subtitles'    => ['Grouping Chords by Function', 'Distance as Tension', 'Natural Roots of Harmony'],
        ],
        'advanced_insight' => [
            'group_label'  => 'Module 1: Advanced Insights',
            'group_desc'   => 'Advanced harmonic techniques for experienced listeners.',
            'titles'       => ['Secondary Dominants', 'Modal Interchange', 'Chromatic Mediant Relations'],
            'subtitles'    => ['Borrowing Tension from Adjacent Keys', 'Mixing Parallel Modes', 'Unexpected Colour Shifts'],
        ],
        'case_study' => [
            'group_label'  => 'Module 1: Case Studies',
            'group_desc'   => 'Real-song analysis using the FNH framework.',
            'titles'       => ['Analysing a Pop Progression', 'Jazz ii-V-I in FNH', 'Blues Form Decoded'],
            'subtitles'    => ['Spotting T-S-D in the Wild', 'Tension Cycles in Jazz', 'The Blues Functional Loop'],
        ],
        'fnh_architecture' => [
            'group_label'  => 'Module 1: FNH Architecture',
            'group_desc'   => 'The structural building blocks of the FNH system.',
            'titles'       => ['Centre Identity and Form', 'Functional Flow Patterns', 'Template Form Tables'],
            'subtitles'    => ['Defining the Harmonic Centre', 'Mapping Directional Movement', 'Reading T and T# Forms'],
        ],
    ];

    /**
     * Real asset pools - files already uploaded via the admin CMS.
     * Stored as storage-relative paths so the model accessors
     * (Learning::thumbnail_image_url, LearningPageElement::file_url)
     * resolve them to absolute URLs at response time.
     *
     * NOTE: These files live in storage/app/public (gitignored) and must
     * exist on the target environment (they are the same CMS-uploaded
     * files present on the production server). If a fresh environment is
     * missing them, the seeded URLs will 404 - upload the matching
     * learnings/ and lessons/ files (or re-save a learning via the admin
     * CMS) before going live.
     */
    private const THUMBNAIL_POOL = [
        '/storage/learnings/thumbnails/9GgDpLC6TkOW0JRCaHSuDjIFq0XVpW4kj1mO0poA.jpg',
        '/storage/lessons/thumbnails/2WRhHuqxpSV374l0HpaKJSZOvNiWpYrMOqRHYn9x.png',
        '/storage/lessons/thumbnails/3QgDIOX2veLgCQN4homqvgGnyIPzxMAin0j49ZYW.png',
        '/storage/lessons/thumbnails/8Ed73HhYmtgoeGmJhtY0Kane2uzowHoqBl9xcFTn.png',
        '/storage/lessons/thumbnails/ADxJZglLqPifd5crd8t8c8YsGyHAMH3BJY2RvMrx.png',
        '/storage/lessons/thumbnails/k8VtDeJJyGV8kdb9nYALkKUbqFo1pPJ1q9cMIFxi.png',
        '/storage/lessons/thumbnails/uvCKu6oxleXIx1sVY2Lgs81rwx6xrWpx2SB1ozfi.png',
    ];

    private const IMAGE_POOL = [
        '/storage/learnings/elements/images/Gi56sVlBYg9rdGxU1ZMd3dyXKYojuxfKSJhYQuY0.jpg',
        '/storage/learnings/elements/images/gIO5zevQvPGA2IARoKr5zTYn26kAPTwFCapFz1cX.jpg',
        '/storage/learnings/elements/images/k9oEDNa95gFKVTzZKgML42rzp53fDxPjuMoGjkKx.jpg',
        '/storage/lessons/contents/images/0cbiglmBaOwqewFbJTp3s9oiKXpVNQ8pCEkVsReu.png',
        '/storage/lessons/contents/images/2Mg2ZbB8DIX8DJ5tXJrtiuClOGLB2pwHSrXdL70R.png',
        '/storage/lessons/contents/images/dIowRTF9rtA63SGlOb64B4ePXkIjMXlqEfRP66z8.png',
        '/storage/lessons/contents/images/F4WGLfCSsUyQMiNab8YtQ6ck1S2Rl2ZJ48fCqfT2.png',
        '/storage/lessons/contents/images/gmDih5ba2jyonaWn6C1ZIFbfWFofs7lTjaH5g04V.png',
        '/storage/lessons/contents/images/rkxyaybjWFuJEJJ9M9B48Mw0yGZQW288QYKz2K10.png',
        '/storage/lessons/contents/images/xfuY4JTxIFmiV94o0zu38EkZwj4VahOHI634YPMc.png',
    ];

    private const AUDIO_POOL = [
        '/storage/learnings/elements/images/GwW5wlwx1vkKG99BsZw4BbEPm8qmzNE6xtpVYnCX.mp3',
        '/storage/lessons/contents/audios/bI8rZfPETtYg6OkHgLkQyQGR2kDQ7hQb8dTjIPRF.mp3',
        '/storage/lessons/contents/audios/edRQHoMq7OAf84A0wYzhBPEmHgPOoKbOrS6KQ2oN.mp3',
        '/storage/lessons/contents/audios/F2jvPy6kJIfEeEydWzGX7LJ8SlGOdVi9gmgUefYF.mp3',
        '/storage/lessons/contents/audios/NFeoBjLUtXV2Ht8t7W8mq7kJrb49icbmDNNjurP0.mp3',
        '/storage/lessons/contents/audios/vY4hNXZtvvizuVuMbfnNjaGSgzZ8SMKEB4Mu8RgZ.mp3',
        '/storage/lessons/contents/audios/w5kVDexyHw0ZNW4yJckNuH95O380a5ET08xx33mR.mp3',
        '/storage/lessons/contents/audios/zKo1UjuL2gE3s5AvehIecP6prNadye7KF75375E8.mp3',
    ];

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        LearningPageElement::truncate();
        LearningPage::truncate();
        Learning::truncate();
        LearningGroup::truncate();
        Schema::enableForeignKeyConstraints();

        $categoryIndex = 0;
        foreach (self::CATEGORY_META as $category => $meta) {
            $this->seedCategory($category, $meta, $categoryIndex);
            $categoryIndex++;
        }
    }

    private function seedCategory(string $category, array $meta, int $categoryIndex): void
    {
        $group = LearningGroup::create([
            'category'    => $category,
            'label'       => $meta['group_label'],
            'description' => $meta['group_desc'],
            'color'       => '#0B4F86',
            'is_premium'  => false,
        ]);

        $isFnhCategory = in_array($category, LearningPageElement::FNH_CATEGORIES, true);

        for ($i = 0; $i < 3; $i++) {
            // Deterministic per-learning asset index so each of the 15
            // learnings gets varied real thumbnails / images / audio.
            $assetIndex = ($categoryIndex * 3) + $i;

            $learning = Learning::create([
                'category'            => $category,
                'group_id'            => $group->id,
                'title'               => $meta['titles'][$i],
                'subtitle'            => $meta['subtitles'][$i],
                'slug'                => Str::slug($meta['titles'][$i]),
                'summary'             => "Key Takeaway 1: Harmony creates motion.\nKey Takeaway 2: Tension resolves to rest.\nKey Takeaway 3: Structure defines meaning.",
                'read_time'           => '5 min read',
                'thumbnail_image_url' => $this->thumbnailAsset($assetIndex),
                'status'              => 'active',
                'is_published'        => true,
                'is_guest_accessible' => null,
            ]);

            for ($p = 1; $p <= 2; $p++) {
                $page = LearningPage::create([
                    'learning_id' => $learning->id,
                    'page_number' => $p,
                    'title'       => $p === 1 ? 'Core Concepts' : 'Practical Applications',
                ]);

                $elements = $isFnhCategory
                    ? $this->generateFnhPageElements($page->id, $p, $assetIndex)
                    : $this->generateStandardPageElements($page->id, $p, $assetIndex);

                $pos = 1;
                foreach ($elements as $el) {
                    $el['position'] = $pos++;
                    LearningPageElement::create($el);
                }
            }
        }
    }

    /* ------------------------------------------------------------------
     * Standard page elements (lesson / core_theory / advanced_insight)
     * ------------------------------------------------------------------ */

    private function generateStandardPageElements(int $pageId, int $pageNum, int $assetIndex): array
    {
        if ($pageNum === 1) {
            return $this->standardPage1($pageId, $assetIndex);
        }

        return $this->standardPage2($pageId, $assetIndex);
    }

    private function standardPage1(int $pageId, int $assetIndex): array
    {
        return [
            $this->makeElement($pageId, 'title', ['text' => 'The Role of the Tonic']),
            $this->makeElement($pageId, 'subtitle', ['text' => 'Establishing the Harmonic Home']),
            $this->makeElement($pageId, 'section_label', ['text' => 'CORE CONCEPT']),
            $this->makeElement($pageId, 'text', ['text' => 'In functional harmony, the Tonic (T) provides a sense of rest and resolution. It acts as the gravitational centre of any progression. Every chord movement in tonal music can be understood relative to this anchor point.']),
            $this->makeElement($pageId, 'bullet_list', [
                'items' => [
                    'The Tonic is the most stable function in any key.',
                    'It is typically the first and last chord of a resolved phrase.',
                    'It provides harmonic relief from the tension of the Dominant.',
                ],
            ]),
            $this->makeElement($pageId, 'checklist', [
                'items' => [
                    ['id' => 1, 'text' => 'Identify the Tonic chord in a given progression.'],
                    ['id' => 2, 'text' => 'Listen for the resolution from Dominant to Tonic.'],
                    ['id' => 3, 'text' => 'Recognise when Subdominant departs from Tonic.'],
                ],
            ]),
            $this->makeElement($pageId, 'table', [
                'columns' => [
                    ['key' => 'function', 'label' => 'Function'],
                    ['key' => 'feeling',  'label' => 'Emotional Quality'],
                    ['key' => 'core_note','label' => 'Core Note'],
                ],
                'rows' => [
                    [
                        'function' => ['text' => 'Tonic (T)', 'bold' => true],
                        'feeling'  => ['text' => 'Rest / Home'],
                        'core_note'=> ['text' => '3'],
                    ],
                    [
                        'function' => ['text' => 'Subdominant (S)', 'bold' => true],
                        'feeling'  => ['text' => 'Motion / Departure'],
                        'core_note'=> ['text' => '6'],
                    ],
                    [
                        'function' => ['text' => 'Dominant (D)', 'bold' => true],
                        'feeling'  => ['text' => 'Tension / Drive'],
                        'core_note'=> ['text' => '5'],
                    ],
                ],
                'col_colors' => [
                    '#DBEAFE', // Blue-100: Function column
                    '#DCFCE7', // Green-100: Emotional Quality column
                    '#FEE2E2', // Red-100: Core Note column
                ],
            ]),
            $this->makeElement($pageId, 'info_box', ['text' => 'Remember: Functional harmony relies on the dynamic relationship between tension and release. The T-S-D-T cycle is the engine of Western tonal music.']),
            $this->makeElement($pageId, 'key_insight', ['text' => 'Harmonic movement is not just about chords - it is about emotional storytelling. Every function has a role in that story.']),
            $this->makeElement($pageId, 'image', [
                'url'     => $this->imageAsset($assetIndex),
                'caption' => 'Visual representation of harmonic gravity and the T-S-D-T cycle.',
            ], $this->imageAsset($assetIndex)),
        ];
    }

    private function standardPage2(int $pageId, int $assetIndex): array
    {
        return [
            $this->makeElement($pageId, 'section_label', ['text' => 'LISTEN & APPLY']),
            $this->makeElement($pageId, 'transparent_image', [
                'url'     => $this->imageAsset($assetIndex + 1),
                'caption' => 'A transparent diagram of the functional cycle showing directional movement.',
            ], $this->imageAsset($assetIndex + 1)),
            $this->makeElement($pageId, 'audio', [
                'url'     => $this->audioAsset($assetIndex),
                'caption' => 'Listen to a full T-S-D-T progression in a major key.',
            ], $this->audioAsset($assetIndex)),
            $this->makeElement($pageId, 'sentence_visual', $this->buildSentenceVisualPayload()),
            $this->makeElement($pageId, 'sentence_visual', $this->buildMultiRowCellsPayload()),
            $this->makeElement($pageId, 'sentence_visual', $this->buildEmptyCellsPayload()),
            $this->makeElement($pageId, 'table', [
                'columns' => [
                    ['key' => 'function', 'label' => 'Function'],
                    ['key' => 'character', 'label' => 'Character'],
                ],
                'rows' => [
                    [
                        'function'  => ['text' => 'Tonic (T)', 'bold' => true],
                        'character' => ['text' => 'Rest / Stability', 'bg_color' => '#F0FDF4'],
                    ],
                    [
                        'function'  => ['text' => 'Subdominant (S)', 'bold' => true],
                        'character' => ['text' => 'Motion / Departure', 'bg_color' => '#FFF7ED'],
                    ],
                    [
                        'function'  => ['text' => 'Dominant (D)', 'bold' => true],
                        'character' => ['text' => 'Tension / Drive', 'bg_color' => '#FEF2F2'],
                    ],
                ],
                'col_colors' => [
                    '#EFF6FF', // Blue-50: Function column
                    '#FEF9C3', // Yellow-50: Character column
                ],
            ]),
        ];
    }

    /* ------------------------------------------------------------------
     * FNH page elements (fnh_architecture / case_study)
     * Covers all 10 FNH_CASE_STUDY_TYPES + 9 FNH_SHARED_STANDARD_TYPES
     * ------------------------------------------------------------------ */

    private function generateFnhPageElements(int $pageId, int $pageNum, int $assetIndex): array
    {
        if ($pageNum === 1) {
            return $this->fnhPage1($pageId);
        }

        return $this->fnhPage2($pageId, $assetIndex);
    }

    private function fnhPage1(int $pageId): array
    {
        return [
            $this->makeElement($pageId, 'title', ['text' => 'FNH Architecture - System Overview']),
            $this->makeElement($pageId, 'text', ['text' => 'Functional Numeric Harmony (FNH) provides a system-neutral lens for analysing music. Instead of traditional letter names, every note is given a fixed numeric identity that expresses its relationship to the tonal centre.']),
            $this->makeElement($pageId, 'key_insight', ['text' => 'FNH reveals the psychological intent behind every chord choice by stripping away culturally arbitrary labels.']),
            $this->makeElement($pageId, 'table', [
                'columns' => [
                    ['key' => 'form',  'label' => 'Form'],
                    ['key' => 'centre','label' => 'Centre'],
                    ['key' => 'quality','label' => 'Quality'],
                ],
                'rows' => [
                    [
                        'form'    => ['text' => 'T1',  'bold' => true],
                        'centre'  => ['text' => 'Tonic Major'],
                        'quality' => ['text' => 'Stable & Bright', 'bg_color' => '#DCFCE7'],
                    ],
                    [
                        'form'    => ['text' => 'T6',  'bold' => true],
                        'centre'  => ['text' => 'Relative Minor'],
                        'quality' => ['text' => 'Stable & Dark', 'bg_color' => '#F3E8FF'],
                    ],
                    [
                        'form'    => ['text' => 'T#1', 'bold' => true],
                        'centre'  => ['text' => 'Parallel Major'],
                        'quality' => ['text' => 'Shifted & Bright', 'bg_color' => '#FEF3C7'],
                    ],
                ],
                'row_colors' => [
                    '#EFF6FF', // Blue-50: T1 (Tonic Major) row
                    '#F5F3FF', // Violet-50: T6 (Relative Minor) row
                    '#FFF7ED', // Orange-50: T#1 (Parallel Major) row
                ],
            ]),
            $this->makeElement($pageId, 'info_box', ['text' => 'Each FNH form maps to a specific emotional and structural role in any tonal context, regardless of key signature.']),
            $this->makeElement($pageId, 'what_fnh_reveals', ['text' => 'FNH reveals the underlying emotional drivers of chord progressions, exposing the raw tension-and-release mechanics that letter-name notation obscures.']),
            $this->makeElement($pageId, 'template_form_table', [
                'columns' => [
                    ['key' => 'col1', 'label' => 'TEMPLATE'],
                    ['key' => 'col2', 'label' => 'T FORM'],
                    ['key' => 'col3', 'label' => 'T# FORM'],
                ],
                'rows' => [
                    ['col1' => 'Major',       'col2' => 'T1',   'col3' => 'T#1'],
                    ['col1' => 'Minor',       'col2' => 'T6',   'col3' => 'T#6'],
                    ['col1' => 'Dominant 7',  'col2' => 'D1',   'col3' => 'D#1'],
                    ['col1' => 'Minor 7',     'col2' => 'S2',   'col3' => 'S#2'],
                ],
            ]),
            $this->makeElement($pageId, 'defining_behaviours_card', [
                'title'      => '6.3 Tension Climb',
                'behaviours' => [
                    'Creates intense upward harmonic momentum.',
                    'Most commonly heard before a powerful chorus.',
                    'Requires a strong Dominant resolution to land effectively.',
                ],
            ]),
            $this->makeElement($pageId, 'movement_quality_table', [
                'columns' => [
                    ['key' => 'col1', 'label' => 'MOVEMENT'],
                    ['key' => 'col2', 'label' => 'EMOTIONAL QUALITY'],
                    ['key' => 'col3', 'label' => 'FROM T'],
                    ['key' => 'col4', 'label' => 'FROM T#'],
                ],
                'rows' => [
                    ['col1' => 'T -> S',   'col2' => 'Expanding, departing',  'col3' => 'Common departure', 'col4' => 'Colour shift'],
                    ['col1' => 'S -> D',   'col2' => 'Building tension',       'col3' => 'Classic ascent',   'col4' => 'Dramatic push'],
                    ['col1' => 'D -> T',   'col2' => 'Resolving, landing',     'col3' => 'Full resolution',  'col4' => 'Grounded return'],
                ],
            ]),
        ];
    }

    private function fnhPage2(int $pageId, int $assetIndex): array
    {
        return [
            $this->makeElement($pageId, 'section_heading', ['text' => 'Centre Identity & Form Reference']),
            $this->makeElement($pageId, 'centre_character_table', [
                'columns' => [
                    ['key' => 'col1', 'label' => 'CENTRE'],
                    ['key' => 'col2', 'label' => 'CHARACTER'],
                    ['key' => 'col3', 'label' => 'WHAT HOME FEELS LIKE'],
                ],
                'rows' => [
                    ['col1' => 'Tonic Major',   'col2' => 'The protagonist',  'col3' => 'Open and secure'],
                    ['col1' => 'Relative Minor', 'col2' => 'The shadow self', 'col3' => 'Melancholic rest'],
                    ['col1' => 'Parallel Minor', 'col2' => 'The dark twin',   'col3' => 'Weighted stability'],
                ],
            ]),
            $this->makeElement($pageId, 'functional_flow', [
                'flow' => 'T -> S -> D -> T',
                'steps' => [
                    ['label' => 'T', 'color' => '#3B82F6'],
                    ['label' => 'S', 'color' => '#16A34A'],
                    ['label' => 'D', 'color' => '#DC2626'],
                    ['label' => 'T', 'color' => '#3B82F6'],
                ],
            ]),
            $this->makeElement($pageId, 'exploring_section', [
                'title' => 'Exploring the Verse Form',
                'tags'  => ['T', 'T#', 'S'],
            ]),
            $this->makeElement($pageId, 'sentence_visual', $this->buildReframeSentenceVisualPayload()),
            $this->makeElement($pageId, 'sentence_visual', $this->buildMultiRowHeldPayload()),
            $this->makeElement($pageId, 'sentence_visual', $this->buildPairVariantBPayload()),
            $this->makeElement($pageId, 'centre_identity_card', [
                'title'       => 'The Tonic Centre',
                'description' => 'The gravitational foundation of the key. Everything departs from and returns to this point.',
                'image_url'   => $this->imageAsset($assetIndex + 2),
            ]),
            $this->makeElement($pageId, 'form_identity_card', [
                'title'       => 'T Form (Major Root)',
                'description' => 'The major Tonic form. Bright, stable, and forward-facing.',
                'image_url'   => $this->imageAsset($assetIndex + 3),
            ]),
            $this->makeElement($pageId, 'sentence_visual', $this->buildHeldSentenceVisualPayload()),
            $this->makeElement($pageId, 'table', [
                'columns' => [
                    ['key' => 'interval', 'label' => 'Interval'],
                    ['key' => 'semitones','label' => 'Semitones'],
                    ['key' => 'quality',  'label' => 'Quality'],
                    ['key' => 'fnh_world','label' => 'FNH World'],
                ],
                'rows' => [
                    [
                        'interval'  => ['text' => 'Unison', 'bold' => true],
                        'semitones' => ['text' => '0'],
                        'quality'   => ['text' => 'Perfect', 'bg_color' => '#DCFCE7'],
                        'fnh_world' => ['text' => 'T', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Minor 2nd', 'bold' => true],
                        'semitones' => ['text' => '1'],
                        'quality'   => ['text' => 'Minor', 'bg_color' => '#FEE2E2'],
                        'fnh_world' => ['text' => 'D♭', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Major 2nd', 'bold' => true],
                        'semitones' => ['text' => '2'],
                        'quality'   => ['text' => 'Major', 'bg_color' => '#DBEAFE'],
                        'fnh_world' => ['text' => 'D', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Minor 3rd', 'bold' => true],
                        'semitones' => ['text' => '3'],
                        'quality'   => ['text' => 'Minor', 'bg_color' => '#FEE2E2'],
                        'fnh_world' => ['text' => 'S♭', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Major 3rd', 'bold' => true],
                        'semitones' => ['text' => '4'],
                        'quality'   => ['text' => 'Major', 'bg_color' => '#DBEAFE'],
                        'fnh_world' => ['text' => 'S', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Perfect 4th', 'bold' => true],
                        'semitones' => ['text' => '5'],
                        'quality'   => ['text' => 'Perfect', 'bg_color' => '#DCFCE7'],
                        'fnh_world' => ['text' => 'S#', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Tritone', 'bold' => true],
                        'semitones' => ['text' => '6'],
                        'quality'   => ['text' => 'Diminished', 'bg_color' => '#FEF9C3'],
                        'fnh_world' => ['text' => 'D', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Perfect 5th', 'bold' => true],
                        'semitones' => ['text' => '7'],
                        'quality'   => ['text' => 'Perfect', 'bg_color' => '#DCFCE7'],
                        'fnh_world' => ['text' => 'T', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Minor 6th', 'bold' => true],
                        'semitones' => ['text' => '8'],
                        'quality'   => ['text' => 'Minor', 'bg_color' => '#FEE2E2'],
                        'fnh_world' => ['text' => 'T♭', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Major 6th', 'bold' => true],
                        'semitones' => ['text' => '9'],
                        'quality'   => ['text' => 'Major', 'bg_color' => '#DBEAFE'],
                        'fnh_world' => ['text' => 'S', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Minor 7th', 'bold' => true],
                        'semitones' => ['text' => '10'],
                        'quality'   => ['text' => 'Minor', 'bg_color' => '#FEE2E2'],
                        'fnh_world' => ['text' => 'D♭', 'bold' => true],
                    ],
                    [
                        'interval'  => ['text' => 'Major 7th', 'bold' => true],
                        'semitones' => ['text' => '11'],
                        'quality'   => ['text' => 'Major', 'bg_color' => '#DBEAFE'],
                        'fnh_world' => ['text' => 'D', 'bold' => true],
                    ],
                ],
                'row_colors' => [
                    '#F8FAFC', // even rows subtle slate
                    '#FFFFFF',
                    '#F8FAFC',
                    '#FFFFFF',
                    '#F8FAFC',
                    '#FFFFFF',
                    '#F8FAFC',
                    '#FFFFFF',
                    '#F8FAFC',
                    '#FFFFFF',
                    '#F8FAFC',
                    '#FFFFFF',
                ],
            ]),
            $this->makeElement($pageId, 'image', [
                'url'     => $this->imageAsset($assetIndex + 4),
                'caption' => 'Diagram: Tonic centre expansions across T and T# territories.',
            ], $this->imageAsset($assetIndex + 4)),
            $this->makeElement($pageId, 'transparent_image', [
                'url'     => $this->imageAsset($assetIndex + 5),
                'caption' => 'Overlay: functional flow arrows showing directional movement.',
            ], $this->imageAsset($assetIndex + 5)),
        ];
    }

    /* ------------------------------------------------------------------
     * Helper methods
     * ------------------------------------------------------------------ */

    private function makeElement(int $pageId, string $type, array $data, ?string $fileUrl = null): array
    {
        $element = [
            'learning_page_id' => $pageId,
            'type'             => $type,
            'data'             => $data,
        ];

        if ($fileUrl !== null) {
            $element['file_url'] = $fileUrl;
        }

        return $element;
    }

    private function thumbnailAsset(int $index): string
    {
        return self::THUMBNAIL_POOL[$index % count(self::THUMBNAIL_POOL)];
    }

    private function imageAsset(int $index): string
    {
        return self::IMAGE_POOL[$index % count(self::IMAGE_POOL)];
    }

    private function audioAsset(int $index): string
    {
        return self::AUDIO_POOL[$index % count(self::AUDIO_POOL)];
    }

    private function buildSentenceVisualPayload(): array
    {
        return [
            'type'               => 'sentence_visual',
            'id'                 => 'sb_prim_cycle',
            'mode'               => 'cells',
            'beat_numbering_mode'=> 'restart_at_8',
            'title'              => 'T-S-D-T Cycle',
            'description'        => 'Observe how the harmonic tension builds across four beats and resolves back to home.',
            'label'              => 'Primary Functional Cycle',
            'tap_to_play'        => false,
            'show_root_toggle'   => false,
            'show_reframe_labels'=> true,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'rows' => [
                [
                    'mode'  => 'cells',
                    'cells' => [
                        $this->canonicalCell(1, 'normal', 'T', [1, 3, 5]),
                        $this->canonicalCell(2, 'normal', 'S', [4, 6, 1]),
                        $this->canonicalCell(3, 'normal', 'D', [5, 7, 2]),
                        $this->canonicalCell(4, 'normal', 'T', [1, 3, 5]),
                    ],
                ],
            ],
        ];
    }

    private function buildReframeSentenceVisualPayload(): array
    {
        return [
            'type'               => 'sentence_visual',
            'id'                 => 'sb_reframe_a',
            'mode'               => 'cells',
            'beat_numbering_mode'=> 'restart_at_8',
            'title'              => 'T♯ Reframe - Parallel Shift',
            'description'        => 'A T major chord reframed as D of the parallel key, creating a sudden upward shift.',
            'label'              => 'Reframe: T -> D♯',
            'tap_to_play'        => true,
            'show_root_toggle'   => true,
            'show_reframe_labels'=> true,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
            ],
            'rows' => [[
                'mode'  => 'cells',
                'cells' => [
                    $this->canonicalCell(1, 'normal', 'T', [1, 3, 5]),
                    $this->canonicalCell(2, 'normal', 'S', [4, 6, 1]),
                    [
                        'type'  => 'pair_v_a',
                        'beat'  => 3,
                        'functions' => [
                            ['world' => 'T#', 'reframe_role' => 'old', 'notes' => [['number' => '3#']]],
                            ['world' => 'D',  'reframe_role' => 'new', 'notes' => [['number' => '2']]],
                        ],
                        'reframe' => [
                            'variant' => 'A',
                            'roots' => [
                                'old' => ['degree' => '1', 'pitch' => 'A#'],
                                'new' => ['degree' => '1', 'pitch' => 'D'],
                            ],
                        ],
                        'editable' => true,
                        'allowable_notes' => ['1', '3', '5', '3#'],
                        'centre_voicing' => 'T#1',
                        'octave_range' => 2,
                    ],
                ],
            ]],
        ];
    }

    private function buildHeldSentenceVisualPayload(): array
    {
        return [
            'type'               => 'sentence_visual',
            'id'                 => 'sb_held_tonic',
            'mode'               => 'held',
            'title'              => 'Held Tonic Pedal',
            'description'        => 'A sustained Tonic chord held across the bar while the upper voices shift.',
            'label'              => 'Held Tonic',
            'tap_to_play'        => true,
            'show_root_toggle'   => true,
            'show_reframe_labels'=> false,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
                ['label' => '5'],
                ['label' => '6'],
                ['label' => '7'],
                ['label' => '8'],
            ],
            'rows' => [[
                'mode'     => 'held',
                'segments' => [
                    [
                        'world'    => 'T',
                        'beat_from'=> 0,
                        'beat_to'  => 3,
                        'weight'   => 4,
                        'notes'    => '1 3 5',
                    ],
                    [
                        'world'    => 'S',
                        'beat_from'=> 4,
                        'beat_to'  => 5,
                        'weight'   => 2,
                        'notes'    => '4 6 1',
                    ],
                    [
                        'world'    => 'D',
                        'beat_from'=> 6,
                        'beat_to'  => 7,
                        'weight'   => 2,
                        'notes'    => '7 2 4',
                    ],
                ],
            ]],
        ];
    }

    /* ------------------------------------------------------------------
     * New varied sentence_visual payloads
     * ------------------------------------------------------------------ */

    /**
     * Multi-row cells: two voices (bass + chords) on 4 beats.
     * Row 1 is a bass-line movement T->S->D->T.
     * Row 2 is a chord layer with different note sets on the same beat grid.
     * Tests stacked cell rendering and multiple canonical rows.
     */
    private function buildMultiRowCellsPayload(): array
    {
        return [
            'type'               => 'sentence_visual',
            'id'                 => 'sb_multi_cells',
            'mode'               => 'cells',
            'title'              => 'Bass + Chords - Two Voices',
            'description'        => 'The bass anchors the harmonic root while the upper voice colours each function with fuller voicings.',
            'label'              => 'Two-Voice T-S-D-T',
            'tap_to_play'        => true,
            'show_root_toggle'   => true,
            'show_reframe_labels'=> true,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'rows' => [
                [
                    'mode'  => 'cells',
                    'label' => 'Bass',
                    'cells' => [
                        $this->canonicalCell(1, 'normal', 'T', [1]),
                        $this->canonicalCell(2, 'normal', 'S', [4]),
                        $this->canonicalCell(3, 'normal', 'D', [5]),
                        $this->canonicalCell(4, 'normal', 'T', [1]),
                    ],
                ],
                [
                    'mode'  => 'cells',
                    'label' => 'Chords',
                    'cells' => [
                        $this->canonicalCell(1, 'normal', 'T', [1, 3, 5]),
                        $this->canonicalCell(2, 'normal', 'S', [4, 6, 1]),
                        $this->canonicalCell(3, 'normal', 'D', [5, 7, 2]),
                        $this->canonicalCell(4, 'normal', 'T', [1, 3, 5]),
                    ],
                ],
            ],
        ];
    }

    /**
     * Multi-row held: two sustained layers on 4 beats.
     * Row 1 is a wide pedal-point T held across all 4 beats.
     * Row 2 shifts in smaller segments: T(0-1) -> S(2-2) -> D(3-3).
     * Tests multiple held rows with different segment granularities.
     */
    private function buildMultiRowHeldPayload(): array
    {
        return [
            'type'               => 'sentence_visual',
            'id'                 => 'sb_multi_held',
            'mode'               => 'held',
            'title'              => 'Pedal Point - Sustained & Shifting',
            'description'        => 'A deep Tonic pedal sustains across the bar while the inner voices change functional colour above it.',
            'label'              => 'Pedal with Shifting Upper Voice',
            'tap_to_play'        => true,
            'show_root_toggle'   => true,
            'show_reframe_labels'=> false,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'rows' => [
                [
                    'mode'     => 'held',
                    'label'    => 'Pedal',
                    'segments' => [
                        [
                            'world'    => 'T',
                            'beat_from'=> 0,
                            'beat_to'  => 3,
                            'weight'   => 4,
                            'notes'    => '1 5',
                        ],
                    ],
                ],
                [
                    'mode'     => 'held',
                    'label'    => 'Inner Voice',
                    'segments' => [
                        [
                            'world'    => 'T',
                            'beat_from'=> 0,
                            'beat_to'  => 1,
                            'weight'   => 2,
                            'notes'    => '3',
                        ],
                        [
                            'world'    => 'S',
                            'beat_from'=> 2,
                            'beat_to'  => 2,
                            'weight'   => 1,
                            'notes'    => '4 6',
                        ],
                        [
                            'world'    => 'D',
                            'beat_from'=> 3,
                            'beat_to'  => 3,
                            'weight'   => 1,
                            'notes'    => '7 2',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Pair variant B reframe: uses type 'pair_v_b' with a flat-reframe flavour.
     * 3 beats. Beat 1: normal T. Beat 2: normal S. Beat 3: pair_v_b
     * where T# reframes as D♭ (flat-side reframe).
     * Complements the existing pair_v_a example.
     */
    private function buildPairVariantBPayload(): array
    {
        return [
            'type'               => 'sentence_visual',
            'id'                 => 'sb_pair_v_b',
            'mode'               => 'cells',
            'beat_numbering_mode'=> 'continuous',
            'title'              => 'T♯ -> D♭ - Variant B Reframe',
            'description'        => 'A sharp-origin reframe resolves to the flat side, creating a darkening shift in harmonic colour.',
            'label'              => 'Reframe: T♯ -> D♭ (Variant B)',
            'tap_to_play'        => true,
            'show_root_toggle'   => false,
            'show_reframe_labels'=> true,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
            ],
            'rows' => [[
                'mode'  => 'cells',
                'cells' => [
                    $this->canonicalCell(1, 'normal', 'T', [1, 3, 5]),
                    $this->canonicalCell(2, 'normal', 'S', [4, 6, 1]),
                    [
                        'type'  => 'pair_v_b',
                        'beat'  => 3,
                        'functions' => [
                            ['world' => 'T#', 'reframe_role' => 'old', 'notes' => [['number' => '3#']]],
                            ['world' => 'D♭', 'reframe_role' => 'new', 'notes' => [['number' => '2♭']]],
                        ],
                        'reframe' => [
                            'variant' => 'B',
                            'roots' => [
                                'old' => ['degree' => '1', 'pitch' => 'C#'],
                                'new' => ['degree' => '1', 'pitch' => 'F'],
                            ],
                        ],
                        'editable' => false,
                        'allowable_notes' => ['1', '3', '5', '2♭'],
                        'centre_voicing' => 'D♭1',
                        'octave_range' => 1,
                    ],
                ],
            ]],
        ];
    }

    /**
     * Empty cells: a 4-beat row where beats 2 and 4 are rests (empty).
     * Tests that the Flutter client renders silence/gaps correctly.
     * Beat 1: normal T with triad. Beat 3: normal D with dyad.
     * Beats 2 & 4: empty (function omitted, type = empty).
     */
    private function buildEmptyCellsPayload(): array
    {
        return [
            'type'               => 'sentence_visual',
            'id'                 => 'sb_empty_cells',
            'mode'               => 'cells',
            'title'              => 'Alternating Hits & Rests',
            'description'        => 'A syncopated pattern where the harmony sounds only on beats 1 and 3, with rests on 2 and 4.',
            'label'              => 'Syncopated T-D',
            'tap_to_play'        => false,
            'show_root_toggle'   => false,
            'show_reframe_labels'=> true,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'rows' => [[
                'mode'  => 'cells',
                'cells' => [
                    $this->canonicalCell(1, 'normal', 'T', [1, 3, 5]),
                    [
                        'beat'      => 2,
                        'type'      => 'empty',
                        'functions' => [
                            ['world' => 'T', 'notes' => []],
                        ],
                    ],
                    $this->canonicalCell(3, 'normal', 'D', [5, 7]),
                    [
                        'beat'      => 4,
                        'type'      => 'empty',
                        'functions' => [
                            ['world' => 'T', 'notes' => []],
                        ],
                    ],
                ],
            ]],
        ];
    }

    private function canonicalCell(int $beat, string $type, string $world, array $noteNumbers): array
    {
        return [
            'beat'      => $beat,
            'type'      => $type,
            'functions' => [
                [
                    'world' => $world,
                    'notes' => array_map(fn ($n) => ['number' => (string) $n], $noteNumbers),
                ],
            ],
        ];
    }
}
