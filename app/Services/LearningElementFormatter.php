<?php

namespace App\Services;

use App\Services\SentenceVisual\SentenceVisualService;

/**
 * Formats raw learning element data into type-specific structured payloads.
 *
 * Handles formatting for all element types: checklists, tables,
 * sentence visuals, what-fnh-reveals, template-form tables, cards,
 * functional flows, section headings, and legacy format conversions.
 */
class LearningElementFormatter
{
    /**
     * Format a learning element's data payload based on its type.
     *
     * @param  string  $type  The element type
     * @param  mixed  $data  The raw JSON data of the element
     * @param  iterable|null  $checklistProgressMap  Any user progress associated with checklist elements
     * @param  int  $elementId  The ID of the element (needed for checklist progress)
     * @return array The formatted data array
     */
    public static function format(string $type, $data, $checklistProgressMap = [], int $elementId = 0, bool $forAdmin = false): array
    {
        $data = is_array($data) ? $data : (array) ($data ?? []);

        // For admin edits, we want to return the raw JSON structures exactly as saved,
        // to avoid stripping fields like colors, playback labels, etc.
        if ($forAdmin) {
            $jsonKeyMap = [
                'table' => 'table_json',
                'template_form_table' => 'template_form_table_json',
                'movement_quality_table' => 'movement_quality_table_json',
                'centre_character_table' => 'centre_character_table_json',
                'functional_flow' => 'functional_flow_json',
                'exploring_section' => 'exploring_section_json',
                'centre_identity_card' => 'centre_identity_card_json',
                'form_identity_card' => 'form_identity_card_json',
            ];

            if (isset($jsonKeyMap[$type])) {
                $data = self::decodeEmbeddedJsonPayload($data, $jsonKeyMap[$type]);
            }

            if ($type === 'sentence_visual') {
                return self::formatSentenceVisual($data, true);
            }

            return $data;
        }

        return match ($type) {
            'checklist' => self::formatChecklist($data, $elementId, $checklistProgressMap),
            'bullet_list' => self::formatBulletList($data),
            'table' => self::formatTable($data),
            'sentence_visual' => self::formatSentenceVisual($data),
            'what_fnh_reveals' => self::formatWhatFnhReveals($data),
            'template_form_table' => self::formatTemplateFormTable($data),
            'defining_behaviours_card' => self::formatDefiningBehavioursCard($data),
            'movement_quality_table' => self::formatMovementQualityTable($data),
            'centre_character_table' => self::formatCentreCharacterTable($data),
            'functional_flow' => self::formatFunctionalFlow($data),
            'section_heading' => self::formatSectionHeading($data),
            'exploring_section' => self::formatExploringSection($data),
            'centre_identity_card' => self::formatCentreIdentityCard($data),
            'form_identity_card' => self::formatFormIdentityCard($data),
            default => $data,
        };
    }

    private static function formatChecklist(array $data, int $elementId, $checklistProgressMap): array
    {
        if (isset($data['items'])) {
            $progress = $checklistProgressMap[$elementId] ?? null;
            $checkedIndexes = $progress
                ? array_map('intval', $progress->checked_items ?? [])
                : [];

            $data['checklist_items'] = collect($data['items'])->map(function ($item, $index) use ($checkedIndexes) {
                if (is_array($item)) {
                    $item['checked'] = in_array((int) $index, $checkedIndexes, true);
                }

                return $item;
            })->all();
            unset($data['items']);

            $totalItems = count($data['checklist_items']);
            $checkedCount = count($checkedIndexes);
            $data['completed_count'] = $checkedCount;
            $data['total_count'] = $totalItems;

            unset($data['footer']);
        }

        return $data;
    }

    private static function formatBulletList(array $data): array
    {
        if (isset($data['items'])) {
            $data['bullet_list_items'] = $data['items'];
            unset($data['items']);
        }

        return $data;
    }

    private static function formatTable(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'table_json');

