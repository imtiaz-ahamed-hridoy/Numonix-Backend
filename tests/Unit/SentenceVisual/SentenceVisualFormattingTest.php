<?php

namespace Tests\Unit\SentenceVisual;

use App\Services\LearningElementFormatter;
use App\Services\SentenceVisual\SentenceVisualMapper;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Tests that beat:0 in canonical sentence_visual data does NOT crash
 * the formatting pipeline.
 *
 * Previously the SentenceVisualMapper passed beat:0 through as-is,
 * and the SentenceVisualValidator threw an InvalidArgumentException
 * because $beat < 1. That exception propagated up through
 * formatSentenceVisual() → LearningElementResource → LearningController::show(),
 * which caught it with catch(\Throwable) and returned "Learning not found" (404).
 *
 * Fixes applied:
 *   1. SentenceVisualMapper now clamps beat to max(1, ...) so beat:0 → 1
 *   2. formatSentenceVisual() wraps SentenceVisualService::format()
 *      in a try/catch as a safety net, falling through to legacy handling
 *      if the mapper/validator still rejects the data.
 *
 * These tests verify BOTH fixes work.
 */
class SentenceVisualFormattingTest extends TestCase
{
    /**
     * Direct unit test of SentenceVisualMapper::cell() — the private method
     * that performs the max(1, ...) clamp on beat values.
     *
     * Tests edge cases:
     *   - beat:0   → clamped to 1
     *   - beat:-5  → clamped to 1 (negative values)
     *   - beat:null → defaults to 1 (via ?? 1)
     *   - beat:1   → stays 1 (minimum valid)
     *   - beat:5   → stays 5 (normal positive)
     */
    public function test_cell_method_clamps_beat_to_minimum_1(): void
    {
        $mapper = app(SentenceVisualMapper::class);
        $method = new ReflectionMethod(SentenceVisualMapper::class, 'cell');
        $method->setAccessible(true);

        // beat:0 should be clamped to 1
        $result = $method->invoke($mapper, ['functions' => [['world' => 'T', 'notes' => []]], 'beat' => 0]);
        $this->assertSame(1, $result['beat'], 'beat:0 should be clamped to 1');

        // beat:-5 (negative) should be clamped to 1
        $result = $method->invoke($mapper, ['functions' => [['world' => 'T', 'notes' => []]], 'beat' => -5]);
        $this->assertSame(1, $result['beat'], 'beat:-5 should be clamped to 1');

        // beat:null should default to 1 (?? 1 in the mapper)
        $result = $method->invoke($mapper, ['functions' => [['world' => 'T', 'notes' => []]]]);
        $this->assertSame(1, $result['beat'], 'missing beat should default to 1');

        // beat:1 should stay 1 (minimum valid, no clamp needed)
        $result = $method->invoke($mapper, ['functions' => [['world' => 'T', 'notes' => []]], 'beat' => 1]);
        $this->assertSame(1, $result['beat'], 'beat:1 should remain 1');

        // beat:5 should stay 5 (normal positive value)
        $result = $method->invoke($mapper, ['functions' => [['world' => 'T', 'notes' => []]], 'beat' => 5]);
        $this->assertSame(5, $result['beat'], 'beat:5 should remain 5');
    }

