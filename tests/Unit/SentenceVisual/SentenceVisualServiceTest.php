<?php

namespace Tests\Unit\SentenceVisual;

use App\Services\SentenceVisual\SentenceVisualService;
use InvalidArgumentException;
use Tests\TestCase;

class SentenceVisualServiceTest extends TestCase
{
    public function test_it_accepts_canonical_reframe_cells(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'id' => 'sb_017',
            'label' => 'T reframed as D',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
            ],
            'tap_to_play' => true,
            'show_root_toggle' => true,
            'show_reframe_labels' => true,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_a',
                    'beat' => 3,
                    'functions' => [
                        ['world' => 'T#', 'reframe_role' => 'old', 'notes' => [['number' => '3#']]],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => [['number' => '2']]],
                    ],
                    'reframe' => [
                        'variant' => 'A',
                        'roots' => [
                            'old' => ['degree' => '1', 'pitch' => 'A#'],
                            'new' => ['degree' => '1', 'pitch' => 'D'],
                        ],
                    ],
                ]],
            ]],
        ]);

        $this->assertSame('A', $block['rows'][0]['cells'][0]['reframe']['variant']);
        $this->assertArrayNotHasKey('playback', $block);
        $this->assertArrayNotHasKey('colour_notes', $block);
    }

    public function test_it_accepts_variant_b_paired_mini_cells(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Variant B paired mini-cells',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
            ],
            'tap_to_play' => true,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T#', 'reframe_role' => 'old', 'notes' => [['number' => '3#']]],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => [['number' => '2']]],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => [
                            'old' => ['degree' => '1', 'pitch' => 'A#'],
                            'new' => ['degree' => '1', 'pitch' => 'D'],
                        ],
                    ],
                ]],
            ]],
        ]);

        $this->assertSame('B', $block['rows'][0]['cells'][0]['reframe']['variant']);
        $this->assertSame('old', $block['rows'][0]['cells'][0]['functions'][0]['reframe_role']);
        $this->assertSame('new', $block['rows'][0]['cells'][0]['functions'][1]['reframe_role']);
    }

    public function test_it_accepts_held_mode_full_controls_from_client_visual(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'id' => 'sb_held_full_controls',
            'label' => 'Held chord with full controls',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
                ['label' => '5'],
                ['label' => '6'],
                ['label' => '7'],
                ['label' => '8'],
                ['label' => '1'],
            ],
            'tap_to_play' => true,
            'show_root_toggle' => true,
            'show_reframe_labels' => true,
            'rows' => [[
                'mode' => 'held',
                'segments' => [
                    ['world' => 'T#', 'beat_from' => 0, 'beat_to' => 3, 'weight' => 4, 'notes' => '1 3# 5', 'root' => ['degree' => '1', 'pitch' => 'A#']],
                    ['world' => 'S#', 'beat_from' => 4, 'beat_to' => 5, 'weight' => 2, 'notes' => '4 6 1', 'root' => ['degree' => '1', 'pitch' => 'E']],
                    ['world' => 'D', 'beat_from' => 6, 'beat_to' => 7, 'weight' => 2, 'notes' => '7 2 4', 'root' => ['degree' => '1', 'pitch' => 'D']],
                    ['world' => 'T#', 'beat_from' => 8, 'beat_to' => 8, 'weight' => 1, 'notes' => '1 3# 5', 'root' => ['degree' => '1', 'pitch' => 'A#']],
                ],
            ]],
        ]);

        $this->assertArrayNotHasKey('mode', $block['rows'][0]); // mode is now at root level, not per-row
        $this->assertTrue($block['tap_to_play']);
        $this->assertTrue($block['show_root_toggle']);
        $this->assertCount(4, $block['rows'][0]['segments']);
        $this->assertSame('S#', $block['rows'][0]['segments'][1]['world']);
    }

    public function test_it_rejects_non_canonical_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SentenceVisualService::class)->format([
            'title' => 'Old payload',
            'rows' => [['beats' => [['type' => 'chord']]]],
        ]);
    }

    public function test_canonical_output_has_no_color_fields(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'No color test',
            'beats' => [['label' => '1'], ['label' => '2']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [
                    [
                        'type' => 'normal',
                        'beat' => 1,
                        'functions' => [
                            ['world' => 'T', 'notes' => [['number' => '1'], ['number' => '3'], ['number' => '5']]],
                        ],
                    ],
                    [
                        'type' => 'empty',
                        'beat' => 2,
                        'functions' => [],
                    ],
                ],
            ]],
        ]);

        // Functions should NOT have func_color, sup_color, or highlighted_notes
        $function = $block['rows'][0]['cells'][0]['functions'][0];
        $this->assertArrayNotHasKey('func_color', $function);
        $this->assertArrayNotHasKey('sup_color', $function);
        $this->assertArrayNotHasKey('highlighted_notes', $function);

        // Cells should NOT have bg_color or bottom_bar_color
        $cell = $block['rows'][0]['cells'][0];
        $this->assertArrayNotHasKey('bg_color', $cell);
        $this->assertArrayNotHasKey('bottom_bar_color', $cell);
    }

    public function test_notes_are_objects_with_number_key(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Notes format test',
            'beats' => [['label' => '1'], ['label' => '2']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'normal',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'D', 'notes' => [['number' => '5'], ['number' => '7'], ['number' => '2']]],
                    ],
                ]],
            ]],
        ]);

        $notes = $block['rows'][0]['cells'][0]['functions'][0]['notes'];
        $this->assertCount(3, $notes);
        $this->assertSame('5', $notes[0]['number']);
        $this->assertSame('7', $notes[1]['number']);
        $this->assertSame('2', $notes[2]['number']);
        // Each note should be an object, not a string
        $this->assertIsArray($notes[0]);
    }

    public function test_pair_v_b_cells_have_proper_reframe_structure(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Pair V-B test',
            'beats' => [['label' => '1'], ['label' => '2'], ['label' => '3']],
            'tap_to_play' => true,
            'show_root_toggle' => false,
            'show_reframe_labels' => true,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => [['number' => '1'], ['number' => '3'], ['number' => '5']]],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => [['number' => '5'], ['number' => '7'], ['number' => '2']]],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => [
                            'old' => ['degree' => null, 'pitch' => null],
                            'new' => ['degree' => null, 'pitch' => null],
                        ],
                    ],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertSame('pair_v_b', $cell['type']);
        $this->assertSame(1, $cell['beat']);
        $this->assertCount(2, $cell['functions']);
        $this->assertSame('B', $cell['reframe']['variant']);
        $this->assertSame('old', $cell['functions'][0]['reframe_role']);
        $this->assertSame('new', $cell['functions'][1]['reframe_role']);
        // Reframe roots should include degree and pitch
        $this->assertArrayHasKey('degree', $cell['reframe']['roots']['old']);
        $this->assertArrayHasKey('pitch', $cell['reframe']['roots']['old']);
        $this->assertArrayHasKey('degree', $cell['reframe']['roots']['new']);
        $this->assertArrayHasKey('pitch', $cell['reframe']['roots']['new']);
        // No color fields on cell
        $this->assertArrayNotHasKey('bg_color', $cell);
        $this->assertArrayNotHasKey('bottom_bar_color', $cell);
        // No color fields on functions
        foreach ($cell['functions'] as $fn) {
            $this->assertArrayNotHasKey('func_color', $fn);
            $this->assertArrayNotHasKey('sup_color', $fn);
            $this->assertArrayNotHasKey('highlighted_notes', $fn);
        }
    }

    public function test_beats_are_label_only_objects(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Beat format test',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
            ],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [
                    ['beat' => 1, 'functions' => [['world' => 'T', 'notes' => []]]],
                    ['beat' => 2, 'functions' => [['world' => 'S', 'notes' => []]]],
                    ['beat' => 3, 'functions' => [['world' => 'D', 'notes' => []]]],
                ],
            ]],
        ]);

        $this->assertCount(3, $block['beats']);
        $this->assertSame('1', $block['beats'][0]['label']);
        $this->assertArrayNotHasKey('strong', $block['beats'][0]);
        $this->assertSame('2', $block['beats'][1]['label']);
        $this->assertArrayNotHasKey('strong', $block['beats'][1]);
        $this->assertSame('3', $block['beats'][2]['label']);
        $this->assertArrayNotHasKey('strong', $block['beats'][2]);
    }

    public function test_normal_cell_has_no_reframe(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Normal cell test',
            'beats' => [['label' => '1'], ['label' => '2']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'normal',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'S', 'notes' => [['number' => '4'], ['number' => '6']]],
                    ],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertSame('normal', $cell['type']);
        $this->assertSame(1, $cell['beat']);
        $this->assertCount(1, $cell['functions']);
        $this->assertArrayNotHasKey('reframe', $cell);
        $this->assertSame('S', $cell['functions'][0]['world']);
    }

    public function test_empty_cell_has_no_functions_and_no_reframe(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Empty cell test',
            'beats' => [['label' => '1'], ['label' => '2']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'empty',
                    'beat' => 2,
                    'functions' => [],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertSame('empty', $cell['type']);
        $this->assertCount(0, $cell['functions']);
        $this->assertArrayNotHasKey('reframe', $cell);
        $this->assertArrayNotHasKey('bg_color', $cell);
        $this->assertArrayNotHasKey('bottom_bar_color', $cell);
    }

    public function test_string_notes_are_normalized_to_objects(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'String notes normalization',
            'beats' => [['label' => '1'], ['label' => '2']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'normal',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'notes' => '1 3 5'],
                    ],
                ]],
            ]],
        ]);

        $notes = $block['rows'][0]['cells'][0]['functions'][0]['notes'];
        $this->assertCount(3, $notes);
        $this->assertSame('1', $notes[0]['number']);
        $this->assertSame('3', $notes[1]['number']);
        $this->assertSame('5', $notes[2]['number']);
    }

    public function test_title_description_mode_are_promoted_when_not_null(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Promoted fields test',
            'title' => 'My Title',
            'description' => 'My Description',
            'mode' => 'cells',
            'beats' => [['label' => '1']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [
                    ['beat' => 1, 'functions' => [['world' => 'T', 'notes' => []]]],
                ],
            ]],
        ]);

        $this->assertArrayHasKey('title', $block);
        $this->assertSame('My Title', $block['title']);
        $this->assertArrayHasKey('description', $block);
        $this->assertSame('My Description', $block['description']);
        $this->assertArrayHasKey('mode', $block);
        $this->assertSame('cells', $block['mode']);
    }

    public function test_title_description_are_empty_string_when_null(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'No promoted fields',
            'beats' => [['label' => '1']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [
                    ['beat' => 1, 'functions' => [['world' => 'T', 'notes' => []]]],
                ],
            ]],
        ]);

        // title and description are ALWAYS present (default to empty string)
        $this->assertArrayHasKey('title', $block);
        $this->assertSame('', $block['title']);
        $this->assertArrayHasKey('description', $block);
        $this->assertSame('', $block['description']);
        // mode is still conditional — omitted when null
        $this->assertArrayNotHasKey('mode', $block);
    }

    public function test_playback_label_is_preserved_in_canonical_output(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'playback_label' => 'Play example',
            'beats' => [['label' => '1'], ['label' => '2']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [
                    ['type' => 'normal', 'beat' => 1, 'functions' => []],
                    ['type' => 'empty', 'beat' => 2, 'functions' => []],
                ],
            ]],
        ]);

        $this->assertSame('Play example', $block['playback_label']);
    }

    public function test_start_from_cell_accepts_zero_and_valid_non_zero_index(): void
    {
        $zero = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2']],
            'rows' => [[
                'start_from_cell' => 0,
                'cells' => [
                    ['type' => 'normal', 'beat' => 1, 'functions' => []],
                    ['type' => 'empty', 'beat' => 2, 'functions' => []],
                ],
            ]],
        ]);

        $valid = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2'], ['label' => '3']],
            'rows' => [[
                'start_from_cell' => 1,
                'cells' => [
                    ['type' => 'normal', 'beat' => 1, 'functions' => []],
                    ['type' => 'empty', 'beat' => 2, 'functions' => []],
                    ['type' => 'normal', 'beat' => 3, 'functions' => []],
                ],
            ]],
        ]);

        $this->assertArrayNotHasKey('start_from_cell', $zero['rows'][0]);
        $this->assertSame(1, $valid['rows'][0]['start_from_cell']);
    }

    public function test_start_from_cell_rejects_an_index_at_or_after_the_row_end(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('start_from_cell');

        app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2']],
            'rows' => [[
                'start_from_cell' => 2,
                'cells' => [
                    ['type' => 'normal', 'beat' => 1, 'functions' => []],
                    ['type' => 'empty', 'beat' => 2, 'functions' => []],
                ],
            ]],
        ]);
    }

    public function test_held_segment_notes_string_is_normalized(): void
    {
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Held segment notes',
            'beats' => [['label' => '1'], ['label' => '2'], ['label' => '3'], ['label' => '4']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'held',
                'segments' => [
                    ['world' => 'T', 'beat_from' => 0, 'beat_to' => 3, 'weight' => 4, 'notes' => '1 3 5'],
                ],
            ]],
        ]);

        $notes = $block['rows'][0]['segments'][0]['notes'];
        $this->assertCount(3, $notes);
        $this->assertSame('1', $notes[0]['number']);
        $this->assertSame('3', $notes[1]['number']);
        $this->assertSame('5', $notes[2]['number']);
    }

    public function test_color_fields_from_input_are_stripped_in_output(): void
    {
        // Even if input has color fields, they should NOT appear in output
        $block = app(SentenceVisualService::class)->format([
            'type' => 'sentence_visual',
            'label' => 'Color stripping test',
            'beats' => [['label' => '1']],
            'tap_to_play' => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'normal',
                    'beat' => 1,
                    'bg_color' => '#ff0000',
                    'bottom_bar_color' => '#00ff00',
                    'functions' => [[
                        'world' => 'T',
                        'notes' => [['number' => '1']],
                        'func_color' => '#ff0000',
                        'sup_color' => '#00ff00',
                        'highlighted_notes' => [],
                    ]],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertArrayNotHasKey('bg_color', $cell);
        $this->assertArrayNotHasKey('bottom_bar_color', $cell);

        $function = $cell['functions'][0];
        $this->assertArrayNotHasKey('func_color', $function);
        $this->assertArrayNotHasKey('sup_color', $function);
        $this->assertArrayNotHasKey('highlighted_notes', $function);
    }
}
