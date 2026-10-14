<?php

namespace App\Services\SentenceVisual;

use App\Enums\SentenceFunctionWorld;
use InvalidArgumentException;

/**
 * Maps raw sentence visual data into the canonical sentence_visual format.
 *
 * Handles conversion of beats, rows (cells/held), cells, segments,
 * functions, and notes into the standardized structure expected by
 * clients and the SentenceVisualValidator.
 */
class SentenceVisualMapper
{
    /**
     * Convert raw data into a canonical sentence visual array.
     *
     * Validates the presence of required beats and rows arrays,
     * maps each beat and row into the canonical format, and
     * null title/description are defaulted to empty string; mode is still omitted when null.
     *
     * Backward-compatible: accepts both 'sentence_visual' and
     * 'sentence_block' as valid canonical types to support
     * existing stored data.
     *
     * @param  array  $data  Raw sentence visual data
     * @param  int|string|null  $id  Optional block identifier
     * @return array The canonical sentence visual payload
     * @throws InvalidArgumentException If beats or rows are missing or invalid
     */
    public function toCanonical(array $data, int|string|null $id = null): array
    {
        $type = $data['type'] ?? null;
        if (! in_array($type, ['sentence_visual', 'sentence_block'], true)) {
            throw new InvalidArgumentException('Sentence visual payload must be canonical type sentence_visual.');
        }

        if (! isset($data['beats'], $data['rows']) || ! is_array($data['beats']) || ! is_array($data['rows'])) {
            throw new InvalidArgumentException('Sentence visual requires canonical beats and rows arrays.');
        }

        $result = [
            'type'               => 'sentence_visual',
            'label'              => $data['label'] ?? null,
            'title'              => $data['title'] ?? null,
            'description'       => $data['description'] ?? null,
            'mode'               => $data['mode'] ?? null,
            'beat_numbering_mode'=> $data['beat_numbering_mode'] ?? (! empty($data['continuous_numbering']) ? 'continuous' : 'restart_at_8'),
            'beats'              => array_values(array_map(fn (array $beat) => $this->beat($beat), $data['beats'])),
            'tap_to_play'        => (bool) ($data['tap_to_play'] ?? false),
            'show_root_toggle'   => (bool) ($data['show_root_toggle'] ?? false),
            'show_reframe_labels'=> (bool) ($data['show_reframe_labels'] ?? true),
            'rows'               => array_values(array_map(fn (array $row) => $this->row($row, $data['mode'] ?? null), $data['rows'])),
        ];

        // Keep the admin-configured playback label in the public canonical
        // payload. Dropping it here makes a saved sentence visual lose its
        // playback control text after API formatting.
        if (isset($data['playback_label']) && trim((string) $data['playback_label']) !== '') {
            $result['playback_label'] = (string) $data['playback_label'];
        }

        if ($result['title'] === null) {
            $result['title'] = '';
        }
        if ($result['description'] === null) {
            $result['description'] = '';
        }
        if ($result['mode'] === null) {
            unset($result['mode']);
        }

        return $result;
    }

    /**
     * Map a single beat to canonical format.
     *
     * @param  array  $beat  Raw beat data
     * @return array{label: string}
     */
    private function beat(array $beat): array
    {
        return [
            'label' => (string) ($beat['label'] ?? ''),
        ];
    }

    /**
     * Map a single row to canonical format based on its mode.
     *
     * Supports 'cells' mode (with individual cell objects) and
     * 'held' mode (with segment spans).
     *
     * @param  array  $row  Raw row data
     * @return array The canonical row payload
     */
    private function row(array $row, ?string $mode = null): array
    {
        // Effective mode: per-row mode takes precedence (legacy), otherwise root-level mode, else 'cells'
        $effectiveMode = $row['mode'] ?? $mode ?? 'cells';

        $startFromCell = isset($row['start_from_cell']) && $row['start_from_cell'] !== null && $row['start_from_cell'] !== ''
            ? (int) $row['start_from_cell']
            : null;

        if ($startFromCell !== null && $startFromCell < 0) {
            throw new InvalidArgumentException('start_from_cell must be zero or a positive cell index.');
        }

        if ($effectiveMode === 'held') {
            return [
                ...(! empty($row['label']) ? ['label' => (string) $row['label']] : []),
                ...($startFromCell !== null && $startFromCell > 0 ? ['start_from_cell' => $startFromCell] : []),
                'segments' => array_values(array_map(fn (array $segment) => $this->segment($segment), $row['segments'] ?? [])),
            ];
        }

        return [
            ...(! empty($row['label']) ? ['label' => (string) $row['label']] : []),
            ...($startFromCell !== null && $startFromCell > 0 ? ['start_from_cell' => $startFromCell] : []),
            'cells' => array_values(array_map(fn (array $cell) => $this->cell($cell), $row['cells'] ?? [])),
        ];
    }

