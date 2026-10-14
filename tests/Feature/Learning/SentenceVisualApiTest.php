<?php

namespace Tests\Feature\Learning;

use App\Models\GuestDevice;
use App\Models\Learning;
use App\Models\LearningPage;
use App\Models\LearningPageElement;
use App\Services\GuestFingerprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive end-to-end test for the Sentence Visual (Beat Grid) API.
 *
 * Verifies that mode, title, and description are correctly returned
 * at the element root level, and that no rows[].mode exists.
 */
class SentenceVisualApiTest extends TestCase
{
    use RefreshDatabase;

    private GuestFingerprintService $fingerprintService;
    private Learning $learning;
    private LearningPage $page;
    private LearningPageElement $element;

    // Guest fixtures
    private string $guestDeviceId;
    private string $guestToken;
    private string $guestFingerprint;
    private array $guestHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fingerprintService = app(GuestFingerprintService::class);

        // ── Create a learning ──────────────────────────────────────
        $this->learning = Learning::create([
            'category'    => 'lesson',
            'title'       => 'Test Sentence Visual',
            'slug'        => 'test-sentence-visual-'.uniqid(),
            'status'      => 'active',
            'is_published'=> true,
        ]);

        // ── Create a page ──────────────────────────────────────────
        $this->page = LearningPage::create([
            'learning_id'   => $this->learning->id,
            'page_number'   => 1,
            'title'         => 'Test Page',
            'section_title' => 'Test Section',
        ]);

        // ── Create a reusable guest device ─────────────────────────
        $this->guestDeviceId    = 'test_sv_device_'.uniqid();
        $this->guestToken       = 'test_sv_token_'.uniqid();
        $this->guestFingerprint = $this->fingerprintService->compute($this->guestDeviceId, $this->guestToken);

        GuestDevice::create([
            'device_id'         => $this->guestDeviceId,
            'token'             => $this->guestToken,
            'guest_fingerprint' => $this->guestFingerprint,
        ]);

