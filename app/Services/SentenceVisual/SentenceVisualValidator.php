<?php

namespace App\Services\SentenceVisual;

use InvalidArgumentException;

/**
 * Validates sentence visual canonical payloads.
 *
 * Ensures that sentence visual blocks conform to the canonical schema,
 * checking beat assignments, cell types, function counts,
 * reframe constraints, and held-mode segment spans.
 */
class SentenceVisualValidator
{
    /**
     * Validate a complete canonical sentence visual block.
     *
     * Rejects obsolete properties, verifies at least one beat exists,
     * and validates each row according to its mode (cells or held).
     *
     * @param  array  $block  The canonical sentence visual payload
     * @throws InvalidArgumentException If any validation rule is violated
     */
    public function validate(array $block): void
    {
        // Reject obsolete properties
        if (array_key_exists('colour_notes', $block)) {
            throw new InvalidArgumentException('Obsolete property colour_notes must not be present.');
        }
        if (array_key_exists('playback', $block)) {
            throw new InvalidArgumentException('Obsolete property playback must not be present.');
        }

        $beats = $block['beats'] ?? [];
        if (! is_array($beats) || count($beats) === 0) {
            throw new InvalidArgumentException('Sentence visual requires at least one beat.');
        }

        foreach ($block['rows'] ?? [] as $rowIndex => $row) {
            $mode = $row['mode'] ?? 'cells';
            if ($mode === 'held') {
                $this->validateHeldRow($row, $rowIndex, count($beats));

                continue;
            }

            $this->validateCellsRow($row, $rowIndex, $beats);
        }
    }

    /**
     * Validate a cells-mode row.
     *
     * Checks each cell's type, beat range, unique beat assignments,
     * function count limits, and reframe pair constraints.
     *
     * @param  array  $row  The row payload
     * @param  int  $rowIndex  Row index for error messages
     * @param  array  $beats  The canonical beats array
     * @throws InvalidArgumentException If any cell validation fails
     */
    private function validateCellsRow(array $row, int $rowIndex, array $beats): void
    {
        $beatCount = count($beats);
        $occupiedBeats = [];
        $validTypes = ['normal', 'empty', 'pair_v_a', 'pair_v_b'];

        $this->validateStartFromCell($row, $rowIndex, count($row['cells'] ?? []));

        foreach ($row['cells'] ?? [] as $cellIndex => $cell) {
            // Validate mandatory type
            $type = $cell['type'] ?? null;
            if ($type === null || ! in_array($type, $validTypes, true)) {
                throw new InvalidArgumentException("Row {$rowIndex} cell {$cellIndex} requires a valid type (normal, empty, pair_v_a, pair_v_b).");
            }

            $beat = (int) ($cell['beat'] ?? 0);
            if ($beat < 1 || $beat > $beatCount) {
                throw new InvalidArgumentException("Row {$rowIndex} cell {$cellIndex} references an invalid beat.");
            }
            if (isset($occupiedBeats[$beat])) {
                throw new InvalidArgumentException("Row {$rowIndex} has duplicate cells for beat {$beat}.");
            }
            $occupiedBeats[$beat] = true;

            $functions = $cell['functions'] ?? [];
            if (count($functions) > 2) {
                throw new InvalidArgumentException('A sentence cell may contain at most two functions.');
            }

            // Pair types must have exactly 2 functions
            if (in_array($type, ['pair_v_a', 'pair_v_b'], true) && count($functions) !== 2) {
                throw new InvalidArgumentException("Row {$rowIndex} cell {$cellIndex} of type {$type} requires exactly 2 functions.");
            }

            if (count($functions) === 2) {
                $roles = array_values(array_filter(array_column($functions, 'reframe_role')));
                sort($roles);
                if ($roles !== ['new', 'old']) {
                    throw new InvalidArgumentException('Reframe cells require old and new function roles.');
                }
                if (empty($cell['reframe']['variant'])) {
                    throw new InvalidArgumentException('Reframe cells require a variant.');
                }
            }

        }
    }

    /**
     * Validate a held-mode row.
     *
     * Checks each segment's beat span validity and ensures
     * no overlapping segments exist.
     *
     * @param  array  $row  The held-mode row payload
     * @param  int  $rowIndex  Row index for error messages
     * @param  int  $beatCount  Total number of beats
     * @throws InvalidArgumentException If any segment validation fails
     */
    private function validateHeldRow(array $row, int $rowIndex, int $beatCount): void
    {
        $this->validateStartFromCell($row, $rowIndex, count($row['segments'] ?? []));

        $lastTo = -1;
        foreach ($row['segments'] ?? [] as $segmentIndex => $segment) {
            $from = (int) ($segment['beat_from'] ?? -1);
            $to = (int) ($segment['beat_to'] ?? -1);
            if ($from < 0 || $to < $from || $to >= $beatCount) {
                throw new InvalidArgumentException("Held row {$rowIndex} segment {$segmentIndex} has an invalid beat span.");
            }
            if ($from <= $lastTo) {
                throw new InvalidArgumentException("Held row {$rowIndex} has overlapping segments.");
            }
            $lastTo = $to;
        }
    }

    /**
     * Validate the optional temporal reframe start index.
     * Zero is a no-op; non-zero values must point after the first
     * cell/segment and before the end of the row.
     */
    private function validateStartFromCell(array $row, int $rowIndex, int $cellCount): void
    {
        if (! array_key_exists('start_from_cell', $row) || $row['start_from_cell'] === null) {
            return;
        }

        $startFromCell = (int) $row['start_from_cell'];
        if ($startFromCell < 0 || ($startFromCell > 0 && $startFromCell >= $cellCount)) {
            throw new InvalidArgumentException(
                "Row {$rowIndex} start_from_cell must be 0 or an index from 1 to ".max(0, $cellCount - 1).'.'
            );
        }
    }
}