    /**
     * Map a single cell to canonical format.
     *
     * Determines the cell type (normal, empty, pair_v_a, pair_v_b)
     * and builds the appropriate structure including optional
     * reframe data for pair cells.
     *
     * @param  array  $cell  Raw cell data
     * @return array The canonical cell payload
     */
    private function cell(array $cell): array
    {
        $functions = array_values(array_map(fn (array $function) => $this->function($function), $cell['functions'] ?? []));

        $type = $cell['type'] ?? null;
        if ($type === null || ! in_array($type, ['normal', 'empty', 'pair_v_a', 'pair_v_b'], true)) {
            // Infer type from functions count and reframe variant for backward compatibility
            $type = match (count($functions)) {
                2 => ($cell['reframe']['variant'] ?? 'B') === 'A' ? 'pair_v_a' : 'pair_v_b',
                0 => 'empty',
                default => 'normal',
            };
        }

        $payload = [
            'type' => $type,
            'beat' => max(1, (int) ($cell['beat'] ?? 1)),
            'functions' => $functions,
        ];

        if (isset($cell['annotation']) && $cell['annotation'] !== '') {
            $payload['annotation'] = (string) $cell['annotation'];
        }

        if (count($functions) === 2) {
            $payload['reframe'] = [
                'variant' => $cell['reframe']['variant'] ?? ($type === 'pair_v_a' ? 'A' : 'B'),
                'roots' => [
                    'old' => $cell['reframe']['roots']['old'] ?? ['degree' => null, 'pitch' => null],
                    'new' => $cell['reframe']['roots']['new'] ?? ['degree' => null, 'pitch' => null],
                ],
            ];

            $payload['editable'] = (bool) ($cell['editable'] ?? false);

            if (isset($cell['allowable_notes']) && is_array($cell['allowable_notes'])) {
                $payload['allowable_notes'] = array_values(array_filter(array_map('strval', $cell['allowable_notes'])));
            } elseif (isset($cell['allowable_notes']) && is_string($cell['allowable_notes']) && trim($cell['allowable_notes']) !== '') {
                $payload['allowable_notes'] = array_values(preg_split('/[\s,]+/', trim($cell['allowable_notes'])));
            }

            if (isset($cell['centre_voicing']) && $cell['centre_voicing'] !== '') {
                $payload['centre_voicing'] = (string) $cell['centre_voicing'];
            }

            if (isset($cell['octave_range']) && is_numeric($cell['octave_range'])) {
                $payload['octave_range'] = (int) $cell['octave_range'];
            }
        }

        // bg_color and bottom_bar_color intentionally omitted - Flutter handles visual styling

        return $payload;
    }

    /**
     * Map a single held-mode segment to canonical format.
     *
     * @param  array  $segment  Raw segment data
     * @return array The canonical segment payload
     */
    private function segment(array $segment): array
    {
        $payload = [
            'world' => SentenceFunctionWorld::normalize($segment['world'] ?? null),
            'notes' => $this->notes($segment['notes'] ?? []),
            'beat_from' => (int) ($segment['beat_from'] ?? 0),
            'beat_to' => (int) ($segment['beat_to'] ?? 0),
            'weight' => (float) ($segment['weight'] ?? 1),
            ...(! empty($segment['root']) ? ['root' => $segment['root']] : []),
        ];

        if (isset($segment['superscript'])) {
            $payload['superscript'] = (string) $segment['superscript'];
        }

        return $payload;
    }

    /**
     * Map a single function to canonical format.
     *
     * @param  array  $function  Raw function data
     * @return array The canonical function payload
     */
    private function function(array $function): array
    {
        $world = SentenceFunctionWorld::normalize($function['world'] ?? null);
        $payload = [
            'world' => $world,
            'notes' => $this->notes($function['notes'] ?? []),
        ];

        if (isset($function['reframe_role'])) {
            $payload['reframe_role'] = $function['reframe_role'];
        }

        if (isset($function['superscript'])) {
            $payload['superscript'] = (string) $function['superscript'];
        }

        if (isset($function['annotation']) && $function['annotation'] !== '') {
            $payload['annotation'] = (string) $function['annotation'];
        }

        return $payload;
    }

    /**
     * Normalize notes data into an array of note objects.
     *
     * Accepts a string (space-separated), an array of strings,
     * or an array of note objects with a 'number' key.
     *
     * @param  array|string|null  $notes  Raw notes data
     * @return array<int, array{number: string}> Normalized notes
     */
    private function notes(array|string|null $notes): array
    {
        if (is_string($notes)) {
            $notes = preg_split('/\s+/', trim($notes)) ?: [];
        }

        return array_values(array_map(function ($note) {
            if (is_array($note)) {
                return [
                    'number' => (string) ($note['number'] ?? ''),
                ];
            }

            return [
                'number' => (string) $note,
            ];
        }, array_filter($notes ?? [], fn ($note) => $note !== '' && $note !== null)));
    }
}