        if (isset($data['rows'])) {
            $normalizedRows = collect($data['rows'])->map(function ($row) {
                if (! is_array($row)) {
                    return [self::normalizeTableCell($row)];
                }

                return collect($row)->map(fn ($cell) => self::normalizeTableCell($cell))->all();
            })->all();

            $numCols = ! empty($normalizedRows[0]) ? count($normalizedRows[0]) : 0;
            $numRows = count($normalizedRows);
            $rowColors = $data['row_colors'] ?? [];
            $colColors = $data['col_colors'] ?? [];

            $rows = [];
            for ($r = 0; $r < $numRows; $r++) {
                $columns = [];
                for ($c = 0; $c < $numCols; $c++) {
                    $cell = $normalizedRows[$r][$c] ?? self::normalizeTableCell('');
                    $cell['column'] = $c + 1;

                    // Apply column color if cell doesn't have its own color AND row doesn't have a color
                    if (empty($cell['bg_color']) && empty($rowColors[$r]) && ! empty($colColors[$c])) {
                        $cell['bg_color'] = $colColors[$c];
                    }

                    $columns[] = $cell;
                }

                $rowPayload = [
                    'row' => $r + 1,
                ];

                // Apply row color
                if (! empty($rowColors[$r])) {
                    $rowPayload['bg_color'] = $rowColors[$r];
                }

                $rowPayload['columns'] = $columns;
                $rows[] = $rowPayload;
            }

            $data['rows'] = $rows;
            $data['total_rows'] = $numRows;
            $data['total_columns'] = $numCols;

            unset($data['row_colors'], $data['col_colors']);
        }

