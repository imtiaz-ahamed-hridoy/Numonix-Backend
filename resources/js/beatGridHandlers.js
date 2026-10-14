/**
 * Beat Grid Event Handlers
 *
 * jQuery event handlers for the Sentence Visual (Beat Grid) editor.
 * Extracted from _form.blade.php into a dedicated Vite module.
 *
 * References these globals set by the blade template's inline script:
 *   beatGrids          — the beat grid data store
 *   renderBeatGrid()   — re-renders the grid UI (calls serializeBeatGrid internally)
 *   serializeBeatGrid()— serializes grid state to hidden textarea
 *   newChordBeat()     — factory for a default chord beat
 *   newEmptyBeat()     — factory for an empty (rest) beat
 *   newPairBeat()      — factory for a pair beat
 *   escapeHtml()       — HTML escaping helper
 *
 * All helpers (noteObjects, rootObject, chordFunction, etc.) are
 * available via window.* from beatGrid.js → beatGridHelpers.js.
 */

/**
 * Initialise all beat grid event handlers.
 * Call once after the DOM is ready.
 * Uses event delegation so elements added dynamically by the blade template
 * are automatically handled.
 */
export function initBeatGridHandlers() {
    // ─── Type change → re-render ─────────────────────────────────────────
    $(document).on('change', '.bg-type-sel', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        const v = $(this).val();
        if (v === 'pair_v_a' || v === 'pair_v_b') {
            beatGrids[g].rows[r].beats[i] = newPairBeat(v === 'pair_v_a' ? 'A' : 'B');
        } else if (v === 'normal') {
            beatGrids[g].rows[r].beats[i] = newChordBeat();
        } else if (v === 'empty') {
            beatGrids[g].rows[r].beats[i] = newEmptyBeat();
        }
        renderBeatGrid(g);
    });

    // ─── Pair sub-beat: function change ──────────────────────────────────
    $(document).on('change', '.bg-pair-func', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx')), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats[i].type !== 'pair') return;
        const v = $(this).val();
        if (v === '_custom') {
            beatGrids[g].rows[r].beats[i][side].function = 'X';
        } else {
            beatGrids[g].rows[r].beats[i][side].function = v;
        }
        renderBeatGrid(g);
    });

    // ─── Pair variant change ──────────────────────────────────────────────
    $(document).on('change', '.bg-pair-variant', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx'));
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats[i].type !== 'pair') return;
        beatGrids[g].rows[r].beats[i].variant = $(this).val() === 'A' ? 'A' : 'B';
        serializeBeatGrid(g);
    });

    // ─── Pair sub-beat: custom function text ─────────────────────────────
    $(document).on('input', '.bg-pair-func-custom-in', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx')), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats[i].type !== 'pair') return;
        beatGrids[g].rows[r].beats[i][side].function = $(this).val();
        serializeBeatGrid(g);
    });

    // ─── Pair sub-beat: superscript ──────────────────────────────────────
    $(document).on('input', '.bg-pair-super', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx')), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats[i].type !== 'pair') return;
        beatGrids[g].rows[r].beats[i][side].superscript = $(this).val();
        serializeBeatGrid(g);
    });

    // ─── Pair sub-beat: notes ────────────────────────────────────────────
    $(document).on('input', '.bg-pair-notes', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx')), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats[i].type !== 'pair') return;
        beatGrids[g].rows[r].beats[i][side].notes = $(this).val();
        renderBeatGrid(g);
    });

    // ─── Pair sub-beat: annotation ───────────────────────────────────────
    $(document).on('input', '.bg-pair-ann', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx')), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats[i].type !== 'pair') return;
        beatGrids[g].rows[r].beats[i][side].annotation = $(this).val();
        serializeBeatGrid(g);
    });

    // ─── Title / Show Root / Reframe Labels / Playback Label ─────────────
    $(document).on('input change', '.bg-title, .bg-example-title, .bg-example-desc, .bg-tap-play, .bg-show-root, .bg-reframe-labels, .bg-playback', function() {
        const g = $(this).data('bg');
        if (g && beatGrids[g]) serializeBeatGrid(g);
    });

    // ─── Global mode toggle (cells / held) — affects ALL rows ──────────
    $(document).on('change', '.bg-mode', function() {
        const g = $(this).data('bg');
        if (!beatGrids[g]) return;
        const newMode = $(this).val();
        beatGrids[g].rows.forEach(row => {
            if (newMode === 'held') {
                row.segments = row.segments || [];
                delete row.beats;
            } else {
                row.beats = row.beats || [newEmptyBeat(), newEmptyBeat(), newEmptyBeat(), newEmptyBeat()];
                delete row.segments;
            }
        });
        renderBeatGrid(g);
    });

    // ─── Held mode: add segment ──────────────────────────────────────────
    $(document).on('click', '.bg-held-add-segment', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        const segments = beatGrids[g].rows[r].segments || [];
        const from = segments.length ? (parseInt(segments[segments.length - 1].beat_to) || 0) + 1 : 0;
        segments.push({ world: 'T', beat_from: from, beat_to: from, weight: 1, notes: '1 3 5', root: '' });
        beatGrids[g].rows[r].segments = segments;
        renderBeatGrid(g);
    });

    // ─── Held mode: remove segment ───────────────────────────────────────
    $(document).on('click', '.bg-held-del-segment', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), s = parseInt($(this).data('seg'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        beatGrids[g].rows[r].segments.splice(s, 1);
        renderBeatGrid(g);
    });

    // ─── Held mode: field changes (world, from, to, notes, weight, root) ─
    $(document).on('input change', '.bg-held-world, .bg-held-from, .bg-held-to, .bg-held-notes, .bg-held-weight, .bg-held-root', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), s = parseInt($(this).data('seg'));
        if (!beatGrids[g] || !beatGrids[g].rows[r] || !beatGrids[g].rows[r].segments[s]) return;
        const segment = beatGrids[g].rows[r].segments[s];
        if ($(this).hasClass('bg-held-world')) segment.world = $(this).val();
        if ($(this).hasClass('bg-held-from')) segment.beat_from = parseInt($(this).val()) || 0;
        if ($(this).hasClass('bg-held-to')) segment.beat_to = parseInt($(this).val()) || 0;
        if ($(this).hasClass('bg-held-notes')) segment.notes = $(this).val();
        if ($(this).hasClass('bg-held-weight')) segment.weight = parseFloat($(this).val()) || 1;
        if ($(this).hasClass('bg-held-root')) segment.root = $(this).val();
        serializeBeatGrid(g);
    });

    // ─── Function change → re-render (updates preview color) ─────────────
    $(document).on('change', '.bg-func-sel', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        const v = $(this).val();
        if (v === '_custom') {
            beatGrids[g].rows[r].beats[i].function = 'X';
        } else {
            beatGrids[g].rows[r].beats[i].function = v;
        }
        renderBeatGrid(g);
    });

    // ─── Custom function text ────────────────────────────────────────────
    $(document).on('input', '.bg-func-custom-in', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        beatGrids[g].rows[r].beats[i].function = $(this).val();
        serializeBeatGrid(g);
    });

    // ─── Superscript ─────────────────────────────────────────────────────
    $(document).on('input', '.bg-super-in', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        beatGrids[g].rows[r].beats[i].superscript = $(this).val();
        serializeBeatGrid(g);
    });

    // ─── Notes input → live inline preview update ────────────────────────
    $(document).on('input', '.bg-notes-in', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        beatGrids[g].rows[r].beats[i].notes = $(this).val();
        serializeBeatGrid(g);
        const card = $(this).closest('.beat-card');
        const notes = $(this).val().split(/\s+/).filter(function(n){ return n; });
        card.find('.beat-notes-row').html(notes.map(function(n) {
            return '<span class="beat-note">' + escapeHtml(n) + '</span>';
        }).join(' '));
    });

    // ─── Add beat ────────────────────────────────────────────────────────
    $(document).on('click', '.bg-add-beat', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        beatGrids[g].rows[r].beats.push(newEmptyBeat());
        renderBeatGrid(g);
    });

    // ─── Remove beat ─────────────────────────────────────────────────────
    $(document).on('click', '.bg-del-beat', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row'));
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats.length <= 1) return;
        beatGrids[g].rows[r].beats.pop();
        renderBeatGrid(g);
    });

    // ─── Add row ─────────────────────────────────────────────────────────
    $(document).on('click', '.bg-add-row', function() {
        const g = $(this).data('bg');
        if (!beatGrids[g]) return;
        beatGrids[g].rows.push({
            title: '',
            mode: 'cells',
            beats: [newEmptyBeat(), newEmptyBeat(), newEmptyBeat(), newEmptyBeat()],
        });
        renderBeatGrid(g);
    });

    // ─── Remove row ──────────────────────────────────────────────────────
    $(document).on('click', '.bg-del-row', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row'));
        if (!beatGrids[g] || beatGrids[g].rows.length <= 1) return;
        beatGrids[g].rows.splice(r, 1);
        renderBeatGrid(g);
    });

    // ─── Row title change — update opacity to reflect optional state ──────
    $(document).on('input', '.bg-row-title', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row'));
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        beatGrids[g].rows[r].title = $(this).val();
        $(this).css('opacity', $(this).val() ? '1' : '0.45');
        serializeBeatGrid(g);
    });

    // ─── Pair sub-beat: root annotation ──────────────────────────────────
    $(document).on('input', '.bg-pair-root-ann-in', function() {
        const g = $(this).data('bg'), r = parseInt($(this).data('row')), i = parseInt($(this).data('idx')), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || beatGrids[g].rows[r].beats[i].type !== 'pair') return;
        beatGrids[g].rows[r].beats[i][side].root_annotation = $(this).val();
        serializeBeatGrid(g);
    });
}
