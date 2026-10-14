<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Normalizes raw learning element data into clean, structured arrays for database storage and API responses.
 */
trait ElementDataNormalizer
{
    protected /**
     * Normalize raw element data into a clean structured array based on element type.
     */
function normalizeElementData(string $type, array|string|null $data): array
    {
        $data = is_array($data) ? $data : ['text' => $data];
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value !== null && $value !== '') {
                $clean[$key] = $value;
            }
        }

        if (isset($clean['items_text'])) {
            $clean['items'] = $this->linesToArray($clean['items_text']);
            unset($clean['items_text']);
        }

        if (isset($clean['cards_text'])) {
            $clean['cards'] = $this->linesToCards($clean['cards_text']);
            unset($clean['cards_text']);
        }

        if (isset($clean['terms_text'])) {
            $clean['items'] = $this->linesToCards($clean['terms_text']);
            unset($clean['terms_text']);
        }

        if (isset($clean['checks_text'])) {
            $clean['items'] = collect($this->linesToArray($clean['checks_text']))
                ->map(fn ($item) => ['text' => $item, 'checked' => false])
                ->values()
                ->all();
            unset($clean['checks_text']);
        }

        // -- Enhanced Table: if table_json is provided, use it as the full data --
        if ($type === 'table' && isset($clean['table_json'])) {
            $decoded = json_decode($clean['table_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
            unset($clean['table_json']);
        }

        // -- Beat Grid (Sentence Visual): if sentence_json is provided, use it --
        if ($type === 'sentence_visual' && isset($clean['sentence_json'])) {
            $decoded = json_decode($clean['sentence_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
            unset($clean['sentence_json']);
        }

        // ===================================================================
        // FNH Architecture & Case Study element types
        // ===================================================================

        // -- what_fnh_reveals: "WHAT FNH REVEALS" callout box --
        if ($type === 'what_fnh_reveals') {
            return [
                'label'       => $clean['label'] ?? 'WHAT FNH REVEALS',
                'text'        => $clean['text'] ?? '',
                'label_color' => $clean['label_color'] ?? '#7C3AED',
            ];
        }

        // -- template_form_table: TEMPLATE / T FORM / T# FORM table --
        if ($type === 'template_form_table' && isset($clean['template_form_table_json'])) {
            $decoded = json_decode($clean['template_form_table_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // -- defining_behaviours_card: DEFINING BEHAVIOURS card --
        if ($type === 'defining_behaviours_card') {
            $bullets = [];
            if (isset($clean['bullets_text'])) {
                $bullets = $this->linesToArray($clean['bullets_text']);
                unset($clean['bullets_text']);
            } elseif (isset($clean['bullets']) && is_array($clean['bullets'])) {
                $bullets = $clean['bullets'];
            }

            return [
                'number'        => $clean['number'] ?? '',
                'title'         => $clean['title'] ?? '',
                'section_label' => $clean['section_label'] ?? 'DEFINING BEHAVIOURS',
                'bullets'       => $bullets,
                'best_for'      => [
                    'label' => $clean['best_for_label'] ?? 'Best for:',
                    'text'  => $clean['best_for_text'] ?? '',
                ],
            ];
        }

        // -- movement_quality_table: MOVEMENT / EMOTIONAL QUALITY table --
        if ($type === 'movement_quality_table' && isset($clean['movement_quality_table_json'])) {
            $decoded = json_decode($clean['movement_quality_table_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // -- centre_character_table: CENTRE / CHARACTER table --
        if ($type === 'centre_character_table' && isset($clean['centre_character_table_json'])) {
            $decoded = json_decode($clean['centre_character_table_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // -- functional_flow: FUNCTIONAL FLOW chain --
        if ($type === 'functional_flow' && isset($clean['functional_flow_json'])) {
            $decoded = json_decode($clean['functional_flow_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // -- section_heading: Section heading text --
        if ($type === 'section_heading') {
            return ['text' => $clean['text'] ?? ''];
        }

        // -- exploring_section: Exploring section with formula tags --
        if ($type === 'exploring_section' && isset($clean['exploring_section_json'])) {
            $decoded = json_decode($clean['exploring_section_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // -- centre_identity_card: Centre identity card(s) --
        if ($type === 'centre_identity_card' && isset($clean['centre_identity_card_json'])) {
            $decoded = json_decode($clean['centre_identity_card_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // -- form_identity_card: Form identity card(s) --
        if ($type === 'form_identity_card' && isset($clean['form_identity_card_json'])) {
            $decoded = json_decode($clean['form_identity_card_json'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        if (isset($clean['headers_text'])) {
            $clean['headers'] = $this->splitRow($clean['headers_text']);
            unset($clean['headers_text']);
        }

        if (isset($clean['rows_text'])) {
            $clean['rows'] = collect(preg_split('/\r\n|\r|\n/', $clean['rows_text']))
                ->map(fn ($row) => $this->splitRow($row))
                ->filter(fn ($row) => count(array_filter($row, fn ($cell) => $cell !== '')) > 0)
                ->values()
                ->all();
            unset($clean['rows_text']);
        }

        if (isset($clean['sentence_rows_text'])) {
            $clean['sentence'] = [
                'rows' => collect(preg_split('/\r\n|\r|\n/', $clean['sentence_rows_text']))
                    ->map(fn ($row) => $this->splitRow($row))
                    ->filter(fn ($row) => count(array_filter($row, fn ($cell) => $cell !== '')) > 0)
                    ->values()
                    ->all(),
            ];
            unset($clean['sentence_rows_text']);
        }

        if ($type === 'checklist' && isset($clean['items'])) {
            $clean['items'] = collect($clean['items'])
                ->map(fn ($item) => ['text' => $item, 'checked' => false])
                ->values()
                ->all();
        }

        // Preserve table style booleans
        if ($type === 'table') {
            if (isset($clean['rounded'])) {
                $clean['rounded'] = filter_var($clean['rounded'], FILTER_VALIDATE_BOOLEAN);
            }
            // Remove deprecated header fields
            unset($clean['show_header'], $clean['headers'], $clean['headers_text']);
        }

        // Remove deprecated visual styling fields
        unset($clean['variant'], $clean['accent'], $clean['layout']);

        return $clean;
    }

    protected /**
     * Split a multi-line string into an array of trimmed values.
     */
function linesToArray(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    protected /**
     * Split a pipe-delimited row string into an array of trimmed values.
     */
function splitRow(string $value): array
    {
        return collect(explode('|', $value))
            ->map(fn ($item) => trim($item))
            ->values()
            ->all();
    }

    protected /**
     * Convert a multi-line card-formatted string into an array of card arrays with title and body.
     */
function linesToCards(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(function ($item) {
                $parts = array_pad($this->splitRow($item), 2, null);
                $title = $parts[0] !== null ? trim($parts[0]) : null;
                $body  = $parts[1] !== null ? trim($parts[1]) : null;

                return [
                    'title'      => $title,
                    'body'       => $body,
                    'term'       => $title,
                    'definition' => $body,
                ];
            })
            ->filter(fn ($item) => $item['title'] || $item['body'])
            ->values()
            ->all();
    }

    protected /**
     * Store a file to the public disk and return its URL.
     */
function storePublicFile($file, string $directory): string
    {
        return Storage::url($file->store($directory, 'public'));
    }

    /**
     * Delete a file from the public disk by its URL or path.
     */
    protected function deletePublicFile(?string $url): void
    {
        if (!$url) {
            return;
        }

        $path = Str::after($url, '/storage/');

        if ($path === $url) {
            $path = ltrim(Str::after($url, 'storage/'), '/');
        }

        if (!$path) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }
}