        return $data;
    }

    private static function formatSentenceVisual(array $data, bool $forAdmin = false): array
    {
        // ------------------------------------------------------------------
        // ULTIMATE SAFETY NET: wrap the entire method body so that NO
        // exception can ever escape. If ANYTHING fails (canonical format
        // rejected, legacy conversion throws, etc.), we return a hardcoded
        // minimal block directly - not via SentenceVisualService - so there
        // is absolutely NO path for an exception to leak to the controller
        // and produce the dreaded "Learning not found" (404).
        // ------------------------------------------------------------------
        try {
            // Already in canonical sentence_visual format - pass directly
            if (($data['type'] ?? null) === 'sentence_visual') {
                // -- Backward compatibility: infer root fields from legacy structure --
                if (! isset($data['mode']) && isset($data['rows'][0]['mode'])) {
                    $data['mode'] = $data['rows'][0]['mode'];
                }
                // Old format stored title/desc as example_title/example_description
                if (! isset($data['title']) && isset($data['example_title'])) {
                    $data['title'] = $data['example_title'];
                }
                if (! isset($data['description']) && isset($data['example_description'])) {
                    $data['description'] = $data['example_description'];
                }

                // Ensure rows array exists and is valid before passing to SentenceVisualService
                if (isset($data['rows']) && is_array($data['rows'])) {
                    // Ensure beats array exists (backward compat for data saved without beats)
                    if (! isset($data['beats']) || ! is_array($data['beats']) || empty($data['beats'])) {
                        // Compute beats from the maximum beat index across all rows' cells
                        $maxBeat = 0;
                        foreach ($data['rows'] as $row) {
                            if (($row['mode'] ?? 'cells') === 'held') {
                                foreach ($row['segments'] ?? [] as $segment) {
                                    $to = (int) ($segment['beat_to'] ?? 0);
                                    $beat = $to + 1;
                                    if ($beat > $maxBeat) {
                                        $maxBeat = $beat;
                                    }
                                }
                            } else {
                                foreach ($row['cells'] ?? [] as $cell) {
                                    $beat = (int) ($cell['beat'] ?? 0);
                                    if ($beat > $maxBeat) {
                                        $maxBeat = $beat;
                                    }
                                }
                            }
                        }
                        if ($maxBeat < 1) {
                            $maxBeat = 4;
                        }
                        $data['beats'] = [];
                        for ($i = 1; $i <= $maxBeat; $i++) {
                            $data['beats'][] = ['label' => (string) $i];
                        }
                    }

                    // Wrap in try/catch - if SentenceVisualService rejects the data (e.g. cells with
                    // beat 0, invalid types, overlapping segments), fall through to legacy handling
                    // instead of crashing the entire learning show endpoint.
                    try {
                        if ($forAdmin) {
                            return $data;
                        }
                        return app(SentenceVisualService::class)->format($data);
                    } catch (\Throwable $e) {
                        logger()->warning('SentenceVisualService rejected canonical data, falling back to legacy format', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                // rows missing, invalid, or SentenceVisualService rejected - fall through to legacy
            }

            // -- Legacy format: sentence.rows (seeder / pre-canonical) ---------
            // Structure: ['title', 'playback_label', 'sentence' => ['rows' => [
            //     ['Beat', '1', '2', '3', ...],
            //     ['Function', 'T', 'S', 'D', ...],
            //     ...
            // ]]]
            if (isset($data['sentence']['rows']) && is_array($data['sentence']['rows'])) {
                $converted = self::convertLegacySentenceRows($data);
                return $forAdmin ? $converted : app(SentenceVisualService::class)->format($converted);
            }

            // -- Legacy format: old rows/beats with chord objects --------------
            // This format had rows with type (chord/pair/empty), function, notes, etc.
            if (isset($data['rows']) && is_array($data['rows'])) {
                $firstRow = $data['rows'][0] ?? [];
                if (isset($firstRow['beats']) && is_array($firstRow['beats'])) {
                    $converted = self::convertLegacyBeatRows($data);
                    return $forAdmin ? $converted : app(SentenceVisualService::class)->format($converted);
                }
            }

            if (isset($data['beats']) && is_array($data['beats'])) {
                $converted = self::convertLegacyFlatBeats($data);
                return $forAdmin ? $converted : app(SentenceVisualService::class)->format($converted);
            }

            // Last resort - wrap into a minimal canonical block so it doesn't crash
            $converted = self::wrapMinimalBlock($data);
            return $forAdmin ? $converted : app(SentenceVisualService::class)->format($converted);
        } catch (\Throwable $e) {
            // -- NUCLEAR FALLBACK ---------------------------------------------
            // If absolutely everything failed (including wrapMinimalBlock's own
            // call to SentenceVisualService), return a hardcoded block directly
            // WITHOUT going through SentenceVisualService. This ensures no path
            // exists for an exception to escape to the controller.
            logger()->error('formatSentenceVisual: all formatting attempts failed, returning hardcoded block', [
                'error' => $e->getMessage(),
            ]);

            return self::hardcodedFallbackBlock($data);
        }
    }

    /**
     * Convert legacy sentence.rows format to canonical sentence_visual.
     *
     * Old format:
     *   ['title' => '…', 'playback_label' => '…', 'sentence' => ['rows' => [
     *       ['Beat',     '1',   '2',   '3',   '4'],
     *       ['Function', 'T',   'S',   'D',   'T'],
     *       ['Feeling',  'Home','Away','Push','Rest'],
     *   ]]]
     *
     * Row 0 = beat labels (header)
     * Row 1 = function names (T/S/D) - the only row that produces canonical cells
     * Row 2+ = additional metadata rows - skipped (no canonical representation)
     */
    private static function convertLegacySentenceRows(array $data): array
    {
        $rawRows = $data['sentence']['rows'];
        if (count($rawRows) < 2) {
            return self::wrapMinimalBlock($data);
        }

        // First row is the beat header - extract beat labels
        $headerRow = $rawRows[0];
        $beatLabels = array_slice(array_values($headerRow), 1); // skip first cell (label)

        // Build canonical beats array
        $beats = [];
        foreach ($beatLabels as $label) {
            $beats[] = [
                'label' => (string) $label,
            ];
        }

        // Row 1 = Function row - create canonical cells with parsed functions
        $functionRow = array_values($rawRows[1]);
        $functionValues = array_slice($functionRow, 1);

        $cells = [];
        foreach ($functionValues as $beatIndex => $value) {
            $beatNum = $beatIndex + 1;
            $world = self::parseFunctionWorld((string) $value);
            $cells[] = [
                'beat' => $beatNum,
                'functions' => [
                    ['world' => $world, 'notes' => []],
                ],
            ];
        }

        $rows = [['mode' => 'cells', 'cells' => $cells]];

        return app(SentenceVisualService::class)->format(
            self::buildCanonicalBlock([
                'id' => $data['id'] ?? '',
                'label' => $data['label'] ?? $data['title'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'mode' => $data['mode'] ?? null,
                'beats' => $beats,
                'tap_to_play' => ! empty($data['playback_label']),
                'show_root_toggle' => (bool) ($data['show_root_button'] ?? false),
                'rows' => $rows,
                'playback_label' => $data['playback_label'] ?? null,
            ])
        );
    }

    /**
     * Convert legacy rows-with-beats format to canonical.
     * Old format had beats with type (chord/pair/empty), function, notes, etc.
     */
    private static function convertLegacyBeatRows(array $data): array
    {
        $allBeats = [];
        $firstRowBeats = $data['rows'][0]['beats'] ?? [];

        // Build beats from the first row
        $beatNum = 1;
        foreach (array_values($firstRowBeats) as $raw) {
            $allBeats[] = ['label' => (string) $beatNum];
            $beatNum++;
        }

        // Build rows - each data row becomes a canonical row
        $rows = [];
        foreach ($data['rows'] as $row) {
            $rawBeats = array_values($row['beats'] ?? []);
            $rowTitle = trim($row['title'] ?? '');

            $cells = [];
            $bNum = 1;
            foreach ($rawBeats as $raw) {
                $cells[] = self::rawBeatToCell($raw, $bNum);
                $bNum++;
            }

            $rowPayload = ['mode' => 'cells', 'cells' => $cells];
            if ($rowTitle !== '') {
                $rowPayload['label'] = $rowTitle;
            }
            $rows[] = $rowPayload;
        }

        return app(SentenceVisualService::class)->format(
            self::buildCanonicalBlock([
                'id' => $data['id'] ?? '',
                'label' => $data['label'] ?? $data['title'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'mode' => $data['mode'] ?? null,
                'beats' => $allBeats,
                'tap_to_play' => ! empty($data['playback_label']) || ! empty($data['tap_to_play']),
                'show_root_toggle' => (bool) ($data['show_root_button'] ?? $data['show_root_toggle'] ?? false),
                'rows' => $rows,
                'playback_label' => $data['playback_label'] ?? null,
            ])
        );
    }

    /**
     * Convert legacy flat beats array to canonical.
     */
    private static function convertLegacyFlatBeats(array $data): array
    {
        $rawBeats = array_values($data['beats']);

        $allBeats = [];
        $beatNum = 1;
        foreach ($rawBeats as $raw) {
            $allBeats[] = ['label' => (string) $beatNum];
            $beatNum++;
        }

        $cells = [];
        $bNum = 1;
        foreach ($rawBeats as $raw) {
            $cells[] = self::rawBeatToCell($raw, $bNum);
            $bNum++;
        }

        return app(SentenceVisualService::class)->format(
            self::buildCanonicalBlock([
                'id' => $data['id'] ?? '',
                'label' => $data['label'] ?? $data['title'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'mode' => $data['mode'] ?? null,
                'beats' => $allBeats,
                'tap_to_play' => ! empty($data['playback_label']) || ! empty($data['tap_to_play']),
                'show_root_toggle' => (bool) ($data['show_root_button'] ?? $data['show_root_toggle'] ?? false),
                'rows' => [['mode' => 'cells', 'cells' => $cells]],
                'playback_label' => $data['playback_label'] ?? null,
            ])
        );
    }

    /**
     * Convert a single raw old-format beat to a canonical cell.
     */
    private static function rawBeatToCell(array $raw, int $beatNum): array
    {
        $type = $raw['type'] ?? 'chord';

        if ($type === 'empty') {
            return ['beat' => $beatNum, 'functions' => [['world' => 'T', 'notes' => []]]];
        }

        if ($type === 'pair') {
            $leftWorld = self::parseFunctionWorld($raw['left']['function'] ?? 'T');
            $rightWorld = self::parseFunctionWorld($raw['right']['function'] ?? 'D');

            $pairCell = [
                'beat' => $beatNum,
                'functions' => [
                    ['world' => $leftWorld, 'notes' => [], 'reframe_role' => 'old'],
                    ['world' => $rightWorld, 'notes' => [], 'reframe_role' => 'new'],
                ],
                'reframe' => [
                    'variant' => $raw['variant'] ?? 'A',
                    'roots' => [
                        'old' => ['degree' => '1', 'pitch' => ''],
                        'new' => ['degree' => '1', 'pitch' => ''],
                    ],
                ],
            ];

            if (isset($raw['editable'])) {
                $pairCell['editable'] = (bool) $raw['editable'];
            }
            if (isset($raw['allowable_notes'])) {
                $pairCell['allowable_notes'] = $raw['allowable_notes'];
            }
            if (isset($raw['centre_voicing'])) {
                $pairCell['centre_voicing'] = $raw['centre_voicing'];
            }
            if (isset($raw['octave_range'])) {
                $pairCell['octave_range'] = $raw['octave_range'];
            }

            return $pairCell;
        }

        // Default: chord
        $world = self::parseFunctionWorld($raw['function'] ?? 'T');

        return ['beat' => $beatNum, 'functions' => [['world' => $world, 'notes' => []]]];
    }

    /**
     * Build a canonical sentence_visual array with sensible defaults.
     */
    private static function buildCanonicalBlock(array $overrides): array
    {
        $block = array_replace(
            [
                'type' => 'sentence_visual',
                'id' => '',
                'label' => null,
                'beats' => [],
                'tap_to_play' => false,
                'show_root_toggle' => false,
                'show_reframe_labels' => true,
                'rows' => [],
                'playback_label' => null,
            ],
            $overrides
        );

        // Remove playback_label from the output if null
        if ($block['playback_label'] === null) {
            unset($block['playback_label']);
        }

        return $block;
    }

    /**
     * Create a minimal canonical block as a safety net.
     *
     * Uses SentenceVisualService for formatting so the output matches the
     * canonical schema (type inference, beat labels, etc.). If SentenceVisualService
     * itself throws (highly unlikely with hardcoded valid data), the exception
     * will be caught by the outer try/catch in formatSentenceVisual().
     */
    private static function wrapMinimalBlock(array $data): array
    {
        return app(SentenceVisualService::class)->format(
            self::buildCanonicalBlock([
                'label' => $data['title'] ?? $data['label'] ?? null,
                'beats' => [
                    ['label' => '1'],
                    ['label' => '2'],
                    ['label' => '3'],
                    ['label' => '4'],
                ],
                'tap_to_play' => ! empty($data['playback_label']),
                'show_root_toggle' => (bool) ($data['show_root_button'] ?? false),
                'rows' => [[
                    'mode' => 'cells',
                    'cells' => [
                        ['beat' => 1, 'functions' => [['world' => 'T', 'notes' => []]]],
                        ['beat' => 2, 'functions' => [['world' => 'T', 'notes' => []]]],
                        ['beat' => 3, 'functions' => [['world' => 'T', 'notes' => []]]],
                        ['beat' => 4, 'functions' => [['world' => 'T', 'notes' => []]]],
                    ],
                ]],
            ])
        );
    }

    /**
     * Hardcoded fallback block returned when even wrapMinimalBlock fails.
     *
     * This does NOT go through SentenceVisualService::format() - it returns
     * a pre-built array directly. This is the absolute last line of defence
     * to guarantee that NO exception can propagate from formatSentenceVisual()
     * to the controller and cause a 404 "Learning not found".
     */
    private static function hardcodedFallbackBlock(array $data): array
    {
        return [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'label' => $data['title'] ?? $data['label'] ?? null,
            'beats' => [
                ['label' => '1'],
                ['label' => '2'],
                ['label' => '3'],
                ['label' => '4'],
            ],
            'tap_to_play' => ! empty($data['playback_label']),
            'show_root_toggle' => (bool) ($data['show_root_button'] ?? false),
            'show_reframe_labels' => true,
            'rows' => [[
                'mode' => 'cells',
                'cells' => [
                    ['beat' => 1, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                    ['beat' => 2, 'type' => 'normal', 'functions' => [['world' => 'S', 'notes' => []]]],
                    ['beat' => 3, 'type' => 'normal', 'functions' => [['world' => 'D', 'notes' => []]]],
                    ['beat' => 4, 'type' => 'normal', 'functions' => [['world' => 'T', 'notes' => []]]],
                ],
            ]],
        ];
    }

    /**
     * Parse a function string to its canonical world form.
     */
    private static function parseFunctionWorld(string $func): string
    {
        $func = strtoupper(trim($func));

        return match ($func) {
            'T', 'TONIC' => 'T',
            'S', 'SUBDOMINANT' => 'S',
            'D', 'DOMINANT' => 'D',
            'T#', 'T_SHARP' => 'T#',
            'S#', 'S_SHARP' => 'S#',
            'D#', 'D_SHARP' => 'D#',
            'T♭', 'T_FLAT' => 'T♭',
            'S♭', 'S_FLAT' => 'S♭',
            'D♭', 'D_FLAT' => 'D♭',
            default => $func !== '' ? $func : 'T',
        };
    }

    private static function formatWhatFnhReveals(array $data): array
    {
        $data['label'] = $data['label'] ?? 'WHAT FNH REVEALS';
        $data['text'] = $data['text'] ?? '';
        $data['label_color'] = $data['label_color'] ?? '#7C3AED';

        return $data;
    }

    private static function formatTemplateFormTable(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'template_form_table_json');

        $data['t_form_color'] = $data['t_form_color'] ?? '#2457E6';
        $data['t_sharp_form_color'] = $data['t_sharp_form_color'] ?? '#E97B00';
        $rows = $data['rows'] ?? [];
        $data['rows'] = collect($rows)->map(function ($row, $index) {
            return [
                'row' => $index + 1,
                'template' => $row['template'] ?? '',
                't_form' => $row['t_form'] ?? '',
                't_sharp_form' => $row['t_sharp_form'] ?? '',
                'row_color' => $row['row_color'] ?? '',
            ];
        })->values()->all();
        $data['total_rows'] = count($data['rows']);

        return $data;
    }

    private static function formatDefiningBehavioursCard(array $data): array
    {
        $data['number'] = $data['number'] ?? '';
        $data['title'] = $data['title'] ?? '';
        $data['section_label'] = $data['section_label'] ?? 'DEFINING BEHAVIOURS';
        $data['bullets'] = is_array($data['bullets'] ?? null) ? array_values($data['bullets']) : [];
        $data['total_bullets'] = count($data['bullets']);
        $bestFor = $data['best_for'] ?? [];
        $data['best_for'] = [
            'label' => $bestFor['label'] ?? 'Best for:',
            'text' => $bestFor['text'] ?? '',
        ];

        return $data;
    }

    private static function formatMovementQualityTable(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'movement_quality_table_json');

        $rows = $data['rows'] ?? [];
        $data['rows'] = collect($rows)->map(function ($row, $index) {
            return [
                'row' => $index + 1,
                'movement' => $row['movement'] ?? '',
                'movement_color' => $row['movement_color'] ?? '#16A34A',
                'emotional_quality' => $row['emotional_quality'] ?? '',
                'from_t' => $row['from_t'] ?? '',
                'from_t_sharp' => $row['from_t_sharp'] ?? '',
            ];
        })->values()->all();
        $data['total_rows'] = count($data['rows']);

        return $data;
    }

    private static function formatCentreCharacterTable(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'centre_character_table_json');

        $rows = $data['rows'] ?? [];
        $data['rows'] = collect($rows)->map(function ($row, $index) {
            return [
                'row' => $index + 1,
                'centre' => $row['centre'] ?? '',
                'centre_color' => $row['centre_color'] ?? '#2457E6',
                'character' => $row['character'] ?? '',
                'what_home_feels_like' => $row['what_home_feels_like'] ?? '',
            ];
        })->values()->all();
        $data['total_rows'] = count($data['rows']);

        return $data;
    }

    private static function formatFunctionalFlow(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'functional_flow_json');

        $data['title'] = $data['title'] ?? '';
        $steps = $data['steps'] ?? [];
        $data['steps'] = collect($steps)->map(function ($step, $index) {
            return [
                'position' => $index + 1,
                'label' => $step['label'] ?? '',
                'color' => $step['color'] ?? '#94A3B8',
            ];
        })->values()->all();
        $data['total_steps'] = count($data['steps']);

        return $data;
    }

    private static function formatSectionHeading(array $data): array
    {
        $data['text'] = $data['text'] ?? '';

        return $data;
    }

    private static function formatExploringSection(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'exploring_section_json');

        $data['title'] = $data['title'] ?? '';
        $data['subtitle'] = $data['subtitle'] ?? '';
        $formulas = $data['formulas'] ?? [];
        $data['formulas'] = collect($formulas)->map(function ($formula, $index) {
            return [
                'position' => $index + 1,
                'label' => self::valueOrDefault($formula['label'] ?? null, 'T'),
                'formula' => self::valueOrDefault($formula['formula'] ?? null, 'T->S->D->(open)'),
                'color' => $formula['color'] ?? '#2457E6',
            ];
        })->values()->all();
        $data['total_formulas'] = count($data['formulas']);

        return $data;
    }

    private static function formatCentreIdentityCard(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'centre_identity_card_json');

        $centres = $data['centres'] ?? [];
        $data['layout'] = count($centres) > 1 ? 'grid' : 'single';
        $data['centres'] = collect($centres)->map(function ($centre, $index) {
            return [
                'position' => $index + 1,
                'centre' => self::valueOrDefault($centre['centre'] ?? null, 'T'),
                'character' => self::valueOrDefault($centre['character'] ?? null, 'Deep, grounded'),
                'description' => self::valueOrDefault($centre['description'] ?? null, 'Resting in depth'),
                'colour' => $centre['colour'] ?? '#2457E6',
            ];
        })->values()->all();
        $data['total_centres'] = count($data['centres']);

        return $data;
    }

    private static function formatFormIdentityCard(array $data): array
    {
        $data = self::decodeEmbeddedJsonPayload($data, 'form_identity_card_json');

        $cards = $data['cards'] ?? [];
        $data['layout'] = count($cards) > 1 ? 'pair' : 'single';
        $data['cards'] = collect($cards)->map(function ($card, $index) {
            return [
                'position' => $index + 1,
                'label' => $card['label'] ?? '',
                'character' => $card['character'] ?? '',
                'description' => $card['description'] ?? '',
                'colour' => $card['colour'] ?? '#2457E6',
            ];
        })->values()->all();
        $data['total_cards'] = count($data['cards']);

        return $data;
    }

    private static function decodeEmbeddedJsonPayload(array $data, string $key): array
    {
        if (empty($data[$key]) || ! is_string($data[$key])) {
            return $data;
        }

        $decoded = json_decode($data[$key], true);
        if (! is_array($decoded)) {
            unset($data[$key]);

            return $data;
        }

        unset($data[$key]);

        return array_replace($data, $decoded);
    }

    private static function valueOrDefault($value, string $default): string
    {
        if (! is_string($value)) {
            return $default;
        }

        $value = trim($value);

        return $value === '' ? $default : $value;
    }

    private static function normalizeTableCell($cell): array
    {
        if (is_array($cell)) {
            return [
                'text' => $cell['text'] ?? '',
                'bold' => (bool) ($cell['bold'] ?? false),
                'bg_color' => $cell['bg_color'] ?? null,
            ];
        }

        return [
            'text' => is_string($cell) ? $cell : '',
            'bold' => false,
            'bg_color' => null,
        ];
    }
}