    /**
     * The primary fix: beat:0 should be clamped to 1 by the mapper,
     * so the validator accepts it and the canonical block is returned normally.
     */
    public function test_sentence_visual_with_beat_0_is_clamped_by_mapper(): void
    {
        $mapper = app(SentenceVisualMapper::class);

        $block = $mapper->toCanonical([
            'type'  => 'sentence_visual',
            'label' => 'Test with beat:0',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'tap_to_play'      => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode'  => 'cells',
                'cells' => [
                    ['beat' => 0, 'functions' => [['world' => 'T', 'notes' => []]]],
                    ['beat' => 2, 'functions' => [['world' => 'S', 'notes' => []]]],
                    ['beat' => 3, 'functions' => [['world' => 'D', 'notes' => []]]],
                    ['beat' => 4, 'functions' => [['world' => 'T', 'notes' => []]]],
                ],
            ]],
        ]);

        // The cell with beat:0 should have been clamped to 1
        $this->assertSame(1, $block['rows'][0]['cells'][0]['beat'],
            'beat:0 should be clamped to 1 by the mapper');
    }

    /**
     * Full integration: formatSentenceVisual() should NOT throw when
     * canonical data contains beat:0 — it should be clamped and returned normally.
     */
    public function test_format_sentence_visual_with_beat_0_does_not_crash(): void
    {
        // This is data that has type:'sentence_visual', valid rows, but a cell with beat:0
        $data = [
            'type'  => 'sentence_visual',
            'label' => 'Beat 0 test',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'tap_to_play'      => false,
            'show_root_toggle' => false,
            'rows' => [[
                'mode'  => 'cells',
                'cells' => [
                    ['beat' => 0, 'functions' => [['world' => 'T', 'notes' => []]]],
                    ['beat' => 2, 'functions' => [['world' => 'S', 'notes' => []]]],
                    ['beat' => 3, 'functions' => [['world' => 'D', 'notes' => []]]],
                    ['beat' => 4, 'functions' => [['world' => 'T', 'notes' => []]]],
                ],
            ]],
        ];

        // Should NOT throw — the mapper clamps beat:0, validator accepts it
        $result = LearningElementFormatter::format('sentence_visual', $data);

        // Should have valid block structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('beats', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertCount(4, $result['beats']);

        // The first cell (originally beat:0) should have beat=1 (clamped by mapper)
        $this->assertSame(1, $result['rows'][0]['cells'][0]['beat'],
            'Cell with original beat:0 should be clamped to beat:1');
    }

    /**
     * Even if the mapper somehow doesn't clamp (hypothetical regression),
     * the try/catch in formatSentenceVisual() should fall through to
     * legacy handling instead of crashing.
     *
     * Uses overlapping held segments which the validator rejects.
     */
    public function test_format_sentence_visual_falls_through_to_safety_net_on_invalid_data(): void
    {
        // Held row with overlapping segments — validator will reject
        $data = [
            'type'  => 'sentence_visual',
            'label' => 'Overlapping segments',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
            ],
            'rows' => [[
                'mode'     => 'held',
                'segments' => [
                    ['world' => 'T', 'beat_from' => 0, 'beat_to' => 1],
                    ['world' => 'S', 'beat_from' => 1, 'beat_to' => 2], // overlaps with previous
                ],
            ]],
        ];

        // Should NOT throw an exception — should return a valid block via legacy fallback
        $result = LearningElementFormatter::format('sentence_visual', $data);

        // Should have valid block structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('beats', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertNotEmpty($result['rows']);
        $this->assertNotEmpty(
            $result['rows'][0]['cells'] ?? $result['rows'][0]['segments'] ?? []
        );
    }

    /**
     * Valid canonical data should still format normally (no regression).
     */
    public function test_valid_canonical_data_formats_normally(): void
    {
        $data = [
            'type'  => 'sentence_visual',
            'label' => 'Normal T-S-D-T',
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'rows' => [[
                'mode'  => 'cells',
                'cells' => [
                    ['beat' => 1, 'functions' => [['world' => 'T', 'notes' => []]]],
                    ['beat' => 2, 'functions' => [['world' => 'S', 'notes' => []]]],
                    ['beat' => 3, 'functions' => [['world' => 'D', 'notes' => []]]],
                    ['beat' => 4, 'functions' => [['world' => 'T', 'notes' => []]]],
                ],
            ]],
        ];

        $result = LearningElementFormatter::format('sentence_visual', $data);

        // The canonical mapper does NOT include 'type' in output;
        // type is set at the element level by LearningElementResource.
        // Instead check for the canonical block structure.
        $this->assertArrayHasKey('beats', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertCount(4, $result['beats']);
        $this->assertCount(4, $result['rows'][0]['cells']);
        $this->assertSame(1, $result['rows'][0]['cells'][0]['beat']);
    }
}
