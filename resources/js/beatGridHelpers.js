/**
 * Beat Grid Helpers
 *
 * Pure helper functions for the Sentence Visual (Beat Grid) editor.
 * These are extracted from _form.blade.php for testability.
 * Used by the blade template for client-side data transforms.
 *
 * These functions have NO DOM dependencies — they are pure data transforms.
 */

// ─── Core helpers ─────────────────────────────────────────────────────────

export function leadingFor(world) {
    return ({ T: '3', 'T#': '3', S: '6', 'S#': '6', D: '2', 'D#': '7', Db: '4' }[world] || '');
}

export function noteObjects(notes, world) {
    const leading = leadingFor(world);
    return (notes || '')
        .split(/\s+/)
        .filter(Boolean)
        .map((number) => ({
            number,
            leading: leading !== '' && String(number).replace(/[#b]$/, '') === leading,
            octave: 0,
        }));
}

export function rootObject(rootText) {
    const value = String(rootText || '').trim();
    if (!value) return { degree: null, pitch: null };
    if (value.includes('=')) {
        const parts = value.split('=');
        return { degree: parts[0].trim(), pitch: parts.slice(1).join('=').trim() };
    }
    return { degree: null, pitch: value };
}

export function notesToString(notes) {
    if (!Array.isArray(notes)) return notes || '';
    return notes
        .map((n) => (typeof n === 'object' ? n.number || '' : n))
        .filter(Boolean)
        .join(' ');
}

export function rootToText(root) {
    if (!root || typeof root !== 'object') return root || '';
    if (root.degree && root.pitch) return `${root.degree} = ${root.pitch}`;
    return root.pitch || root.degree || '';
}

// ─── Beat factory functions ───────────────────────────────────────────────

export function newChordBeat() {
    return { type: 'normal', function: 'T', superscript: '', notes: '1 3 5', annotation: '', root_annotation: '' };
}

export function newEmptyBeat() {
    return { type: 'empty', function: '', superscript: '', notes: '', annotation: '' };
}

export function newPairBeat(variant = 'B') {
    return {
        type: variant === 'A' ? 'pair_v_a' : 'pair_v_b',
        variant,
        left: { function: 'T', superscript: '', notes: '1 3 5', annotation: 'OLD', span: 1, root_annotation: '' },
        right: { function: 'D', superscript: '', notes: '5 7 2', annotation: 'NEW', span: 1, root_annotation: '' },
    };
}

// ─── Normalization ────────────────────────────────────────────────────────

export function normChordSub(b) {
    if (!b || typeof b !== 'object') return newChordBeat();
    return {
        function: b.function || 'T',
        superscript: b.superscript || '',
        notes: Array.isArray(b.notes) ? b.notes.join(' ') : (b.notes || ''),
        annotation: b.annotation || '',
        root_annotation: b.root_annotation || '',
    };
}

export function normBeat(b) {
    const isPair = b.type === 'pair_v_a' || b.type === 'pair_v_b' || b.type === 'pair';
    const isEmpty = b.type === 'empty';

    if (isPair) {
        const resolvedType = b.type === 'pair' ? 'pair_v_b' : b.type;
        return {
            type: resolvedType,
            variant: b.variant || (b.type === 'pair_v_a' ? 'A' : 'B'),
            left: normChordSub(b.left),
            right: normChordSub(b.right),
        };
    }

    if (isEmpty) {
        return { type: 'empty', annotation: b.annotation || '' };
    }

    // normal or chord (backward compat)
    return {
        type: b.type === 'chord' ? 'normal' : (b.type || 'normal'),
        function: b.function || 'T',
        superscript: b.superscript || '',
        notes: Array.isArray(b.notes) ? b.notes.join(' ') : (b.notes || ''),
        annotation: b.annotation || '',
        root_annotation: b.root_annotation || '',
    };
}

// ─── Canonical ↔ Editor conversions ───────────────────────────────────────

export function canonicalFunctionToChord(fn, role) {
    const chord = normChordSub({
        function: fn.world || 'T',
        notes: notesToString(fn.notes || []),
        annotation: role ? role.toUpperCase() : '',
    });
    return chord;
}

export function canonicalCellToBeat(cell) {
    const functions = Array.isArray(cell.functions) ? cell.functions : [];
    const cellType = cell.type || (
        functions.length === 2
            ? ((cell.reframe || {}).variant === 'A' ? 'pair_v_a' : 'pair_v_b')
            : (functions.length === 0 ? 'empty' : 'normal')
    );

    if (cellType === 'empty' || functions.length === 0) return newEmptyBeat();

    if (cellType === 'pair_v_a' || cellType === 'pair_v_b' || functions.length === 2) {
        return {
            type: cellType === 'pair_v_a' ? 'pair_v_a' : 'pair_v_b',
            variant: cellType === 'pair_v_a' ? 'A' : 'B',
            left: {
                ...canonicalFunctionToChord(functions.find(f => f.reframe_role === 'old') || functions[0], 'old'),
                root_annotation: rootToText(((cell.reframe || {}).roots || {}).old),
            },
            right: {
                ...canonicalFunctionToChord(functions.find(f => f.reframe_role === 'new') || functions[1], 'new'),
                root_annotation: rootToText(((cell.reframe || {}).roots || {}).new),
            },
        };
    }

    const chordBeat = canonicalFunctionToChord(functions[0]);

    return chordBeat;
}

export function canonicalRowsToEditorRows(rows, rulerBeats, mode) {
    if (mode === undefined) mode = 'cells';
    return rows.map(r => {
        if (mode === 'held') {
            return {
                title: r.label || '',
                segments: (r.segments || []).map(s => ({
                    world: s.world || 'T',
                    beat_from: parseInt(s.beat_from) || 0,
                    beat_to: parseInt(s.beat_to) || 0,
                    weight: parseFloat(s.weight) || 1,
                    notes: notesToString(s.notes || []),
                    root: rootToText(s.root),
                })),
            };
        }

        const cellsByBeat = {};
        (r.cells || []).forEach(c => {
            cellsByBeat[Math.max(1, parseInt(c.beat) || 1)] = c;
        });
        const maxBeat = Math.max(
            Array.isArray(rulerBeats) ? rulerBeats.length : 0,
            ...(r.cells || []).map(c => parseInt(c.beat) || 1),
            1,
        );
        const beats = [];
        for (let beatNumber = 1; beatNumber <= maxBeat; beatNumber++) {
            const editorBeat = cellsByBeat[beatNumber]
                ? canonicalCellToBeat(cellsByBeat[beatNumber])
                : newEmptyBeat();
            beats.push(editorBeat);
        }
        return { title: r.label || '', beats };
    });
}

// ─── Serialization: editor → canonical (mimics serializeBeatGrid cells mode) ─

export function chordFunction(beat, role = null) {
    const world = beat.function || beat.world || 'T';
    const payload = { world, notes: noteObjects(beat.notes || '', world) };
    if (beat.superscript) payload.superscript = beat.superscript;
    if (role) payload.reframe_role = role;
    return payload;
}

export function serializeCellBeat(beat, slotIdx) {
    const slot = slotIdx + 1;
    let cellType = 'normal';
    if (beat.type === 'pair' || beat.type === 'pair_v_a' || beat.type === 'pair_v_b') {
        cellType = (beat.variant === 'A') ? 'pair_v_a' : 'pair_v_b';
    } else if (beat.type === 'empty') {
        cellType = 'empty';
    }

    const cell = { type: cellType, beat: slot, functions: [] };

    if (beat.type === 'chord' || beat.type === 'normal') {
        cell.functions = [chordFunction(beat)];
    } else if (beat.type === 'pair' || beat.type === 'pair_v_a' || beat.type === 'pair_v_b') {
        cell.functions = [
            chordFunction(beat.left || {}, 'old'),
            chordFunction(beat.right || {}, 'new'),
        ];
        cell.reframe = {
            variant: beat.variant || 'B',
            roots: {
                old: rootObject((beat.left || {}).root_annotation || ''),
                new: rootObject((beat.right || {}).root_annotation || ''),
            },
        };
    }

    return cell;
}

export function serializeCellRow(editorRow) {
    const cells = (editorRow.beats || []).map((beat, slotIdx) =>
        serializeCellBeat(beat, slotIdx),
    );
    const rowPayload = { cells };
    if (editorRow.title && editorRow.title.trim()) {
        rowPayload.label = editorRow.title.trim();
    }
    return rowPayload;
}

export function serializeCellsMode(editorRows, beats) {
    return {
        beats: beats || Array.from({ length: 4 }, (_, i) => ({
            label: String(i + 1),
        })),
        rows: editorRows.map((row) => {
            if ((row.mode || 'cells') === 'held') {
                return serializeHeldRow(row);
            }
            return serializeCellRow(row);
        }),
    };
}

// ─── Deserialization: canonical → editor ──────────────────────────────────

export function deserializeCellToBeat(cell) {
    const functions = Array.isArray(cell.functions) ? cell.functions : [];
    const cellType = cell.type || (
        functions.length === 2
            ? ((cell.reframe || {}).variant === 'A' ? 'pair_v_a' : 'pair_v_b')
            : (functions.length === 0 ? 'empty' : 'normal')
    );

    if (cellType === 'empty' || functions.length === 0) {
        return { type: 'empty', annotation: '' };
    }

    if (cellType === 'pair_v_a' || cellType === 'pair_v_b' || functions.length === 2) {
        const resolvedType = cellType === 'pair_v_a' ? 'pair_v_a' : 'pair_v_b';
        const oldFn = functions.find((f) => f.reframe_role === 'old') || functions[0] || {};
        const newFn = functions.find((f) => f.reframe_role === 'new') || functions[1] || {};
        return {
            type: resolvedType,
            variant: cellType === 'pair_v_a' ? 'A' : 'B',
            left: {
                function: oldFn.world || 'T',
                superscript: oldFn.superscript || '',
                notes: notesToString(oldFn.notes || []),
                annotation: 'OLD',
                root_annotation: rootToText(((cell.reframe || {}).roots || {}).old),
            },
            right: {
                function: newFn.world || 'D',
                superscript: newFn.superscript || '',
                notes: notesToString(newFn.notes || []),
                annotation: 'NEW',
                root_annotation: rootToText(((cell.reframe || {}).roots || {}).new),
            },
        };
    }

    // Normal / chord
    const fn = functions[0] || {};
    return {
        type: 'normal',
        function: fn.world || 'T',
        superscript: fn.superscript || '',
        notes: notesToString(fn.notes || []),
        annotation: '',
        root_annotation: '',
    };
}

export function deserializeCellRow(canonicalRow) {
    const cells = canonicalRow.cells || [];
    if (cells.length === 0) {
        return { title: canonicalRow.label || '', beats: [] };
    }
    const cellsByBeat = {};
    cells.forEach((c) => {
        cellsByBeat[Math.max(1, parseInt(c.beat) || 1)] = c;
    });
    const maxBeat = Math.max(...cells.map((c) => parseInt(c.beat) || 1), 1);
    const beats = [];
    for (let beatNumber = 1; beatNumber <= maxBeat; beatNumber++) {
        const editorBeat = cellsByBeat[beatNumber]
            ? deserializeCellToBeat(cellsByBeat[beatNumber])
            : { type: 'empty', annotation: '' };
        beats.push(editorBeat);
    }
    return { title: canonicalRow.label || '', beats };
}

export function deserializeCellsRows(canonicalRows, mode) {
    if (mode === undefined) mode = 'cells';
    return canonicalRows.map((r) => {
        if (mode === 'held') {
            return deserializeToHeldRow(r);
        }
        return deserializeCellRow(r);
    });
}

// ─── Held mode serialization ──────────────────────────────────────────────

export function serializeHeldRow(editorRow) {
    const heldRow = {
        segments: (editorRow.segments || []).map((s) => ({
            world: s.world || 'T',
            beat_from: parseInt(s.beat_from) || 0,
            beat_to: parseInt(s.beat_to) || 0,
            weight: parseFloat(s.weight) || 1,
            notes: noteObjects(s.notes || '', s.world || 'T'),
            root: rootObject(s.root || ''),
        })),
    };
    if (editorRow.title && editorRow.title.trim()) {
        heldRow.label = editorRow.title.trim();
    }
    return heldRow;
}

export function serializeHeldRows(editorRows) {
    return editorRows.map((row) => {
        if ((row.mode || 'cells') === 'held') {
            return serializeHeldRow(row);
        }
        return row;
    });
}

// ─── Held mode deserialization ────────────────────────────────────────────

export function deserializeToHeldRow(canonicalRow) {
    return {
        title: canonicalRow.label || '',
        segments: (canonicalRow.segments || []).map((s) => ({
            world: s.world || 'T',
            beat_from: parseInt(s.beat_from) || 0,
            beat_to: parseInt(s.beat_to) || 0,
            weight: parseFloat(s.weight) || 1,
            notes: notesToString(s.notes || []),
            root: rootToText(s.root),
        })),
    };
}

export function deserializeHeldRows(canonicalRows) {
    return canonicalRows.map((r) => deserializeToHeldRow(r));
}
