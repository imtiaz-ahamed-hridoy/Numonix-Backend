/**
 * Beat Grid — Vite entry point
 *
 * Imports all pure helper functions from beatGridHelpers.js and makes them
 * available on `window` so the Blade template's inline @push('scripts') can
 * reference them via global scope.
 *
 * Inline scripts in _form.blade.php use:
 *   window.newChordBeat = window.newChordBeat || ... (fallback pattern)
 *
 * This module sets the canonical implementations on window.
 */

import {
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
} from './beatGridHelpers';

import { initBeatGridHandlers } from './beatGridHandlers';

// Attach all helpers to window for blade template backward compatibility
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

// Initialise beat grid event handlers once window globals are available
// The inline blade script (which defines beatGrids, renderBeatGrid, etc.)
// runs after this module, so handlers are set up via delegation and will
// match elements added later by the blade template.
if (typeof $ !== 'undefined') {
    initBeatGridHandlers();
}
