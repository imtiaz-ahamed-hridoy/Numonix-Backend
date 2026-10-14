/**
 * Beat Grid Standalone Bundle
 * 
 * Combines beatGridHelpers.js and beatGridHandlers.js as a single non-module file
 * for use without Node.js build process.
 * 
 * All functions are attached to window.beatGridHelpers for blade template access.
 */

// ============================================================================
// BEAT GRID HELPERS (Pure Functions)
// ============================================================================

function leadingFor(world) {
    return ({ T: '3', 'T#': '3', S: '6', 'S#': '6', D: '2', 'D#': '7', Db: '4' }[world] || '');
}

function noteObjects(notes, world) {
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

function rootObject(rootText) {
    const value = String(rootText || '').trim();
    if (!value) return { degree: null, pitch: null };
    if (value.includes('=')) {
        const parts = value.split('=');
        return { degree: parts[0].trim(), pitch: parts.slice(1).join('=').trim() };
    }
    return { degree: null, pitch: value };
}

function notesToString(notes) {
    if (!Array.isArray(notes)) return notes || '';
    return notes
        .map((n) => (typeof n === 'object' ? n.number || '' : n))
        .filter(Boolean)
        .join(' ');
}

function rootToText(root) {
    if (!root || typeof root !== 'object') return root || '';
    if (root.degree && root.pitch) return `${root.degree} = ${root.pitch}`;
    return root.pitch || root.degree || '';
}

// ─── Beat factory functions ───────────────────────────────────────────────

function newChordBeat() {
    return { type: 'normal', function: 'T', superscript: '', notes: '1 3 5', annotation: '', root_annotation: '' };
}

function newEmptyBeat() {
    return { type: 'empty', function: '', superscript: '', notes: '', annotation: '' };
}

function newPairBeat(variant = 'B') {
    return {
        type: variant === 'A' ? 'pair_v_a' : 'pair_v_b',
        variant,
        left: { function: 'T', superscript: '', notes: '1 3 5', annotation: 'OLD', span: 1, root_annotation: '' },
        right: { function: 'D', superscript: '', notes: '5 7 2', annotation: 'NEW', span: 1, root_annotation: '' },
    };
}

// ─── Normalization ────────────────────────────────────────────────────────

function normChordSub(b) {
    if (!b || typeof b !== 'object') return newChordBeat();
    return {
        function: b.function || 'T',
        superscript: b.superscript || '',
        notes: Array.isArray(b.notes) ? b.notes.join(' ') : (b.notes || ''),
        annotation: b.annotation || '',
        root_annotation: b.root_annotation || '',
    };
}

function normBeat(b) {
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

function canonicalFunctionToChord(fn, role) {
    const chord = normChordSub({
        function: fn.world || 'T',
        notes: notesToString(fn.notes || []),
        annotation: role ? role.toUpperCase() : '',
    });
    return chord;
}

function canonicalCellToBeat(cell) {
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

function canonicalRowsToEditorRows(rows, rulerBeats) {
    return rows.map(r => {
        if ((r.mode || 'cells') === 'held') {
            return {
                title: r.label || '',
                mode: 'held',
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
        return { title: r.label || '', mode: 'cells', beats };
    });
}

// ─── Serialization: editor → canonical (mimics serializeBeatGrid cells mode) ─

function chordFunction(beat, role = null) {
    const world = beat.function || beat.world || 'T';
    const payload = { world, notes: noteObjects(beat.notes || '', world) };
    if (beat.superscript) payload.superscript = beat.superscript;
    if (role) payload.reframe_role = role;
    return payload;
}

function serializeCellBeat(beat, slotIdx) {
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

function serializeCellRow(editorRow) {
    const cells = (editorRow.beats || []).map((beat, slotIdx) =>
        serializeCellBeat(beat, slotIdx),
    );
    const rowPayload = { mode: 'cells', cells };
    if (editorRow.title && editorRow.title.trim()) {
        rowPayload.label = editorRow.title.trim();
    }
    return rowPayload;
}

function serializeCellsMode(editorRows, beats) {
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

function deserializeCellToBeat(cell) {
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

function deserializeCellRow(canonicalRow) {
    const cells = canonicalRow.cells || [];
    if (cells.length === 0) {
        return { title: canonicalRow.label || '', mode: 'cells', beats: [] };
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
    return { title: canonicalRow.label || '', mode: 'cells', beats };
}

function deserializeCellsRows(canonicalRows) {
    return canonicalRows.map((r) => {
        if ((r.mode || 'cells') === 'held') {
            return deserializeToHeldRow(r);
        }
        return deserializeCellRow(r);
    });
}

// ─── Held mode serialization ──────────────────────────────────────────────

function serializeHeldRow(editorRow) {
    const heldRow = {
        mode: 'held',
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

function serializeHeldRows(editorRows) {
    return editorRows.map((row) => {
        if ((row.mode || 'cells') === 'held') {
            return serializeHeldRow(row);
        }
        return row;
    });
}

// ─── Held mode deserialization ────────────────────────────────────────────

function deserializeToHeldRow(canonicalRow) {
    return {
        title: canonicalRow.label || '',
        mode: 'held',
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

function deserializeHeldRows(canonicalRows) {
    return canonicalRows.map((r) => {
        if ((r.mode || 'cells') === 'held') {
            return deserializeToHeldRow(r);
        }
        return r;
    });
}

// ============================================================================
// ATTACH HELPERS TO WINDOW
// ============================================================================

window.beatGridHelpers = {
    leadingFor,
    noteObjects,
    rootObject,
    notesToString,
    rootToText,
    newChordBeat,
    newEmptyBeat,
    newPairBeat,
    normChordSub,
    normBeat,
    canonicalFunctionToChord,
    canonicalCellToBeat,
    canonicalRowsToEditorRows,
    chordFunction,
    serializeCellBeat,
    serializeCellRow,
    serializeCellsMode,
    deserializeCellToBeat,
    deserializeCellRow,
    deserializeCellsRows,
    serializeHeldRow,
    serializeHeldRows,
    deserializeToHeldRow,
    deserializeHeldRows,
};

// Also assign directly to window for simple global access (used by inline blade JS)
window.leadingFor = leadingFor;
window.noteObjects = noteObjects;
window.rootObject = rootObject;
window.notesToString = notesToString;
window.rootToText = rootToText;
window.newChordBeat = newChordBeat;
window.newEmptyBeat = newEmptyBeat;
window.newPairBeat = newPairBeat;
window.normChordSub = normChordSub;
window.normBeat = normBeat;
window.canonicalFunctionToChord = canonicalFunctionToChord;
window.canonicalCellToBeat = canonicalCellToBeat;
window.canonicalRowsToEditorRows = canonicalRowsToEditorRows;
window.chordFunction = chordFunction;
window.serializeCellBeat = serializeCellBeat;
window.serializeCellRow = serializeCellRow;
window.serializeCellsMode = serializeCellsMode;
window.deserializeCellToBeat = deserializeCellToBeat;
window.deserializeCellRow = deserializeCellRow;
window.deserializeCellsRows = deserializeCellsRows;
window.serializeHeldRow = serializeHeldRow;
window.serializeHeldRows = serializeHeldRows;
window.deserializeToHeldRow = deserializeToHeldRow;
window.deserializeHeldRows = deserializeHeldRows;

console.log('✓ Beat Grid Helpers loaded successfully (standalone version)');
