<?php

namespace Tests\Unit\SentenceVisual;

use App\Services\SentenceVisual\SentenceVisualMapper;
use InvalidArgumentException;
use Tests\TestCase;

class SentenceVisualMapperTest extends TestCase
{
    private SentenceVisualMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = app(SentenceVisualMapper::class);
    }

    // ─── Pair V-B canonical output ───────────────────────────────────────

    public function test_pair_v_b_has_correct_type_and_structure(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2']],
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
    }

    public function test_custom_function_world_and_superscript_are_preserved(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'rows' => [[
                'label' => 'BASS',
                'cells' => [[
                    'type' => 'normal',
                    'beat' => 4,
                    'functions' => [[
                        'world' => 'TS#',
                        'superscript' => '3',
                        'notes' => [
                            ['number' => '1'],
                            ['number' => '3'],
                            ['number' => '5'],
                            ['number' => '6#'],
                        ],
                    ]],
                ]],
            ]],
        ]);

        $function = $block['rows'][0]['cells'][0]['functions'][0];

        $this->assertSame('TS#', $function['world']);
        $this->assertSame('3', $function['superscript']);
        $this->assertSame('6#', $function['notes'][3]['number']);
    }

    public function test_pair_v_b_output_excludes_obsolete_fields(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'bg_color' => '#ff0000',
                    'bottom_bar_color' => '#00ff00',
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => [['number' => '1']], 'func_color' => '#ff0000', 'sup_color' => '#00ff00', 'highlighted_notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => [['number' => '5']], 'func_color' => '#0000ff', 'sup_color' => '#ffff00', 'highlighted_notes' => ['1']],
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
        $this->assertArrayNotHasKey('bg_color', $cell);
        $this->assertArrayNotHasKey('bottom_bar_color', $cell);

        foreach ($cell['functions'] as $fn) {
            $this->assertArrayNotHasKey('func_color', $fn);
            $this->assertArrayNotHasKey('sup_color', $fn);
            $this->assertArrayNotHasKey('highlighted_notes', $fn);
        }
    }

    public function test_pair_v_b_reframe_has_default_roots(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
                    ],
                ]],
            ]],
        ]);

        $roots = $block['rows'][0]['cells'][0]['reframe']['roots'];
        $this->assertSame('B', $block['rows'][0]['cells'][0]['reframe']['variant']);
        $this->assertSame(['degree' => null, 'pitch' => null], $roots['old']);
        $this->assertSame(['degree' => null, 'pitch' => null], $roots['new']);
    }

    // ─── Pair V-A canonical output ───────────────────────────────────────

    public function test_pair_v_a_has_correct_type_and_structure(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_a',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => [['number' => '1']]],
                        ['world' => 'S', 'reframe_role' => 'new', 'notes' => [['number' => '4']]],
                    ],
                    'reframe' => [
                        'variant' => 'A',
                        'roots' => [
                            'old' => ['degree' => '1', 'pitch' => 'C'],
                            'new' => ['degree' => '1', 'pitch' => 'F'],
                        ],
                    ],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertSame('pair_v_a', $cell['type']);
        $this->assertSame(1, $cell['beat']);
        $this->assertCount(2, $cell['functions']);
        $this->assertSame('A', $cell['reframe']['variant']);
        $this->assertSame('C', $cell['reframe']['roots']['old']['pitch']);
        $this->assertSame('F', $cell['reframe']['roots']['new']['pitch']);
    }

    public function test_pair_v_a_defaults_when_reframe_not_provided(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_a',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
                    ],
                ]],
            ]],
        ]);

        $reframe = $block['rows'][0]['cells'][0]['reframe'];
        $this->assertSame('A', $reframe['variant']);
        $this->assertSame(['degree' => null, 'pitch' => null], $reframe['roots']['old']);
        $this->assertSame(['degree' => null, 'pitch' => null], $reframe['roots']['new']);
    }

    // ─── Legacy type inference ───────────────────────────────────────────

    public function test_two_functions_without_type_infers_pair_v_b(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
                    ],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertSame('pair_v_b', $cell['type']);
        $this->assertSame('B', $cell['reframe']['variant']);
    }

    public function test_two_functions_with_variant_a_infers_pair_v_a(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'S', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'A',
                    ],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertSame('pair_v_a', $cell['type']);
        $this->assertSame('A', $cell['reframe']['variant']);
    }

    // ─── Function world values ───────────────────────────────────────────

    public function test_pair_functions_accept_all_worlds(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2'], ['label' => '3'], ['label' => '4']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T#', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D#', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => [
                            'old' => ['degree' => null, 'pitch' => null],
                            'new' => ['degree' => null, 'pitch' => null],
                        ],
                    ],
                ], [
                    'type' => 'pair_v_b',
                    'beat' => 2,
                    'functions' => [
                        ['world' => 'Db', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'S#', 'reframe_role' => 'new', 'notes' => []],
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

        $cells = $block['rows'][0]['cells'];
        $this->assertSame('T#', $cells[0]['functions'][0]['world']);
        $this->assertSame('D#', $cells[0]['functions'][1]['world']);
        $this->assertSame('Db', $cells[1]['functions'][0]['world']);
        $this->assertSame('S#', $cells[1]['functions'][1]['world']);
    }

    // ─── Notes format ─────────────────────────────────────────────────────

    public function test_pair_notes_can_be_array_of_number_objects(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
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

        $fnA = $block['rows'][0]['cells'][0]['functions'][0];
        $fnB = $block['rows'][0]['cells'][0]['functions'][1];

        $this->assertCount(3, $fnA['notes']);
        $this->assertSame('1', $fnA['notes'][0]['number']);
        $this->assertSame('3', $fnA['notes'][1]['number']);
        $this->assertSame('5', $fnA['notes'][2]['number']);

        $this->assertCount(3, $fnB['notes']);
        $this->assertSame('5', $fnB['notes'][0]['number']);
        $this->assertSame('7', $fnB['notes'][1]['number']);
        $this->assertSame('2', $fnB['notes'][2]['number']);
    }

    public function test_pair_notes_can_be_string_notes(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => '1 3 5'],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => '5 7 2'],
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

        $fnA = $block['rows'][0]['cells'][0]['functions'][0];
        $fnB = $block['rows'][0]['cells'][0]['functions'][1];

        $this->assertCount(3, $fnA['notes']);
        $this->assertSame('1', $fnA['notes'][0]['number']);
        $this->assertSame('3', $fnA['notes'][1]['number']);
        $this->assertSame('5', $fnA['notes'][2]['number']);

        $this->assertCount(3, $fnB['notes']);
        $this->assertSame('5', $fnB['notes'][0]['number']);
        $this->assertSame('7', $fnB['notes'][1]['number']);
        $this->assertSame('2', $fnB['notes'][2]['number']);
    }

    public function test_pair_notes_can_be_array_of_strings(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => ['1', '3#', '5']],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => ['5', '7', '2']],
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

        $fnA = $block['rows'][0]['cells'][0]['functions'][0];
        $this->assertCount(3, $fnA['notes']);
        $this->assertSame('3#', $fnA['notes'][1]['number']);
    }

    // ─── Edge cases ──────────────────────────────────────────────────────

    public function test_pair_beat_number_is_clamped_to_minimum_one(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 0,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
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

        $this->assertSame(1, $block['rows'][0]['cells'][0]['beat']);
    }

    public function test_multiple_pair_cells_in_single_row(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2'], ['label' => '3']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => ['old' => ['degree' => null, 'pitch' => null], 'new' => ['degree' => null, 'pitch' => null]],
                    ],
                ], [
                    'type' => 'pair_v_a',
                    'beat' => 2,
                    'functions' => [
                        ['world' => 'S', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'T', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'A',
                        'roots' => ['old' => ['degree' => null, 'pitch' => null], 'new' => ['degree' => null, 'pitch' => null]],
                    ],
                ], [
                    'type' => 'pair_v_b',
                    'beat' => 3,
                    'functions' => [
                        ['world' => 'D', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'T', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => ['old' => ['degree' => null, 'pitch' => null], 'new' => ['degree' => null, 'pitch' => null]],
                    ],
                ]],
            ]],
        ]);

        $cells = $block['rows'][0]['cells'];
        $this->assertCount(3, $cells);
        $this->assertSame('pair_v_b', $cells[0]['type']);
        $this->assertSame(1, $cells[0]['beat']);
        $this->assertSame('pair_v_a', $cells[1]['type']);
        $this->assertSame(2, $cells[1]['beat']);
        $this->assertSame('pair_v_b', $cells[2]['type']);
        $this->assertSame(3, $cells[2]['beat']);
    }

    public function test_mixed_normal_and_pair_cells_in_single_row(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1'], ['label' => '2'], ['label' => '3']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'normal',
                    'beat' => 1,
                    'functions' => [['world' => 'T', 'notes' => [['number' => '1']]]],
                ], [
                    'type' => 'pair_v_b',
                    'beat' => 2,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => ['old' => ['degree' => null, 'pitch' => null], 'new' => ['degree' => null, 'pitch' => null]],
                    ],
                ], [
                    'type' => 'empty',
                    'beat' => 3,
                    'functions' => [],
                ]],
            ]],
        ]);

        $cells = $block['rows'][0]['cells'];
        $this->assertCount(3, $cells);
        $this->assertSame('normal', $cells[0]['type']);
        $this->assertArrayNotHasKey('reframe', $cells[0]);
        $this->assertSame('pair_v_b', $cells[1]['type']);
        $this->assertArrayHasKey('reframe', $cells[1]);
        $this->assertCount(2, $cells[1]['functions']);
        $this->assertSame('empty', $cells[2]['type']);
        $this->assertArrayNotHasKey('reframe', $cells[2]);
        $this->assertCount(0, $cells[2]['functions']);
    }

    public function test_pair_cell_label_is_omitted_when_empty(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => ['old' => ['degree' => null, 'pitch' => null], 'new' => ['degree' => null, 'pitch' => null]],
                    ],
                ]],
            ]],
        ]);

        $this->assertArrayNotHasKey('label', $block['rows'][0]);
    }

    public function test_pair_cell_label_is_included_when_set(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'label' => 'My Reframe Row',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [
                        ['world' => 'T', 'reframe_role' => 'old', 'notes' => []],
                        ['world' => 'D', 'reframe_role' => 'new', 'notes' => []],
                    ],
                    'reframe' => [
                        'variant' => 'B',
                        'roots' => ['old' => ['degree' => null, 'pitch' => null], 'new' => ['degree' => null, 'pitch' => null]],
                    ],
                ]],
            ]],
        ]);

        $this->assertSame('My Reframe Row', $block['rows'][0]['label']);
    }

    public function test_pair_reframe_roots_preserved_when_provided(): void
    {
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
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
                        'variant' => 'A',
                        'roots' => [
                            'old' => ['degree' => '1', 'pitch' => 'A#'],
                            'new' => ['degree' => '1', 'pitch' => 'D'],
                        ],
                    ],
                ]],
            ]],
        ]);

        $roots = $block['rows'][0]['cells'][0]['reframe']['roots'];
        $this->assertSame('1', $roots['old']['degree']);
        $this->assertSame('A#', $roots['old']['pitch']);
        $this->assertSame('1', $roots['new']['degree']);
        $this->assertSame('D', $roots['new']['pitch']);
    }

    // ─── Empty functions array ───────────────────────────────────────────

    public function test_pair_cell_with_empty_functions_keeps_type_but_has_no_reframe(): void
    {
        // Edge case: type explicitly says pair but functions are empty.
        // The type is preserved, but reframe is omitted since there are
        // no functions to distinguish old/new.
        $block = $this->mapper->toCanonical([
            'type' => 'sentence_visual',
            'beats' => [['label' => '1']],
            'rows' => [[
                'mode' => 'cells',
                'cells' => [[
                    'type' => 'pair_v_b',
                    'beat' => 1,
                    'functions' => [],
                ]],
            ]],
        ]);

        $cell = $block['rows'][0]['cells'][0];
        $this->assertSame('pair_v_b', $cell['type']);
        $this->assertCount(0, $cell['functions']);
        $this->assertArrayNotHasKey('reframe', $cell);
    }

    // ─── Canonical type marker validation ─────────────────────────────────

    public function test_invalid_type_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be canonical type sentence_visual');

        $this->mapper->toCanonical([
            'type' => 'old_format',
            'beats' => [],
            'rows' => [],
        ]);
    }

    public function test_missing_beats_or_rows_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires canonical beats and rows arrays');

        $this->mapper->toCanonical([
            'type' => 'sentence_visual',
        ]);
    }
}