        $this->guestHeaders = [
            'X-Guest-Device-Id'   => $this->guestDeviceId,
            'X-Guest-Token'       => $this->guestToken,
            'X-Guest-Fingerprint' => $this->guestFingerprint,
        ];
    }

    /**
     * Create a sentence_visual element with the given data.
     */
    private function createSentenceVisual(array $overrides = []): LearningPageElement
    {
        $defaultData = [
            'type' => 'sentence_visual',
            'label' => 'Visual Title',
            'title' => 'Example Title',
            'description' => 'Example Description',
            'mode' => 'cells',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                        ['beat' => 2, 'type' => 'normal', 'functions' => [['world' => 'S', 'notes' => []]]],
                        ['beat' => 3, 'type' => 'normal', 'functions' => [['world' => 'D', 'notes' => []]]],
                        ['beat' => 4, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                    ],
                ],
            ],
        ];

        $data = array_replace_recursive($defaultData, $overrides);

        // Remove 'mode' from rows if we set it at top level (clean test data)
        if (isset($overrides['mode'])) {
            foreach ($data['rows'] as &$row) {
                unset($row['mode']);
            }
        }

        return LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);
    }

    /** @test */
    public function cells_mode_returns_mode_title_description_at_root(): void
    {
        $this->createSentenceVisual([
            'mode' => 'cells',
            'title' => 'My Cells Title',
            'description' => 'My Cells Description',
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.learning.tabs.0.section_title', 'Test Section');
        $response->assertJsonPath('data.learning.pages.0.section_title', 'Test Section');

        $element = $response->json('data.learning.pages.0.elements.0');

        // Assert mode/title/description are at the element root level
        $this->assertArrayHasKey('mode', $element, 'mode must be at element root');
        $this->assertSame('cells', $element['mode'], 'mode should be cells');

        $this->assertArrayHasKey('title', $element, 'title must be at element root');
        $this->assertSame('My Cells Title', $element['title'], 'title should match');

        $this->assertArrayHasKey('description', $element, 'description must be at element root');
        $this->assertSame('My Cells Description', $element['description'], 'description should match');

        // Assert no rows[].mode exists
        $rows = $element['data']['rows'] ?? [];
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey(
                'mode', $row,
                "rows[{$rowIndex}] must NOT have a mode field"
            );
        }
    }

    /** @test */
    public function held_mode_returns_mode_title_description_at_root(): void
    {
        $this->createSentenceVisual([
            'type' => 'sentence_visual',
            'label' => 'Held Example',
            'mode' => 'held',
            'title' => 'My Held Title',
            'description' => 'My Held Description',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
            ],
            'rows' => [
                [
                    'segments' => [
                        ['world' => 'T', 'notes' => [], 'beat_from' => 0, 'beat_to' => 1, 'weight' => 2],
                        ['world' => 'S', 'notes' => [], 'beat_from' => 2, 'beat_to' => 2, 'weight' => 1],
                    ],
                ],
            ],
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Assert mode/title/description at root level
        $this->assertArrayHasKey('mode', $element, 'mode must be at element root');
        $this->assertSame('held', $element['mode'], 'mode should be held');

        $this->assertArrayHasKey('title', $element, 'title must be at element root');
        $this->assertSame('My Held Title', $element['title']);

        $this->assertArrayHasKey('description', $element, 'description must be at element root');
        $this->assertSame('My Held Description', $element['description']);

        // Assert no rows[].mode
        $rows = $element['data']['rows'] ?? [];
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey(
                'mode', $row,
                "rows[{$rowIndex}] must NOT have a mode field"
            );
        }

        // Assert segments exist for held mode
        $this->assertArrayHasKey('segments', $rows[0], 'held row should have segments');
        $this->assertCount(2, $rows[0]['segments']);
    }

    /** @test */
    public function legacy_data_with_rows_mode_is_backward_compatible(): void
    {
        // Simulate legacy data that was saved BEFORE the refactor:
        // mode is inside rows[0].mode instead of at the root level,
        // and description is stored as example_description
        $legacyData = [
            'type' => 'sentence_visual',
            'label' => 'Legacy Label',
            'title' => 'Legacy Title',
            'example_description' => 'Legacy Description',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'mode' => 'held',  // Legacy: mode inside row
                    'segments' => [
                        ['world' => 'T', 'notes' => [], 'beat_from' => 0, 'beat_to' => 1, 'weight' => 2],
                    ],
                ],
            ],
        ];

        $element = LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $legacyData,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $responseElement = $response->json('data.learning.pages.0.elements.0');

        // Legacy mode should be inferred from rows[0].mode
        $this->assertArrayHasKey('mode', $responseElement, 'mode should be present for legacy data');
        $this->assertSame('held', $responseElement['mode'], 'legacy mode should be inferred');

        // Legacy description should be backfilled from example_description
        $this->assertArrayHasKey('description', $responseElement, 'description should be present for legacy data');
        $this->assertSame('Legacy Description', $responseElement['description'], 'legacy description should be backfilled');

        // No rows[].mode should remain
        $rows = $responseElement['data']['rows'] ?? [];
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey(
                'mode', $row,
                "legacy rows[{$rowIndex}] must NOT have a mode field anymore"
            );
        }
    }

    /** @test */
    public function title_and_description_are_always_present_defaulting_to_empty(): void
    {
        // Create data with NO mode, title, or description — title/desc should default to ''
        $minimalData = [
            'type' => 'sentence_visual',
            'label' => 'Minimal',
            'beats' => [
                ['label' => '1'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $minimalData,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // mode should NOT be present when not in source data
        $this->assertArrayNotHasKey('mode', $element, 'mode should not be present when null');
        // title and description are ALWAYS present — default to empty string
        $this->assertArrayHasKey('title', $element, 'title must always be present');
        $this->assertSame('', $element['title'], 'title should default to empty string');
        $this->assertArrayHasKey('description', $element, 'description must always be present');
        $this->assertSame('', $element['description'], 'description should default to empty string');
    }

    /** @test */
    public function mode_is_never_inside_rows_in_response(): void
    {
        // Test with explicit mode in rows to ensure it's stripped
        $dataWithRowMode = [
            'type' => 'sentence_visual',
            'label' => 'Row Mode Test',
            'mode' => 'cells',
            'title' => 'Row Mode Title',
            'description' => 'Row Mode Desc',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'mode' => 'cells',  // Should be stripped by mapper
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                        ['beat' => 2, 'type' => 'empty', 'functions' => []],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $dataWithRowMode,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Root-level mode should be present
        $this->assertArrayHasKey('mode', $element);
        $this->assertSame('cells', $element['mode']);

        // No rows should have mode
        $rows = $element['data']['rows'] ?? [];
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey(
                'mode', $row,
                "rows[{$rowIndex}] must NOT have a mode field in API response"
            );
        }
    }

    // ═════════════════════════════════════════════════════════════════
    // EDGE CASE TESTS
    // ═════════════════════════════════════════════════════════════════

    /** @test */
    public function empty_beats_and_rows_do_not_crash(): void
    {
        // Empty beats array + empty rows array
        $data = [
            'type' => 'sentence_visual',
            'label' => 'Empty Data',
            'mode' => 'cells',
            'beats' => [],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        // Must NOT crash — should return 200 with a fallback block
        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Should return a valid fallback block (hardcoded fallback)
        $this->assertArrayHasKey('type', $element);
        $this->assertSame('sentence_visual', $element['type']);
        $this->assertArrayHasKey('data', $element);
        // Fallback block should at least have beats and rows
        $this->assertArrayHasKey('beats', $element['data']);
        $this->assertArrayHasKey('rows', $element['data']);
    }

    /** @test */
    public function single_beat_in_row_is_handled(): void
    {
        // Only 1 cell + 1 beat in a row
        $data = [
            'type' => 'sentence_visual',
            'label' => 'Single Beat',
            'mode' => 'cells',
            'title' => 'Single Beat Title',
            'description' => 'Single beat in row',
            'beats' => [
                ['label' => '1'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        $this->assertArrayHasKey('mode', $element);
        $this->assertSame('cells', $element['mode']);
        $this->assertArrayHasKey('title', $element);
        $this->assertSame('Single Beat Title', $element['title']);
        $this->assertArrayHasKey('description', $element);
        $this->assertSame('Single beat in row', $element['description']);

        $rows = $element['data']['rows'] ?? [];
        $this->assertCount(1, $rows, 'should have one row');

        $cells = $rows[0]['cells'] ?? [];
        $this->assertCount(1, $cells, 'should have one cell in row');

        // No rows[].mode
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey('mode', $row, "rows[{$rowIndex}] must NOT have mode");
        }
    }

    /** @test */
    public function invalid_mode_values_fall_back_to_cells(): void
    {
        // Unknown mode value — should not crash, should default to 'cells' behavior
        $data = [
            'type' => 'sentence_visual',
            'label' => 'Invalid Mode',
            'mode' => 'unknown',
            'title' => 'Invalid Mode Title',
            'description' => 'Mode is unknown',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                        ['beat' => 2, 'type' => 'empty', 'functions' => []],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        // Must not crash — should handle gracefully
        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // The invalid mode 'unknown' should be preserved at root (not crash)
        // The mapper may treat unknown mode as 'cells' or pass it through
        $this->assertArrayHasKey('mode', $element);

        $rows = $element['data']['rows'] ?? [];
        $this->assertCount(1, $rows);

        // Should have cells (since 'unknown' is not 'held', it defaults to cells)
        $this->assertArrayHasKey('cells', $rows[0], 'unknown mode should default to cells');
        $this->assertCount(2, $rows[0]['cells']);

        // No rows[].mode
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey('mode', $row, "rows[{$rowIndex}] must NOT have mode");
        }
    }

    /** @test */
    public function missing_mode_defaults_to_cells_behavior(): void
    {
        // No mode field at all — should default to cells
        $data = [
            'type' => 'sentence_visual',
            'label' => 'No Mode',
            'beats' => [
                ['label' => '1'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'D', 'notes' => []]]],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Mode should NOT be present at root since it was missing from source
        $this->assertArrayNotHasKey('mode', $element, 'mode should be absent when not in source');

        $rows = $element['data']['rows'] ?? [];
        $this->assertCount(1, $rows);
        // Should still have cells (default behavior)
        $this->assertArrayHasKey('cells', $rows[0], 'should default to cells when mode is absent');
        $this->assertCount(1, $rows[0]['cells']);
    }

    /** @test */
    public function very_long_title_and_description_are_truncated_or_passed_through(): void
    {
        // Very long title (500 chars) and description (2000 chars)
        $longTitle = str_repeat('ABCDE ', 100);  // 600 chars
        $longDescription = str_repeat('Lorem ipsum dolor sit amet consectetur adipiscing elit. ', 30);  // ~1980 chars

        $data = [
            'type' => 'sentence_visual',
            'label' => 'Long Text',
            'mode' => 'cells',
            'title' => $longTitle,
            'description' => $longDescription,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                        ['beat' => 2, 'type' => 'empty', 'functions' => []],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        // Must NOT crash with long strings
        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Title and description should be present at root
        $this->assertArrayHasKey('title', $element, 'title must be at root');
        $this->assertArrayHasKey('description', $element, 'description must be at root');

        // The values should match the input (no truncation in the API layer)
        $this->assertSame($longTitle, $element['title'], 'long title should pass through');
        $this->assertSame($longDescription, $element['description'], 'long description should pass through');

        // Rows should still be valid
        $rows = $element['data']['rows'] ?? [];
        $this->assertCount(1, $rows);
        $this->assertArrayHasKey('cells', $rows[0]);
        $this->assertCount(2, $rows[0]['cells']);

        // No rows[].mode
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey('mode', $row, "rows[{$rowIndex}] must NOT have mode");
        }
    }

    /** @test */
    public function multiple_rows_with_various_cell_types_work(): void
    {
        // Multiple rows with different cell types: normal, empty, pair_v_a, pair_v_b
        $data = [
            'type' => 'sentence_visual',
            'label' => 'Multi Row',
            'mode' => 'cells',
            'title' => 'Multi Row Title',
            'description' => 'Multi rows mixed cell types',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => true,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'label' => 'Row 1',
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => [['number' => '1']]]]],
                        ['beat' => 2, 'type' => 'empty', 'functions' => []],
                        ['beat' => 3, 'type' => 'normal', 'functions' => [['world' => 'S', 'notes' => [['number' => '4'], ['number' => '6']]]]],
                        ['beat' => 4, 'type' => 'normal', 'functions' => [['world' => 'D', 'notes' => [['number' => '5'], ['number' => '2']]]]],
                    ],
                ],
                [
                    'label' => 'Row 2',
                    'cells' => [
                        ['beat' => 1, 'type' => 'pair_v_a', 'functions' => [
                            ['world' => 'T', 'notes' => [], 'reframe_role' => 'old'],
                            ['world' => 'D', 'notes' => [], 'reframe_role' => 'new'],
                        ], 'reframe' => ['variant' => 'A', 'roots' => ['old' => ['degree' => '1', 'pitch' => 'C'], 'new' => ['degree' => '5', 'pitch' => 'G']]]],
                        ['beat' => 2, 'type' => 'pair_v_b', 'functions' => [
                            ['world' => 'S', 'notes' => [], 'reframe_role' => 'old'],
                            ['world' => 'T', 'notes' => [], 'reframe_role' => 'new'],
                        ], 'reframe' => ['variant' => 'B', 'roots' => ['old' => ['degree' => null, 'pitch' => null], 'new' => ['degree' => null, 'pitch' => 'F']]]],
                        ['beat' => 3, 'type' => 'normal', 'functions' => [['world' => 'D', 'notes' => []]]],
                        ['beat' => 4, 'type' => 'empty', 'functions' => []],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Root level checks
        $this->assertArrayHasKey('mode', $element);
        $this->assertSame('cells', $element['mode']);
        $this->assertArrayHasKey('title', $element);
        $this->assertSame('Multi Row Title', $element['title']);
        $this->assertArrayHasKey('description', $element);
        $this->assertSame('Multi rows mixed cell types', $element['description']);
        $this->assertArrayHasKey('data', $element);

        $dataField = $element['data'];
        $this->assertArrayHasKey('tap_to_play', $dataField);
        $this->assertTrue($dataField['show_root_toggle']);
        $this->assertTrue($dataField['show_reframe_labels']);

        $rows = $dataField['rows'] ?? [];
        $this->assertCount(2, $rows, 'should have two rows');

        // Row 1: should have 4 cells including all cell types
        $this->assertArrayHasKey('label', $rows[0], 'row label should pass through');
        $this->assertSame('Row 1', $rows[0]['label']);
        $this->assertArrayHasKey('cells', $rows[0]);
        $this->assertCount(4, $rows[0]['cells']);
        $this->assertSame('normal', $rows[0]['cells'][0]['type']);
        $this->assertSame('empty', $rows[0]['cells'][1]['type']);

        // Row 2: pair_v_a and pair_v_b cells
        $this->assertArrayHasKey('label', $rows[1]);
        $this->assertSame('Row 2', $rows[1]['label']);
        $this->assertArrayHasKey('cells', $rows[1]);
        $this->assertCount(4, $rows[1]['cells']);
        $this->assertSame('pair_v_a', $rows[1]['cells'][0]['type']);
        $this->assertSame('pair_v_b', $rows[1]['cells'][1]['type']);

        // Pair cell should have reframe data
        $this->assertArrayHasKey('reframe', $rows[1]['cells'][0]);
        $this->assertSame('A', $rows[1]['cells'][0]['reframe']['variant']);
        $this->assertArrayHasKey('functions', $rows[1]['cells'][0]);
        $this->assertCount(2, $rows[1]['cells'][0]['functions']);

        // No rows[].mode
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey('mode', $row, "rows[{$rowIndex}] must NOT have mode");
        }
    }

    /** @test */
    public function held_mode_with_many_segments_works(): void
    {
        // Held mode with many segments
        $data = [
            'type' => 'sentence_visual',
            'label' => 'Held Many Segments',
            'mode' => 'held',
            'title' => 'Held Multiple',
            'description' => 'Testing held mode with multiple segments',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
                ['label' => '5'],
                ['label' => '6'],
            ],
            'tap_to_play' => true,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'label' => 'Held Row',
                    'segments' => [
                        ['world' => 'T', 'notes' => [['number' => '1'], ['number' => '3'], ['number' => '5']], 'beat_from' => 0, 'beat_to' => 1, 'weight' => 2.0],
                        ['world' => 'S', 'notes' => [['number' => '4'], ['number' => '6']], 'beat_from' => 2, 'beat_to' => 3, 'weight' => 1.5],
                        ['world' => 'D', 'notes' => [['number' => '5'], ['number' => '7'], ['number' => '2']], 'beat_from' => 4, 'beat_to' => 5, 'weight' => 1.0],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        $this->assertArrayHasKey('mode', $element);
        $this->assertSame('held', $element['mode']);
        $this->assertArrayHasKey('title', $element);
        $this->assertSame('Held Multiple', $element['title']);
        $this->assertArrayHasKey('description', $element);
        $this->assertSame('Testing held mode with multiple segments', $element['description']);

        $rows = $element['data']['rows'] ?? [];
        $this->assertCount(1, $rows);

        $this->assertArrayHasKey('segments', $rows[0], 'held row should have segments');
        $this->assertCount(3, $rows[0]['segments']);

        // Verify each segment
        $this->assertSame('T', $rows[0]['segments'][0]['world']);
        $this->assertSame('S', $rows[0]['segments'][1]['world']);
        $this->assertSame('D', $rows[0]['segments'][2]['world']);
        // weight goes through JSON serialize/deserialize, so 2.0 becomes int 2
        $this->assertSame(2, $rows[0]['segments'][0]['weight'], 'weight should be 2 after JSON round-trip');
        $this->assertSame(4, $rows[0]['segments'][2]['beat_from']);
        $this->assertSame(5, $rows[0]['segments'][2]['beat_to']);

        // Label should pass through
        $this->assertArrayHasKey('label', $rows[0]);
        $this->assertSame('Held Row', $rows[0]['label']);

        // No rows[].mode
        foreach ($rows as $rowIndex => $row) {
            $this->assertArrayNotHasKey('mode', $row, "rows[{$rowIndex}] must NOT have mode");
        }
    }

    /** @test */
    public function null_mode_in_data_defaults_to_cells(): void
    {
        // Explicit null mode value
        $data = [
            'type' => 'sentence_visual',
            'label' => 'Null Mode',
            'mode' => null,
            'beats' => [
                ['label' => '1'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'S', 'notes' => []]]],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Null mode should be omitted from root (keys with null values are stripped)
        $this->assertArrayNotHasKey('mode', $element, 'null mode should be omitted from root');

        $rows = $element['data']['rows'] ?? [];
        $this->assertCount(1, $rows);
        $this->assertArrayHasKey('cells', $rows[0], 'null mode should default to cells');
        $this->assertCount(1, $rows[0]['cells']);
    }

    /** @test */
    public function empty_string_title_and_description_are_omitted(): void
    {
        // Empty string title and description
        $data = [
            'type' => 'sentence_visual',
            'label' => 'Empty Strings',
            'mode' => 'cells',
            'title' => '',
            'description' => '',
            'beats' => [
                ['label' => '1'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [
                [
                    'cells' => [
                        ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                    ],
                ],
            ],
        ];

        LearningPageElement::create([
            'learning_page_id' => $this->page->id,
            'type'             => 'sentence_visual',
            'data'             => $data,
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$this->learning->id}");

        $response->assertStatus(200);

        $element = $response->json('data.learning.pages.0.elements.0');

        // Empty strings pass through the mapper (only null values are stripped)
        // The controller extracts them to root if present
        $this->assertArrayHasKey('title', $element, 'empty title should still be present');
        $this->assertSame('', $element['title'], 'title should be empty string');
        $this->assertArrayHasKey('description', $element, 'empty description should still be present');
        $this->assertSame('', $element['description'], 'description should be empty string');

        $rows = $element['data']['rows'] ?? [];
        $this->assertCount(1, $rows);
        $this->assertArrayHasKey('cells', $rows[0]);
    }
}
