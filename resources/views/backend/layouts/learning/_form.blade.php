@php
    $categoryLabels = [
        'lesson'           => 'Lesson',
        'core_theory'      => 'Core Theory',
        'case_study'       => 'Case Study',
        'fnh_architecture' => 'FNH Architecture',
        'advanced_insight' => 'Advanced Insights',
    ];

    // Standard element labels (lesson, core_theory, advanced_insight)
    $standardElementLabels = [
        // -- Structure / Layout -------------------------------------------
        'section_label'    => '🏷️ Section Label',
        // -- Text & Typography --------------------------------------------
        'title'            => 'H1 Title',
        'subtitle'         => 'H2 Subtitle',
        'text'             => 'Paragraph',
        // -- Lists & Cards ------------------------------------------------
        'bullet_list'      => 'Bullet List',
        'checklist'        => 'Checklist',
        // -- Callouts ----------------------------------------------------
        'info_box'         => 'Info Box',
        'key_insight'      => 'Key Insight Box',
        // -- Data --------------------------------------------------------
        'table'            => 'Table / Key Map',
        'sentence_visual'  => 'Sentence Visual (Beat Grid)',
        // -- Media --------------------------------------------------------
        'image'            => 'Image',
        'transparent_image'=> 'Transparent Image',
        'audio'            => 'Audio',
    ];

    // FNH Architecture & Case Study element labels (fnh_architecture, case_study)
    $fnhElementLabels = [
        'what_fnh_reveals'          => '🟣 What FNH Reveals',
        'template_form_table'       => '📊 Template / T Form / T# Form Table',
        'defining_behaviours_card'  => '📋 Defining Behaviours Card',
        'movement_quality_table'    => '📊 Movement / Emotional Quality Table',
        'centre_character_table'    => '📊 Centre / Character / Home Table',
        'functional_flow'           => '🔗 Functional Flow Chain',

        'section_heading'           => '📌 Section Heading',
        'exploring_section'         => '🔍 Exploring Section (with Formulas)',
        'centre_identity_card'      => '🎯 Centre Identity Card(s)',
        'form_identity_card'        => '🎵 Form Identity Card(s)',
    ];

    // Shared standard labels that are also available in FNH/Case Study categories
    // Must match LearningPageElement::FNH_SHARED_STANDARD_TYPES
    $sharedStandardElementLabels = array_intersect_key($standardElementLabels, array_flip([
        'title',
        'text',
        'table',
        'info_box',
        'key_insight',
        'image',
        'transparent_image',
        'sentence_visual',
    ]));

    // Combined labels for JS
    $elementLabels = array_merge($standardElementLabels, $fnhElementLabels);

    // FNH categories for JS filtering
    $fnhCategories = ['fnh_architecture', 'case_study'];

    $initialPages = $learning
        ? $learning->pages->map(function ($page) {
            return [
                'id'            => $page->id,
                'title'         => $page->title,
                'section_title' => $page->section_title,
                'elements'      => $page->elements->map(function ($el) {
                    $formattedData = \App\Services\LearningElementFormatter::format(
                        $el->type, 
                        $el->data ?? [], 
                        [], 
                        $el->id,
                        true
                    );
                    return [
                        'id'             => $el->id,
                        'position'       => $el->position,
                        'type'           => $el->type,
                        'data'           => $formattedData,
                        'file_url'       => $el->getRawOriginal('file_url'),
                        'public_file_url'=> $el->file_url,
                    ];
                })->values(),
            ];
        })->values()
        : [];
@endphp

<div class="row">
  <div class="col-12">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="learningForm" class="learning-editor-form">
      @csrf

      {{-- -- Learning Meta Card --------------------------------------- --}}
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0">{{ $formTitle }}</h4>
          <a href="{{ route('learning.index') }}" class="btn btn-outline-secondary">← Back</a>
        </div>
        <div class="card-body">
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Learning Type <span class="text-danger">*</span></label>
              <select name="category" id="category" class="form-select" required>
                @foreach($categoryLabels as $value => $label)
                  <option value="{{ $value }}" @selected(old('category', $learning?->category ?? 'lesson') === $value)>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control"
                value="{{ old('title', $learning?->title) }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Subtitle</label>
              <input type="text" name="subtitle" class="form-control"
                value="{{ old('subtitle', $learning?->subtitle) }}" placeholder="Short subtitle">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Slug <small class="text-muted fw-normal">(auto-generated if empty)</small></label>
              <input type="text" name="slug" class="form-control"
                value="{{ old('slug', $learning?->slug) }}" placeholder="url-friendly-slug">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Read Time</label>
              <input type="text" name="read_time" class="form-control"
                value="{{ old('read_time', $learning?->read_time) }}" placeholder="15 min read">
            </div>
            <div class="col-md-4 mb-3" id="groupField">
              <label class="form-label fw-semibold">Group <small class="text-muted fw-normal">(for Case Study / FNH)</small></label>
              <select name="group_id" class="form-select" id="groupSelect">
                <option value="">- None -</option>
                @foreach($groups as $group)
                  <option value="{{ $group->id }}" data-category="{{ $group->category }}" @selected(old('group_id', $learning?->group_id) == $group->id)>
                    {{ $group->label }} ({{ ucwords(str_replace('_', ' ', $group->category)) }}){{ $group->is_premium ? ' 🔒' : '' }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select" required>
                @foreach(['active' => 'Active', 'coming_soon' => 'Coming Soon', 'inactive' => 'Inactive'] as $value => $label)
                  <option value="{{ $value }}" @selected(old('status', $learning?->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Published</label>
              <select name="is_published" class="form-select">
                <option value="1" @selected((string)old('is_published', $learning?->is_published ?? 1) === '1')>Yes</option>
                <option value="0" @selected((string)old('is_published', $learning?->is_published ?? 1) === '0')>No</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Is Premium?</label>
              <select name="is_premium" class="form-select">
                <option value="0" @selected((string)old('is_premium', $learning?->is_premium ?? 0) === '0')>No (Foundation / Free)</option>
                <option value="1" @selected((string)old('is_premium', $learning?->is_premium ?? 0) === '1')>Yes (Premium)</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Guest Access Override</label>
              <select name="guest_access_override" class="form-select">
                <option value="null" @selected(old('guest_access_override', $learning ? var_export($learning->is_guest_accessible, true) : 'null') === 'NULL')>🟢 Follow Category Default</option>
                <option value="true" @selected(old('guest_access_override', $learning ? var_export($learning->is_guest_accessible, true) : 'null') === 'true')>🔵 Always Unlocked</option>
                <option value="false" @selected(old('guest_access_override', $learning ? var_export($learning->is_guest_accessible, true) : 'null') === 'false')>🔴 Always Locked</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Thumbnail</label>
              <input type="file" name="thumbnail" class="form-control" accept="image/*">
              @if($learning?->thumbnail_image_url)
                <img src="{{ $learning->thumbnail_image_url }}" alt="Thumbnail" class="mt-2 rounded" style="max-height:70px;">
              @endif
            </div>
            <div class="col-12 mb-3">
              <label class="form-label fw-semibold">Key Takeaways / Summary <small class="text-muted fw-normal">(shown as numbered list on the app's Lesson Summary screen)</small></label>
              <input type="hidden" name="summary" id="summaryHidden" value="{{ old('summary', $learning?->summary) }}">
              <div class="border p-3 bg-light" style="overflow:hidden;">
                <div id="takeawaysList"></div>
                <div id="takeawaysEmpty" class="text-center text-muted py-3 mb-3" style="display:none;">
                  <small>No key takeaways yet. Add your first one below.</small>
                </div>
                <div class="d-flex gap-2 mt-2">
                  <input type="text" class="form-control" id="takeawayInput" placeholder="Type a key takeaway and press Enter or click Add" maxlength="500">
                  <button type="button" class="btn btn-primary flex-shrink-0" id="addTakeawayBtn" style="white-space:nowrap;">+ Add</button>
                </div>
              </div>
              <small class="text-muted mt-1 d-block">Each item becomes a numbered Key Takeaway on the Lesson Summary screen. Click ✕ to remove.</small>
            </div>
          </div>
        </div>
      </div>

      {{-- -- Help Banner ----------------------------------------------- --}}
      <div class="alert alert-info d-flex align-items-start gap-3 mb-3">
        <span style="font-size:22px;">💡</span>
        <div>
          <strong>How to build pages:</strong>
          Click <strong>Add Page</strong> to create a new page (screen), then select an element type and click
          <strong>Add Element</strong> to add content blocks.
          Use <strong>↑ ↓</strong> to reorder elements, <strong>✕ Remove</strong> to delete them.
          Each page will be presented as a swipeable screen in the app.
        </div>
      </div>

      {{-- -- Pages Builder --------------------------------------------- --}}
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0">📚 Pages & Elements</h4>
          <button type="button" class="btn btn-primary" id="addPageBtn">+ Add Page</button>
        </div>
        <div class="card-body">
          <div id="pagesContainer"></div>
          <div class="text-muted text-center py-5 d-none" id="emptyBuilderState">
            <div style="font-size:48px;">📖</div>
            <p class="mt-2">No pages yet. Click <strong>Add Page</strong> to get started.</p>
          </div>
        </div>
      </div>

      <div class="text-end mb-5">
        <button type="submit" class="btn btn-success btn-lg">💾 {{ $submitLabel }}</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
/**
 * -----------------------------------------------------------------------------
 * BEAT GRID HELPERS - Pure helper functions used by the beat grid and
 * sentence visual builder. These are defined at global scope so they can be
 * used both inside and outside of $(document).ready().
 * -----------------------------------------------------------------------------
 */

// --- Music theory helpers -----------------------------------------------
function leadingFor(world) {
    return ({ T: '3', 'T#': '3', S: '6', 'S#': '6', D: '2', 'D#': '7', Db: '4' }[world] || '');
}

function noteObjects(notes, world) {
    var leading = leadingFor(world);
    return (notes || '')
        .split(/\s+/)
        .filter(Boolean)
        .map(function(number) {
            return {
                number: number,
                leading: leading !== '' && String(number).replace(/[#b]$/, '') === leading,
                octave: 0,
            };
        });
}

function rootObject(rootText) {
    var value = String(rootText || '').trim();
    if (!value) return { degree: null, pitch: null };
    if (value.indexOf('=') !== -1) {
        var parts = value.split('=');
        return { degree: parts[0].trim(), pitch: parts.slice(1).join('=').trim() };
    }
    return { degree: null, pitch: value };
}

function notesToString(notes) {
    if (!Array.isArray(notes)) return notes || '';
    return notes
        .map(function(n) { return (typeof n === 'object' ? n.number || '' : n); })
        .filter(Boolean)
        .join(' ');
}

function rootToText(root) {
    if (!root || typeof root !== 'object') return root || '';
    if (root.degree && root.pitch) return root.degree + ' = ' + root.pitch;
    return root.pitch || root.degree || '';
}

// --- Beat factory functions -----------------------------------------------
function newChordBeat() {
    return { type: 'normal', function: 'T', superscript: '', notes: '1 3 5', annotation: '', root_annotation: '' };
}

function newEmptyBeat() {
    return { type: 'empty', function: '', superscript: '', notes: '', annotation: '' };
}

function newPairBeat(variant) {
    if (variant === undefined) variant = 'B';
    return {
        type: variant === 'A' ? 'pair_v_a' : 'pair_v_b',
        variant: variant,
        editable: false,
        allowable_notes: '',
        centre_voicing: '',
        octave_range: '',
        left: { function: 'T', superscript: '', notes: '1 3 5', annotation: 'OLD', span: 1, root_annotation: '' },
        right: { function: 'D', superscript: '', notes: '5 7 2', annotation: 'NEW', span: 1, root_annotation: '' },
    };
}

// --- Normalization --------------------------------------------------------
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
    var isPair = b.type === 'pair_v_a' || b.type === 'pair_v_b' || b.type === 'pair';
    var isEmpty = b.type === 'empty';

    if (isPair) {
        var resolvedType = b.type === 'pair' ? 'pair_v_b' : b.type;
        return {
            type: resolvedType,
            variant: b.variant || (b.type === 'pair_v_a' ? 'A' : 'B'),
            editable: !!b.editable,
            allowable_notes: Array.isArray(b.allowable_notes) ? b.allowable_notes.join(' ') : (b.allowable_notes || ''),
            centre_voicing: b.centre_voicing || '',
            octave_range: b.octave_range !== undefined && b.octave_range !== null ? b.octave_range : '',
            left: normChordSub(b.left),
            right: normChordSub(b.right),
        };
    }

    if (isEmpty) {
        return { type: 'empty', annotation: b.annotation || '' };
    }

    return {
        type: b.type === 'chord' ? 'normal' : (b.type || 'normal'),
        function: b.function || 'T',
        superscript: b.superscript || '',
        notes: Array.isArray(b.notes) ? b.notes.join(' ') : (b.notes || ''),
        annotation: b.annotation || '',
        root_annotation: b.root_annotation || '',
    };
}

// --- Canonical ↔ Editor conversions ---------------------------------------
function canonicalFunctionToChord(fn, role) {
    var chord = normChordSub({
        function: fn.world || 'T',
        superscript: fn.superscript || '',
        notes: notesToString(fn.notes || []),
        annotation: fn.annotation || (role ? role.toUpperCase() : ''),
    });
    chord.type = 'normal';
    return chord;
}

function canonicalCellToBeat(cell) {
    var functions = Array.isArray(cell.functions) ? cell.functions : [];
    var cellType = cell.type || (
        functions.length === 2
            ? ((cell.reframe || {}).variant === 'A' ? 'pair_v_a' : 'pair_v_b')
            : (functions.length === 0 ? 'empty' : 'normal')
    );

    var cellAnn = cell.annotation || (functions[0] && functions[0].annotation) || '';

    if (cellType === 'empty' || functions.length === 0) {
        var emptyBeat = newEmptyBeat();
        if (cellAnn) {
            emptyBeat.annotation = cellAnn;
        }
        return emptyBeat;
    }

    if (cellType === 'pair_v_a' || cellType === 'pair_v_b' || functions.length === 2) {
        var pairBeat = {
            type: cellType === 'pair_v_a' ? 'pair_v_a' : 'pair_v_b',
            variant: cellType === 'pair_v_a' ? 'A' : 'B',
            editable: !!cell.editable,
            allowable_notes: Array.isArray(cell.allowable_notes) ? cell.allowable_notes.join(' ') : (cell.allowable_notes || ''),
            centre_voicing: cell.centre_voicing || '',
            octave_range: cell.octave_range !== undefined && cell.octave_range !== null ? cell.octave_range : '',
            left: Object.assign({}, canonicalFunctionToChord(
                functions.find(function(f) { return f.reframe_role === 'old'; }) || functions[0], 'old'
            ), {
                root_annotation: rootToText(((cell.reframe || {}).roots || {}).old)
            }),
            right: Object.assign({}, canonicalFunctionToChord(
                functions.find(function(f) { return f.reframe_role === 'new'; }) || functions[1], 'new'
            ), {
                root_annotation: rootToText(((cell.reframe || {}).roots || {}).new)
            }),
        };
        if (cellAnn) {
            pairBeat.annotation = cellAnn;
        }
        return pairBeat;
    }

    var chordBeat = canonicalFunctionToChord(functions[0]);
    if (cellAnn) {
        chordBeat.annotation = cellAnn;
    }
    return chordBeat;
}

function canonicalRowsToEditorRows(rows, rulerBeats, mode) {
    if (mode === undefined) mode = 'cells';
    return rows.map(function(r) {
        var rowMode = r.mode || mode;
        var startFromCell = (r.start_from_cell !== undefined && r.start_from_cell !== null) ? r.start_from_cell : '';
        if (rowMode === 'held') {
            return {
                title: r.label || '',
                mode: 'held',
                start_from_cell: startFromCell,
                segments: (r.segments || []).map(function(s) {
                    return {
                        world: s.world || 'T',
                        beat_from: parseInt(s.beat_from) || 0,
                        beat_to: parseInt(s.beat_to) || 0,
                        weight: parseFloat(s.weight) || 1,
                        notes: notesToString(s.notes || []),
                        root: rootToText(s.root),
                    };
                }),
            };
        }

        var cellsByBeat = {};
        (r.cells || []).forEach(function(c) {
            cellsByBeat[Math.max(1, parseInt(c.beat) || 1)] = c;
        });
        var maxBeat = Array.isArray(rulerBeats) ? rulerBeats.length : 0;
        (r.cells || []).forEach(function(c) {
            maxBeat = Math.max(maxBeat, parseInt(c.beat) || 1);
        });
        if (maxBeat < 1) maxBeat = 1;
        var beats = [];
        for (var beatNumber = 1; beatNumber <= maxBeat; beatNumber++) {
            var editorBeat = cellsByBeat[beatNumber]
                ? canonicalCellToBeat(cellsByBeat[beatNumber])
                : newEmptyBeat();
            beats.push(editorBeat);
        }
        return { title: r.label || '', start_from_cell: startFromCell, beats: beats };
    });
}

// --- Serialization: editor -> canonical ---------------------------------
function chordFunction(beat, role) {
    if (role === undefined) role = null;
    var world = beat.function || beat.world || 'T';
    var payload = { world: world, notes: noteObjects(beat.notes || '', world) };
    if (beat.superscript) payload.superscript = beat.superscript;
    if (beat.annotation) payload.annotation = beat.annotation;
    if (role) payload.reframe_role = role;
    return payload;
}

function serializeCellBeat(beat, slotIdx) {
    var slot = slotIdx + 1;
    var cellType = 'normal';
    if (beat.type === 'pair' || beat.type === 'pair_v_a' || beat.type === 'pair_v_b') {
        cellType = (beat.variant === 'A') ? 'pair_v_a' : 'pair_v_b';
    } else if (beat.type === 'empty') {
        cellType = 'empty';
    }

    var cell = { type: cellType, beat: slot, functions: [] };
    if (beat.annotation) {
        cell.annotation = beat.annotation;
    }

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
    var cells = (editorRow.beats || []).map(function(beat, slotIdx) {
        return serializeCellBeat(beat, slotIdx);
    });
    var rowPayload = { mode: 'cells', cells: cells };
    if (editorRow.title && editorRow.title.trim()) {
        rowPayload.label = editorRow.title.trim();
    }
    return rowPayload;
}

// --- Held mode serialization ----------------------------------------------
function serializeHeldRow(editorRow) {
    var heldRow = {
        mode: 'held',
        segments: (editorRow.segments || []).map(function(s) {
            return {
                world: s.world || 'T',
                beat_from: parseInt(s.beat_from) || 0,
                beat_to: parseInt(s.beat_to) || 0,
                weight: parseFloat(s.weight) || 1,
                notes: noteObjects(s.notes || '', s.world || 'T'),
                root: rootObject(s.root || ''),
            };
        }),
    };
    if (editorRow.title && editorRow.title.trim()) {
        heldRow.label = editorRow.title.trim();
    }
    return heldRow;
}

/**
 * -----------------------------------------------------------------------------
 * LEARNING FORM INIT
 * -----------------------------------------------------------------------------
 */
$(document).ready(function() {
    'use strict';

    console.log('📚 Initializing Learning Form...', { timestamp: new Date().toISOString() });

    // Parse JSON data from PHP
    var initialPages = @json($initialPages) || [];
    var allElementLabels = @json($elementLabels) || {};
    var standardElementLabels = @json($standardElementLabels) || {};
    var fnhElementLabels = JSON.parse(JSON.stringify(@json($fnhElementLabels) || {}));
    var sharedStandardElementLabels = @json($sharedStandardElementLabels) || {};
    var fnhCategories = @json($fnhCategories) || [];
    var elementLabels = JSON.parse(JSON.stringify(allElementLabels));

    console.log('📊 Data Summary:', {
        pagesCount: initialPages.length,
        elementTypes: Object.keys(allElementLabels).length,
        fnhCategories: fnhCategories
    });

    // --- Global state (exposed on window for submit-time re-serialization) ----
    var pageIndex = 0;
    window.tableGrids    = window.tableGrids    || {};
    window.tableRowColors = window.tableRowColors || {};
    window.tableColColors = window.tableColColors || {};
    window.beatGrids     = window.beatGrids     || {};
    window.fnhData       = window.fnhData       || {};

    // Local aliases pointing at the same objects so all existing code works
    var tableGrids    = window.tableGrids;
    var tableRowColors = window.tableRowColors;
    var tableColColors = window.tableColColors;
    var beatGrids     = window.beatGrids;
    var fnhData       = window.fnhData;

    // --- Category-based Element Type Filtering ------------------------------
    function getCurrentCategory() {
        return $('#category').val() || '';
    }

    function getActiveLabels() {
        var cat = getCurrentCategory();
        if (fnhCategories.indexOf(cat) !== -1) {
            var labels = Object.assign({}, fnhElementLabels, sharedStandardElementLabels);
            if (cat === 'case_study') {
                labels['audio'] = standardElementLabels['audio'];
            }
            return labels;
        }
        return standardElementLabels;
    }

    function refreshElementTypeDropdowns() {
        var labels = getActiveLabels();
        elementLabels = labels;
        $('select[id^="element-type-"]').each(function() {
            var $sel = $(this);
            var curVal = $sel.val();
            $sel.empty();
            Object.keys(labels).forEach(function(t) {
                $sel.append('<option value="' + t + '">' + labels[t] + '</option>');
            });
            if (labels[curVal]) $sel.val(curVal);
        });
    }

    function filterGroupsByCategory() {
        var cat = $('#category').val();
        var $select = $('#groupSelect');
        var currentVal = $select.val();

        $select.find('option').each(function() {
            var $opt = $(this);
            var optCat = $opt.data('category');
            if (!optCat) {
                $opt.show();
                return;
            }
            if (cat === 'case_study' || cat === 'fnh_architecture') {
                $opt.toggle(optCat === cat);
            } else {
                $opt.hide();
            }
        });

        var selectedOption = $select.find('option:selected');
        var selectedCat = selectedOption.data('category');
        if (selectedCat && selectedCat !== cat) {
            $select.val('');
        }
    }

    // --- Helpers -------------------------------------------------------------
    function escapeHtml(v) {
        return String(v || '').replace(/[&<>"']/g, function(c) {
            return ({'&':'&','<':'<','>':'>','"':'"',"'":'&#039;'})[c];
        });
    }

    function itemsToText(items) {
        if (!Array.isArray(items)) return '';
        return items.map(function(i) {
            return typeof i === 'string' ? i : (i.text || '');
        }).join('\n');
    }

    /* --- Table Grid Builder Helpers ----------------------------------------- */
    function cellText(cell) {
        if (cell === null || cell === undefined) return '';
        return typeof cell === 'object' ? (cell.text || '') : String(cell);
    }

    function cellProp(cell, prop, fallback) {
        if (typeof cell === 'object' && cell !== null) return cell[prop] || fallback;
        return fallback;
    }

    function parseTableRows(data) {
        if (!data || typeof data !== 'object') return [['', '', '']];

        var rows = [];

        // -- Format 1: Columns + Object Rows (Key Map format) --
        if (Array.isArray(data.columns) && data.columns.length > 0) {
            var colKeys = data.columns.map(function(c) { return c.key || c.id || c; });
            var colHeaders = data.columns.map(function(c) { return (typeof c === 'object' && c !== null) ? (c.label || c.name || c.key || '') : String(c); });
            rows.push(colHeaders);

            if (Array.isArray(data.rows)) {
                data.rows.forEach(function(r) {
                    if (Array.isArray(r)) {
                        rows.push(r);
                    } else if (typeof r === 'object' && r !== null) {
                        var rowCells = colKeys.map(function(k) {
                            return r[k] !== undefined ? r[k] : '';
                        });
                        rows.push(rowCells);
                    }
                });
            }
            return rows.length > 0 ? rows : [['', '', '']];
        }

        // -- Format 2: Headers + 2D Rows Array --
        if (Array.isArray(data.headers) && data.headers.length > 0) {
            rows.push(data.headers);
        }
        if (Array.isArray(data.rows)) {
            data.rows.forEach(function(r) {
                rows.push(r);
            });
        }

        if (rows.length === 0) rows = [['', '', '']];
        return rows;
    }

    function getMaxCols(rows) {
        return rows.reduce(function(max, r) {
            return Array.isArray(r) ? Math.max(max, r.length) : max;
        }, 1);
    }

    function cardsToText(cards) {
        if (!Array.isArray(cards)) return '';
        return cards.map(function(c) {
            if (typeof c === 'string') return c;
            return [c.title||'', c.body||c.text||''].join(' | ').trim();
        }).join('\n');
    }

    function refreshEmptyState() {
        $('#emptyBuilderState').toggleClass('d-none', $('.page-block').length > 0);
    }

    function tableRowsToText(rows) {
        if (!Array.isArray(rows)) return '';
        return rows.map(function(row) {
            if (Array.isArray(row)) return row.join(' | ');
            if (typeof row === 'string') return row;
            return '';
        }).join('\n');
    }

    // --- Type Badge Colors ----------------------------------------------------
    var typeColors = {
        section_label:'secondary',
        title:'primary', subtitle:'info', text:'dark',
        bullet_list:'success', checklist:'success',
        info_box:'warning', key_insight:'danger',
        table:'info', sentence_visual:'info',
        image:'primary', transparent_image:'primary', audio:'primary',
        what_fnh_reveals:'primary', template_form_table:'info',
        defining_behaviours_card:'success', movement_quality_table:'info',
        centre_character_table:'info', functional_flow:'warning',
        section_heading:'primary',
        exploring_section:'success', centre_identity_card:'danger',
        form_identity_card:'danger',
    };

    function commonFields(pId, eId, data) { return ''; }

    // --- Element-specific fields ----------------------------------------------
    function elementFields(pId, eId, type, data, element) {
        if (typeof data !== 'object' || data === null) data = {};

        if (['title','subtitle','section_label','text'].indexOf(type) !== -1) {
            return '<label class="form-label fw-semibold">Text</label>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][text]" class="form-control mb-2" rows="' +
                (type === 'text' ? 5 : 2) + '">' + escapeHtml(data.text) + '</textarea>' +
                commonFields(pId, eId, data);
        }

        if (['info_box','key_insight'].indexOf(type) !== -1) {
            return '<div class="row">' +
                '<div class="col-md-4 mb-2"><label class="form-label fw-semibold">Box Title</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][title]" class="form-control" value="' + escapeHtml(data.title) + '" placeholder="Remember, Tip…"></div>' +
                '<div class="col-md-8 mb-2"><label class="form-label fw-semibold">Body Text</label>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][text]" class="form-control" rows="3">' + escapeHtml(data.text) + '</textarea></div></div>' +
                commonFields(pId, eId, data);
        }

        if (type === 'bullet_list') {
            return '<label class="form-label fw-semibold">Items <small class="text-muted fw-normal">- one per line</small></label>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][items_text]" class="form-control mb-2" rows="6" placeholder="First item&#10;Second item">' +
                escapeHtml(itemsToText(data.items)) + '</textarea>' + commonFields(pId, eId, data);
        }

        if (type === 'checklist') {
            return '<label class="form-label fw-semibold">Checklist Items <small class="text-muted fw-normal">- one per line</small></label>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][items_text]" class="form-control mb-2" rows="6" placeholder="I understand X&#10;I can do Y">' +
                escapeHtml(itemsToText(data.items)) + '</textarea>' + commonFields(pId, eId, data);
        }

        if (type === 'table') {
            var tableId = 'tbl_' + pId + '_' + eId;
            var rows = parseTableRows(data);
            var numCols = getMaxCols(rows);
            var numRows = rows.length;
            window._pendingTableGrids = window._pendingTableGrids || {};
            window._pendingTableGrids[tableId] = { rows: rows, data: data };
            return '<div class="row mb-3">' +
                '<div class="col-md-5"><label class="form-label fw-semibold">Table Title <small class="text-muted fw-normal">(optional)</small></label>' +
                '<input type="text" class="form-control tbl-title" data-tbl="' + tableId + '" value="' + escapeHtml(data.title || '') + '" placeholder="e.g. The Primary Cycle"></div>' +
                '<div class="col-md-3"><label class="form-label fw-semibold">Rounded Corners</label>' +
                '<div class="form-check form-switch mt-1"><input class="form-check-input tbl-rounded" type="checkbox" id="r_' + tableId + '" data-tbl="' + tableId + '"' +
                (data.rounded ? ' checked' : '') + '><label class="form-check-label" for="r_' + tableId + '">Enable</label></div></div>' +
                '<div class="col-md-4 text-end"><label class="form-label fw-semibold">Grid Size</label>' +
                '<div class="d-flex gap-2 justify-content-end align-items-center"><span class="text-muted" style="font-size:12px">Cols</span>' +
                '<input type="number" min="1" max="20" value="' + numCols + '" class="form-control form-control-sm tbl-cols" data-tbl="' + tableId + '" style="width:60px">' +
                '<span class="text-muted">×</span><span class="text-muted" style="font-size:12px">Rows</span>' +
                '<input type="number" min="1" max="50" value="' + numRows + '" class="form-control form-control-sm tbl-rows" data-tbl="' + tableId + '" style="width:60px"></div></div></div>' +
                '<div id="grid_' + tableId + '" class="table-grid-container mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][table_json]" class="d-none tbl-json" id="json_' + tableId + '"></textarea>' +
                '<div class="d-flex gap-2 flex-wrap"><button type="button" class="btn btn-outline-success btn-sm tbl-add-row" data-tbl="' + tableId + '">＋ Add Row</button>' +
                '<button type="button" class="btn btn-outline-success btn-sm tbl-add-col" data-tbl="' + tableId + '">＋ Add Column</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm tbl-del-row" data-tbl="' + tableId + '">− Remove Row</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm tbl-del-col" data-tbl="' + tableId + '">− Remove Column</button></div>' +
                '<small class="text-muted mt-2 d-block">🎨 <strong>Tip:</strong> Click the color swatch next to <strong>R1, R2…</strong> to color an entire row, or the swatch below <strong>C1, C2…</strong> to color an entire column. Double-click any swatch to clear its color.</small>' +
                commonFields(pId, eId, data);
        }

        if (type === 'sentence_visual') {
            var gridId = 'bg_' + pId + '_' + eId;
            window._pendingBeatGrids = window._pendingBeatGrids || {};
            window._pendingBeatGrids[gridId] = data;
            var numMode = data.beat_numbering_mode || (data.continuous_numbering ? 'continuous' : 'restart_at_8');
            return '<div class="row mb-3">' +
                '<div class="col-md-4"><label class="form-label fw-semibold">Example Title <small class="text-muted fw-normal">(optional)</small></label>' +
                '<input type="text" class="form-control bg-example-title" data-bg="' + gridId + '" value="' + escapeHtml(data.title || data.example_title || '') + '" placeholder="Example 2 -- T# reframed as T"></div>' +
                '<div class="col-md-8"><label class="form-label fw-semibold">Example Description <small class="text-muted fw-normal">(optional)</small></label>' +
                '<textarea class="form-control bg-example-desc" data-bg="' + gridId + '" rows="1" placeholder="The same T# chord is reframed as natural...">' + escapeHtml(data.description || data.example_description || '') + '</textarea></div></div>' +
                '<div class="row mb-3"><div class="col-md-3"><label class="form-label fw-semibold">Visual Title</label>' +
                '<input type="text" class="form-control bg-title" data-bg="' + gridId + '" value="' + escapeHtml(data.label || '') + '" placeholder="EXTENDED CYCLE · 16 BEATS"></div>' +
                '<div class="col-md-2"><label class="form-label fw-semibold">Beat Numbering</label>' +
                '<select class="form-select bg-numbering-mode" data-bg="' + gridId + '"><option value="restart_at_8"' + (numMode === 'restart_at_8' ? ' selected' : '') + '>Restart at 8 (1-8)</option><option value="continuous"' + (numMode === 'continuous' ? ' selected' : '') + '>Continuous (1-16…)</option></select></div>' +
                '<div class="col-md-2"><label class="form-label fw-semibold">Play</label>' +
                '<div class="form-check form-switch mt-1"><input class="form-check-input bg-tap-play" type="checkbox" id="tp_' + gridId + '" data-bg="' + gridId + '"' +
                ((data.tap_to_play || data.playback_label) ? ' checked' : '') + '><label class="form-check-label" for="tp_' + gridId + '">Enable</label></div></div>' +
                '<div class="col-md-1"><label class="form-label fw-semibold">Mode</label>' +
                '<select class="form-select bg-mode" data-bg="' + gridId + '"><option value="cells"' + ((data.mode || (data.rows && data.rows[0] && data.rows[0].mode) || 'cells') === 'cells' ? ' selected' : '') + '>Normal</option><option value="held"' + ((data.mode || (data.rows && data.rows[0] && data.rows[0].mode) || 'cells') === 'held' ? ' selected' : '') + '>Held</option></select></div>' +
                '<div class="col-md-2"><label class="form-label fw-semibold">Show Root</label>' +
                '<div class="form-check form-switch mt-1"><input class="form-check-input bg-show-root" type="checkbox" id="sr_' + gridId + '" data-bg="' + gridId + '"' +
                (data.show_root_toggle ? ' checked' : '') + '><label class="form-check-label" for="sr_' + gridId + '">Enable</label></div></div>' +
                '<div class="col-md-2"><label class="form-label fw-semibold">Reframe Labels</label>' +
                '<div class="form-check form-switch mt-1"><input class="form-check-input bg-reframe-labels" type="checkbox" id="rl_' + gridId + '" data-bg="' + gridId + '"' +
                (data.show_reframe_labels !== false ? ' checked' : '') + '><label class="form-check-label" for="rl_' + gridId + '">Enable</label></div></div>' +
                '<div class="col-md-2 mt-2"><label class="form-label fw-semibold">Playback Label</label>' +
                '<input type="text" class="form-control bg-playback" data-bg="' + gridId + '" value="' + escapeHtml(data.playback_label || '') + '" placeholder="Play"></div></div>' +
                '<label class="form-label fw-semibold mb-1">Beat Grid Rows</label>' +
                '<div id="grid_' + gridId + '"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][sentence_json]" class="d-none" id="json_' + gridId + '"></textarea>' +
                '<div class="d-flex gap-2 flex-wrap mt-1"><button type="button" class="btn btn-outline-success btn-sm bg-add-row" data-bg="' + gridId + '">＋ Add Row</button></div>' +
                '<small class="text-muted mt-2 d-block">🎵 <strong>Tip:</strong> Each beat can be <strong>Chord</strong>, <strong>Empty</strong> (-), or <strong>Pair</strong> (Chord › Chord).</small>' +
                commonFields(pId, eId, data);
        }

        if (['image','transparent_image','audio'].indexOf(type) !== -1) {
            var acceptFile = type === 'audio' ? 'audio/*' : 'image/*';
            var current = element.public_file_url
                ? '<div class="mb-2"><a href="' + element.public_file_url + '" target="_blank">📎 View current file</a></div>' : '';
            return current +
                '<input type="hidden" name="pages[' + pId + '][elements][' + eId + '][existing_file_url]" value="' + escapeHtml(element.file_url) + '">' +
                '<label class="form-label fw-semibold">Upload File</label>' +
                '<input type="file" name="pages[' + pId + '][elements][' + eId + '][file]" class="form-control mb-2" accept="' + acceptFile + '">' +
                '<label class="form-label fw-semibold">Caption / Label</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][caption]" class="form-control mb-2" value="' + escapeHtml(data.caption||data.label) + '" placeholder="Optional caption">' +
                '<label class="form-label fw-semibold">Alt Text</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][alt]" class="form-control mb-2" value="' + escapeHtml(data.alt) + '" placeholder="Accessibility alt text">' +
                commonFields(pId, eId, data);
        }

        // ===================================================================
        // FNH Architecture & Case Study element fields
        // ===================================================================

        if (type === 'what_fnh_reveals') {
            return '<div class="row">' +
                '<div class="col-md-4 mb-2"><label class="form-label fw-semibold">Label Text</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][label]" class="form-control" value="' + escapeHtml(data.label || 'WHAT FNH REVEALS') + '"></div>' +
                '<div class="col-md-2 mb-2"><label class="form-label fw-semibold">Label Color</label>' +
                '<input type="color" name="pages[' + pId + '][elements][' + eId + '][data][label_color]" class="form-control form-control-color" value="' + (data.label_color || '#7C3AED') + '"></div>' +
                '<div class="col-md-6 mb-2"><label class="form-label fw-semibold">Body Text</label>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][text]" class="form-control" rows="4">' + escapeHtml(data.text) + '</textarea></div></div>';
        }

        if (type === 'template_form_table') {
            var gridId = 'tft_' + pId + '_' + eId;
            var existing = data.rows ? JSON.stringify(data) : '';
            window._pendingFnhGrids = window._pendingFnhGrids || {};
            window._pendingFnhGrids[gridId] = { type: 'template_form_table', data: data };
            return '<div class="row mb-3"><div class="col-md-3"><label class="form-label fw-semibold">T Form Color</label>' +
                '<input type="color" class="form-control form-control-color fnh-tft-t-color" data-grid="' + gridId + '" value="' + (data.t_form_color || '#2457E6') + '"></div>' +
                '<div class="col-md-3"><label class="form-label fw-semibold">T# Form Color</label>' +
                '<input type="color" class="form-control form-control-color fnh-tft-ts-color" data-grid="' + gridId + '" value="' + (data.t_sharp_form_color || '#E97B00') + '"></div></div>' +
                '<div id="grid_' + gridId + '" class="fnh-tft-grid mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][template_form_table_json]" class="d-none" id="json_' + gridId + '">' + escapeHtml(existing) + '</textarea>' +
                '<div class="d-flex gap-2"><button type="button" class="btn btn-outline-success btn-sm fnh-tft-add-row" data-grid="' + gridId + '">＋ Add Row</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm fnh-tft-del-row" data-grid="' + gridId + '">− Remove Row</button></div>' +
                '<small class="text-muted mt-1 d-block">Each row: 🎨 Template Color -> Template Name | 🎨 T Form Color -> T Form formula | 🎨 T# Form Color -> T# Form formula.</small>';
        }

        if (type === 'defining_behaviours_card') {
            var bulletsText = Array.isArray(data.bullets) ? data.bullets.join('\n') : '';
            return '<div class="row"><div class="col-md-2 mb-2"><label class="form-label fw-semibold">Number</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][number]" class="form-control" value="' + escapeHtml(data.number) + '"></div>' +
                '<div class="col-md-4 mb-2"><label class="form-label fw-semibold">Title</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][title]" class="form-control" value="' + escapeHtml(data.title) + '"></div>' +
                '<div class="col-md-6 mb-2"><label class="form-label fw-semibold">Section Label</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][section_label]" class="form-control" value="' + escapeHtml(data.section_label || 'DEFINING BEHAVIOURS') + '"></div></div>' +
                '<div class="mb-2"><label class="form-label fw-semibold">Bullet Points <small class="text-muted fw-normal">- one per line</small></label>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][bullets_text]" class="form-control" rows="5">' + escapeHtml(bulletsText) + '</textarea></div>' +
                '<div class="row"><div class="col-md-3 mb-2"><label class="form-label fw-semibold">Best For Label</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][best_for_label]" class="form-control" value="' + escapeHtml((data.best_for||{}).label || 'Best for:') + '"></div>' +
                '<div class="col-md-9 mb-2"><label class="form-label fw-semibold">Best For Text</label>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][best_for_text]" class="form-control" rows="2">' + escapeHtml((data.best_for||{}).text) + '</textarea></div></div>';
        }

        if (type === 'movement_quality_table') {
            var gridId = 'mqt_' + pId + '_' + eId;
            var existing = data.rows ? JSON.stringify(data) : '';
            window._pendingFnhGrids = window._pendingFnhGrids || {};
            window._pendingFnhGrids[gridId] = { type: 'movement_quality_table', data: data };
            return '<div id="grid_' + gridId + '" class="fnh-mqt-grid mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][movement_quality_table_json]" class="d-none" id="json_' + gridId + '">' + escapeHtml(existing) + '</textarea>' +
                '<div class="d-flex gap-2"><button type="button" class="btn btn-outline-success btn-sm fnh-mqt-add-row" data-grid="' + gridId + '">＋ Add Row</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm fnh-mqt-del-row" data-grid="' + gridId + '">− Remove Row</button></div>';
        }

        if (type === 'centre_character_table') {
            var gridId = 'cct_' + pId + '_' + eId;
            var existing = data.rows ? JSON.stringify(data) : '';
            window._pendingFnhGrids = window._pendingFnhGrids || {};
            window._pendingFnhGrids[gridId] = { type: 'centre_character_table', data: data };
            return '<div id="grid_' + gridId + '" class="fnh-cct-grid mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][centre_character_table_json]" class="d-none" id="json_' + gridId + '">' + escapeHtml(existing) + '</textarea>' +
                '<div class="d-flex gap-2"><button type="button" class="btn btn-outline-success btn-sm fnh-cct-add-row" data-grid="' + gridId + '">＋ Add Row</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm fnh-cct-del-row" data-grid="' + gridId + '">− Remove Row</button></div>';
        }

        if (type === 'functional_flow') {
            var gridId = 'ff_' + pId + '_' + eId;
            var existing = data.steps ? JSON.stringify(data) : '';
            window._pendingFnhGrids = window._pendingFnhGrids || {};
            window._pendingFnhGrids[gridId] = { type: 'functional_flow', data: data };
            return '<div class="mb-2"><label class="form-label fw-semibold">Flow Title</label>' +
                '<input type="text" class="form-control fnh-ff-title" data-grid="' + gridId + '" value="' + escapeHtml(data.title) + '" placeholder="FUNCTIONAL FLOW - EXTENDED WITH OPEN ENDING"></div>' +
                '<div id="grid_' + gridId + '" class="fnh-ff-grid d-flex flex-wrap gap-2 align-items-center mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][functional_flow_json]" class="d-none" id="json_' + gridId + '">' + escapeHtml(existing) + '</textarea>' +
                '<div class="d-flex gap-2"><button type="button" class="btn btn-outline-success btn-sm fnh-ff-add-step" data-grid="' + gridId + '">＋ Add Step</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm fnh-ff-del-step" data-grid="' + gridId + '">− Remove Step</button></div>';
        }

        if (type === 'section_heading') {
            return '<label class="form-label fw-semibold">Heading Text</label>' +
                '<input type="text" name="pages[' + pId + '][elements][' + eId + '][data][text]" class="form-control" value="' + escapeHtml(data.text) + '">';
        }

        if (type === 'exploring_section') {
            var gridId = 'es_' + pId + '_' + eId;
            var existing = data.formulas ? JSON.stringify(data) : '';
            window._pendingFnhGrids = window._pendingFnhGrids || {};
            window._pendingFnhGrids[gridId] = { type: 'exploring_section', data: data };
            return '<div class="row mb-2"><div class="col-md-6"><label class="form-label fw-semibold">Title</label>' +
                '<input type="text" class="form-control fnh-es-title" data-grid="' + gridId + '" value="' + escapeHtml(data.title) + '"></div>' +
                '<div class="col-md-6"><label class="form-label fw-semibold">Subtitle</label>' +
                '<input type="text" class="form-control fnh-es-subtitle" data-grid="' + gridId + '" value="' + escapeHtml(data.subtitle) + '"></div></div>' +
                '<label class="form-label fw-semibold">Formula Tags</label><div id="grid_' + gridId + '" class="fnh-es-grid mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][exploring_section_json]" class="d-none" id="json_' + gridId + '">' + escapeHtml(existing) + '</textarea>' +
                '<div class="d-flex gap-2"><button type="button" class="btn btn-outline-success btn-sm fnh-es-add" data-grid="' + gridId + '">＋ Add Formula</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm fnh-es-del" data-grid="' + gridId + '">− Remove Formula</button></div>';
        }

        if (type === 'centre_identity_card') {
            var gridId = 'cic_' + pId + '_' + eId;
            var existing = data.centres ? JSON.stringify(data) : '';
            window._pendingFnhGrids = window._pendingFnhGrids || {};
            window._pendingFnhGrids[gridId] = { type: 'centre_identity_card', data: data };
            return '<label class="form-label fw-semibold">Centre Identity Cards <small class="text-muted fw-normal">- 1 card = single, 2+ = grid</small></label>' +
                '<div id="grid_' + gridId + '" class="fnh-cic-grid mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][centre_identity_card_json]" class="d-none" id="json_' + gridId + '">' + escapeHtml(existing) + '</textarea>' +
                '<div class="d-flex gap-2"><button type="button" class="btn btn-outline-success btn-sm fnh-cic-add" data-grid="' + gridId + '">＋ Add Centre Card</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm fnh-cic-del" data-grid="' + gridId + '">− Remove Card</button></div>';
        }

        if (type === 'form_identity_card') {
            var gridId = 'fic_' + pId + '_' + eId;
            var existing = data.cards ? JSON.stringify(data) : '';
            window._pendingFnhGrids = window._pendingFnhGrids || {};
            window._pendingFnhGrids[gridId] = { type: 'form_identity_card', data: data };
            return '<label class="form-label fw-semibold">Form Identity Cards <small class="text-muted fw-normal">- 1 card = single, 2 = pair</small></label>' +
                '<div id="grid_' + gridId + '" class="fnh-fic-grid mb-2"></div>' +
                '<textarea name="pages[' + pId + '][elements][' + eId + '][data][form_identity_card_json]" class="d-none" id="json_' + gridId + '">' + escapeHtml(existing) + '</textarea>' +
                '<div class="d-flex gap-2"><button type="button" class="btn btn-outline-success btn-sm fnh-fic-add" data-grid="' + gridId + '">＋ Add Form Card</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm fnh-fic-del" data-grid="' + gridId + '">− Remove Card</button></div>';
        }

        return commonFields(pId, eId, data);
    }

    // --- Add Element ----------------------------------------------------------
    function addElement(pId, selectedType, element) {
        if (selectedType === undefined) selectedType = null;
        if (element === undefined) element = {};
        var type = selectedType || $('#element-type-' + pId).val();
        if (!type) { alert('Please select an element type first.'); return; }
        var eId = Date.now() + Math.floor(Math.random() * 1000);
        var data = element.data || {};
        var badgeColor = typeColors[type] || 'secondary';
        var elementPos = element.position || ($('#elements-container-' + pId + ' .element-block').length + 1);

        var elementHtml = '<div class="border p-3 mb-3 bg-white element-block shadow-sm">' +
            '<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">' +
            '<div class="d-flex align-items-center gap-2"><span class="text-muted" style="font-size:16px;cursor:default;">&#9776;</span>' +
            '<span class="badge bg-' + badgeColor + '" style="font-size:10px;letter-spacing:.8px;text-transform:uppercase;">' +
            (elementLabels[type] || type) + '</span></div>' +
            '<div class="d-flex gap-1"><button type="button" class="btn btn-sm btn-outline-secondary move-element-up" title="Move Up">↑</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary move-element-down" title="Move Down">↓</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-element">✕ Remove</button></div></div>' +
            (element.id ? '<input type="hidden" name="pages[' + pId + '][elements][' + eId + '][id]" value="' + escapeHtml(element.id) + '">' : '') +
            '<input type="hidden" class="element-position-input" name="pages[' + pId + '][elements][' + eId + '][position]" value="' + elementPos + '">' +
            '<input type="hidden" name="pages[' + pId + '][elements][' + eId + '][type]" value="' + type + '">' +
            elementFields(pId, eId, type, data, element) + '</div>';

        $('#elements-container-' + pId).append(elementHtml);

        // Initialize pending grids
        setTimeout(function() { initPendingGrids(); }, 10);
    }

    // --- Add Page -------------------------------------------------------------
    function addPage(page) {
        if (page === undefined) page = {};
        var pId = pageIndex++;

        var optionsHtml = '';
        var activeLabels = getActiveLabels();
        Object.keys(activeLabels).forEach(function(t) {
            optionsHtml += '<option value="' + t + '">' + activeLabels[t] + '</option>';
        });

        var pageHtml = '<div class="border p-3 mb-4 bg-light page-block" data-page-index="' + pId + '">' +
            (page.id ? '<input type="hidden" name="pages[' + pId + '][id]" value="' + escapeHtml(page.id) + '">' : '') +
            '<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">' +
            '<h5 class="mb-0">📄 Page <span class="page-number">' + (pId + 1) + '</span></h5>' +
            '<div class="d-flex gap-1">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary move-page-up" title="Move Page Up">↑</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary move-page-down" title="Move Page Down">↓</button>' +
            '<button type="button" class="btn btn-sm btn-danger remove-page">✕ Remove Page</button></div></div>' +
            '<div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label fw-semibold">Page Title <small class="text-muted fw-normal">(optional)</small></label>' +
            '<input type="text" name="pages[' + pId + '][title]" class="form-control page-title-input" value="' + escapeHtml(page.title) + '" placeholder="e.g. The Three Families"></div>' +
            '<div class="col-md-6"><label class="form-label fw-semibold">Section Title <small class="text-muted fw-normal">(optional)</small></label>' +
            '<input type="text" name="pages[' + pId + '][section_title]" class="form-control page-section-title-input" value="' + escapeHtml(page.section_title) + '" placeholder="e.g. Core Pattern"></div></div>' +
            '<div class="elements-container mb-3" id="elements-container-' + pId + '"></div>' +
            '<div class="d-flex flex-wrap gap-2 align-items-center border-top pt-3">' +
            '<select class="form-select w-auto" id="element-type-' + pId + '">' + optionsHtml + '</select>' +
            '<button type="button" class="btn btn-secondary add-element-btn" data-page-id="' + pId + '">+ Add Element</button></div></div>';

        $('#pagesContainer').append(pageHtml);
        if (page.elements && page.elements.length) {
            page.elements.forEach(function(el) { addElement(pId, el.type, el); });
        }
        refreshEmptyState();
    }

    // --- Helpers: Full Dynamic Re-indexing ------------------------------------
    function reindexAll() {
        $('.page-block').each(function(pageIdx, pageEl) {
            var $page = $(pageEl);
            $page.find('.page-number').first().text(pageIdx + 1);
            
            // Update page level inputs
            $page.find('> input[name$="[id]"]').attr('name', 'pages[' + pageIdx + '][id]');
            $page.find('> .row input.page-title-input').attr('name', 'pages[' + pageIdx + '][title]');
            $page.find('> .row input.page-section-title-input').attr('name', 'pages[' + pageIdx + '][section_title]');
            
            // Update each element inside this page block
            $page.find('.elements-container .element-block').each(function(elIdx, el) {
                var $el = $(el);
                $el.find('.element-position-input').val(elIdx + 1);
                
                // Rewrite input names for all fields inside this element to reflect the current page index
                $el.find('input, select, textarea').each(function() {
                    var name = $(this).attr('name');
                    if (name && /^pages\[\d+\]/.test(name)) {
                        $(this).attr('name', name.replace(/^pages\[\d+\]/, 'pages[' + pageIdx + ']'));
                    }
                });
            });
        });
    }

    // --- Pending Grids Initializer ------------------------------------------
    function initPendingGrids() {
        var tblPending = window._pendingTableGrids || {};
        Object.keys(tblPending).forEach(function(tableId) {
            if ($('#grid_' + tableId).length && !tableGrids[tableId]) {
                var info = tblPending[tableId];
                initTableGrid(tableId, info.rows, info.data);
                delete tblPending[tableId];
            }
        });
        var bgPending = window._pendingBeatGrids || {};
        Object.keys(bgPending).forEach(function(gridId) {
            if ($('#grid_' + gridId).length && !beatGrids[gridId]) {
                initBeatGrid(gridId, bgPending[gridId]);
                delete bgPending[gridId];
            }
        });
        var fnhPending = window._pendingFnhGrids || {};
        Object.keys(fnhPending).forEach(function(gridId) {
            if ($('#grid_' + gridId).length && !fnhData[gridId]) {
                initFnhGrid(gridId, fnhPending[gridId]);
                delete fnhPending[gridId];
            }
        });
    }

    // --- Event Handlers -------------------------------------------------------
    $('#learningForm').on('submit', function() { reindexAll(); });
    $('#addPageBtn').on('click', function() { addPage(); reindexAll(); refreshEmptyState(); });

    $(document).on('click', '.move-page-up', function() {
        var $page = $(this).closest('.page-block');
        var $prev = $page.prev('.page-block');
        if ($prev.length) {
            $page.insertBefore($prev);
            reindexAll();
        }
    });

    $(document).on('click', '.move-page-down', function() {
        var $page = $(this).closest('.page-block');
        var $next = $page.next('.page-block');
        if ($next.length) {
            $page.insertAfter($next);
            reindexAll();
        }
    });

    $(document).on('click', '.remove-page', function() {
        $(this).closest('.page-block').remove();
        reindexAll();
        refreshEmptyState();
    });

    $(document).on('click', '.add-element-btn', function() {
        var pId = $(this).data('page-id');
        addElement(pId);
        reindexAll();
    });

    $(document).on('click', '.remove-element', function() {
        $(this).closest('.element-block').remove();
        reindexAll();
    });

    $(document).on('click', '.move-element-up', function() {
        var $el = $(this).closest('.element-block');
        var $prev = $el.prev('.element-block');
        if ($prev.length) {
            $el.insertBefore($prev);
            reindexAll();
        } else {
            var $currentPageBlock = $el.closest('.page-block');
            var $prevPageBlock = $currentPageBlock.prev('.page-block');
            if ($prevPageBlock.length) {
                var $targetContainer = $prevPageBlock.find('.elements-container').first();
                $el.appendTo($targetContainer);
                reindexAll();
            }
        }
    });

    $(document).on('click', '.move-element-down', function() {
        var $el = $(this).closest('.element-block');
        var $next = $el.next('.element-block');
        if ($next.length) {
            $el.insertAfter($next);
            reindexAll();
        } else {
            var $currentPageBlock = $el.closest('.page-block');
            var $nextPageBlock = $currentPageBlock.next('.page-block');
            if ($nextPageBlock.length) {
                var $targetContainer = $nextPageBlock.find('.elements-container').first();
                $el.prependTo($targetContainer);
                reindexAll();
            }
        }
    });

    // --- Category change events ----------------------------------------------
    $(document).on('change', '#category', function() {
        refreshElementTypeDropdowns();
        filterGroupsByCategory();
        var val = $('#category').val();
        if (val === 'case_study' || val === 'fnh_architecture') {
            $('#groupField').show();
        } else {
            $('#groupField').hide();
        }
    });

    // --- Boot: Load existing pages or start with one empty -------------------
    if (initialPages.length) {
        console.log('📄 Loading ' + initialPages.length + ' page(s) from database');
        initialPages.forEach(function(page) { addPage(page); });
    } else {
        addPage();
    }
    refreshEmptyState();

    // Initialize any pending grids from boot
    setTimeout(function() { initPendingGrids(); }, 50);
    setTimeout(function() { refreshElementTypeDropdowns(); }, 100);

    // --- Key Takeaways List Builder ------------------------------------------
    (function() {
        var SEPARATOR = '|';
        var $list = $('#takeawaysList');
        var $empty = $('#takeawaysEmpty');
        var $input = $('#takeawayInput');
        var $hidden = $('#summaryHidden');
        var $addBtn = $('#addTakeawayBtn');

        function refreshEmpty() {
            var hasItems = $list.children('.takeaway-item').length > 0;
            $empty.toggle(!hasItems);
            $list.toggle(hasItems);
        }

        function renumber() {
            $list.find('.takeaway-num').each(function(i) {
                $(this).text(i + 1);
            });
        }

        function syncHidden() {
            var items = [];
            $list.find('.takeaway-text').each(function() {
                items.push($(this).text().trim());
            });
            $hidden.val(items.join(SEPARATOR));
            refreshEmpty();
        }

        function addItem(text) {
            text = text.trim();
            if (!text) return;
            var num = $list.children('.takeaway-item').length + 1;
            var html = '<div class="takeaway-item bg-white border p-2 mb-2" style="display:flex;align-items:flex-start;gap:8px;overflow:hidden;">' +
                '<span class="takeaway-num flex-shrink-0 text-white bg-primary text-center" style="width:24px;height:24px;line-height:24px;font-size:11px;font-weight:600;margin-top:2px;">' + num + '</span>' +
                '<span class="takeaway-text" style="flex:1;min-width:0;word-wrap:break-word;overflow-wrap:anywhere;word-break:break-word;line-height:1.5;font-size:14px;">' + escapeHtml(text) + '</span>' +
                '<button type="button" class="remove-takeaway flex-shrink-0" title="Remove" style="background:none;border:1px solid #dc3545;color:#dc3545;border-radius:4px;width:24px;height:24px;line-height:22px;font-size:12px;cursor:pointer;padding:0;margin-top:2px;text-align:center;">&times;</button></div>';
            $list.append(html);
            syncHidden();
        }

        var existing = ($hidden.val() || '').trim();
        if (existing) {
            var items = existing.indexOf(SEPARATOR) !== -1
                ? existing.split(SEPARATOR)
                : existing.split(/\r?\n/);
            items.forEach(function(item) {
                if (item.trim()) addItem(item.trim());
            });
        }
        refreshEmpty();

        $addBtn.on('click', function() {
            addItem($input.val());
            $input.val('').focus();
        });

        $input.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addItem($input.val());
                $input.val('').focus();
            }
        });

        $(document).on('click', '.remove-takeaway', function() {
            $(this).closest('.takeaway-item').remove();
            renumber();
            syncHidden();
        });
    })();

    /* =======================================================================
     * TABLE GRID BUILDER
     * ======================================================================= */

    // --- CSS injection ------------------------------------------------------
    var gridCss = '' +
        '#learningForm, #learningForm .card, #learningForm .card-body, #learningForm .row, #learningForm [class*="col-"] { min-width:0; }' +
        '#learningForm .card-header { gap: .75rem; flex-wrap:wrap; }' +
        '#learningForm .card-header .card-title { min-width:0; overflow-wrap:anywhere; }' +
        '.page-block { overflow:visible; }' +
        '.element-block { min-width:0; overflow:visible; }' +
        '.table-grid-container { overflow-x:auto; max-width:100%; padding:4px 0; -webkit-overflow-scrolling:touch; scrollbar-width:thin; scrollbar-color:#adb5bd transparent; }' +
        '.table-grid-container::-webkit-scrollbar { height:6px; }' +
        '.table-grid-container::-webkit-scrollbar-thumb { background:#adb5bd; border-radius:3px; }' +
        '.table-grid-container::-webkit-scrollbar-track { background:transparent; }' +
        '.tbl-grid { border-collapse:collapse; width:max-content; }' +
        '.tbl-grid th, .tbl-grid td { padding:2px; vertical-align:middle; white-space:nowrap; }' +
        '.tbl-grid .cell-wrap { border:1px solid #dee2e6; border-radius:4px; padding:3px 5px; display:flex; align-items:center; gap:3px; background:#fff; min-width:90px; transition: box-shadow .15s; }' +
        '.tbl-grid .cell-wrap:focus-within { box-shadow:0 0 0 2px rgba(36,87,230,.2); border-color:#2457E6; }' +
        '.tbl-grid .cell-text { border:none; outline:none; flex:1; min-width:40px; width:70px; font-size:12px; background:transparent; padding:2px 0; }' +
        '.tbl-grid .cell-bold-btn { width:18px; height:18px; border:1px solid #ccc; border-radius:3px; font-weight:900; font-size:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:#fff; color:#999; line-height:1; }' +
        '.tbl-grid .cell-bold-btn.active { background:#2457E6; color:#fff; border-color:#2457E6; }' +
        '.tbl-grid .col-head { text-align:center; font-size:9px; color:#aaa; padding:1px 3px; font-weight:600; white-space:nowrap; }' +
        '.tbl-grid .row-head { text-align:center; font-size:9px; color:#aaa; padding:1px 4px; font-weight:600; vertical-align:middle; width:24px; }' +
        '.tbl-grid .col-color-wrap { text-align:center; padding:2px 4px; }' +
        '.tbl-grid .row-color-wrap { vertical-align:middle; padding:2px 4px; }' +
        '.tbl-grid .rc-color-btn { width:18px; height:18px; border-radius:3px; border:2px solid #ccc; cursor:pointer; padding:0; position:relative; background:transparent; display:inline-block; transition: border-color .15s, box-shadow .15s; }' +
        '.tbl-grid .rc-color-btn:hover { border-color:#666; box-shadow:0 0 0 2px rgba(36,87,230,.15); }' +
        '.tbl-grid .rc-color-input { position:absolute; opacity:0; width:18px; height:18px; cursor:pointer; top:0; left:0; }' +
        '.tbl-grid .rc-label { font-size:9px; color:#aaa; font-weight:600; display:block; line-height:1; margin-bottom:2px; }' +
        '.beat-grid-scroll { display:block; overflow-x:auto; overflow-y:visible; width:100%; max-width:100%; box-sizing:border-box; padding:4px 0 10px; -webkit-overflow-scrolling:touch; scrollbar-width:thin; scrollbar-color:#adb5bd #e9ecef; }' +
        '.beat-grid-scroll::-webkit-scrollbar { height:6px; }' +
        '.beat-grid-scroll::-webkit-scrollbar-track { background:#e9ecef; border-radius:4px; }' +
        '.beat-grid-scroll::-webkit-scrollbar-thumb { background:#adb5bd; border-radius:4px; }' +
        '.beat-grid-scroll::-webkit-scrollbar-thumb:hover { background:#8a94a1; }' +
        '.beat-grid-row-container { width:100%; min-width:0; position:relative; overflow:visible; }' +
        '.beat-grid-row { display:flex !important; flex-direction:row !important; flex-wrap:nowrap !important; align-items:flex-start !important; gap:12px; width:max-content; min-width:100%; padding:4px 8px; box-sizing:border-box; }' +
        '.beat-card { border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px; background: #fff; width:170px; min-width:170px; max-width:170px; min-height:240px; height:auto; flex: 0 0 170px; flex-shrink:0; display: flex; flex-direction: column; gap: 5px; box-shadow: 0 2px 8px rgba(0,0,0,.07); transition: box-shadow .2s, border-color .2s; position: relative; overflow:visible; box-sizing:border-box; }' +
        '.beat-card.beat-pair { border-color: #c7d2fe; background: #f8f8ff; width:340px; min-width:340px; max-width:340px; flex: 0 0 340px; }' +
        '.beat-card.held-segment-card { width:170px; min-width:170px; max-width:170px; flex: 0 0 170px; flex-shrink:0; }' +
        '.beat-card:hover { box-shadow: 0 4px 16px rgba(99,102,241,.13); border-color: #a5b4fc; }' +
        '.pair-side { display: flex; flex-direction: column; gap: 4px; width:100%; padding: 0; }' +
        '.pair-arrow { display: flex; align-items: center; padding: 0 4px; font-weight: bold; font-size: 18px; color: #94a3b8; flex-shrink:0; }' +
        '.beat-card select, .beat-card input:not([type="checkbox"]), .beat-card .form-control, .beat-card .form-select { width:100% !important; box-sizing:border-box !important; min-width:0 !important; max-width:100% !important; }' +
        '.beat-card .bg-editable-toggle { position:static !important; width:1rem !important; min-width:1rem !important; max-width:1rem !important; height:1rem !important; margin:0 !important; flex:0 0 auto !important; }' +
        '.beat-card .beat-checkbox-control { display:flex; align-items:center; gap:4px; min-height:26.5px; margin:0; color:#475569; font-size:11px; line-height:1.2; white-space:nowrap; cursor:pointer; }' +
        '.beat-card .beat-checkbox-control span { overflow:hidden; text-overflow:ellipsis; }' +
        '.beat-card.beat-pair > .d-flex { flex:0 0 auto !important; min-height:auto !important; }' +
        '.beat-card.beat-pair > .beat-fields:last-child { flex:0 0 auto; }' +
        '.beat-card .beat-fields .d-flex.gap-1 { flex-wrap:wrap; }' +
        '.beat-card .beat-fields .d-flex.gap-1 > div { flex:1; min-width:0; }' +
        '.beat-num { text-align:center; font-size:10px; color:#94a3b8; font-weight:700; letter-spacing:.5px; }' +
        '.beat-num.beat-num-cycle-start { color:#ef4444; font-size:11px; }' +
        '.beat-preview { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 6px 7px; min-height: 70px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); position: relative; }' +
        '.beat-preview.is-empty { border-style:dashed; border-color:#cbd5e1; }' +
        '.beat-func { font-weight:800; font-size:15px; line-height:1.2; }' +
        '.beat-func sup { font-size:10px; vertical-align:super; font-weight:700; }' +
        '.beat-notes-row { font-size:12px; color:#334155; letter-spacing:.5px; }' +
        '.beat-note { font-weight:500; }' +
        '.beat-bar { height:5px; width:88%; border-radius:3px; margin-top:3px; transition: background-color .15s; }' +
        '.beat-dash { font-size:20px; color:#cbd5e1; font-weight:300; }' +
        '.beat-ann { font-size:8px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; font-weight:700; position:absolute; top:3px; left:50%; transform:translateX(-50%); white-space:nowrap; }' +
        '.beat-fields { display:flex; flex-direction:column; gap:4px; }' +
        '.beat-fields select, .beat-fields input[type=text], .beat-fields input[type=number] { font-size: 11px !important; padding: 3px 5px !important; border-radius: 5px !important; }' +
        '@media (max-width: 767.98px) {' +
            '#learningForm .card-body, #learningForm .card-header, #learningForm .page-block, #learningForm .element-block { padding-left: .75rem !important; padding-right: .75rem !important; }' +
            '#learningForm .form-label { line-height:1.35; }' +
            '.beat-grid-row { gap:8px; padding-left:4px; padding-right:4px; }' +
            '.beat-card { width:160px; min-width:160px; max-width:160px; flex-basis:160px; }' +
            '.beat-card.beat-pair { width:320px; min-width:320px; max-width:320px; flex-basis:320px; }' +
            '.beat-card.beat-pair .pair-side { width:calc(50% - 9px); }' +
            '.beat-card.beat-pair > .beat-fields:last-child > .d-flex { gap:4px !important; }' +
        '}' +
        '@media (min-width: 768px) {' +
            '.beat-card.beat-pair > .beat-fields:last-child > .d-flex { gap:6px !important; }' +
        '}';
    $('<style>').html(gridCss).appendTo('head');

    // --- Table Grid Functions -----------------------------------------------
    function initTableGrid(tableId, rows, srcData) {
        var numCols = getMaxCols(rows);
        var numRows = rows.length;
        var grid = rows.map(function(row) {
            var r = Array.isArray(row) ? row : [row];
            var out = [];
            for (var c = 0; c < numCols; c++) {
                var cell = r[c];
                if (typeof cell === 'object' && cell !== null) {
                    out.push({ text: cell.text || '', bg_color: cell.bg_color || '', bold: !!cell.bold });
                } else {
                    out.push({ text: cell != null ? String(cell) : '', bg_color: '', bold: false });
                }
            }
            return out;
        });
        tableGrids[tableId] = grid;

        tableRowColors[tableId] = (srcData && Array.isArray(srcData.row_colors))
            ? srcData.row_colors.slice(0, numRows)
            : new Array(numRows).fill('');
        tableColColors[tableId] = (srcData && Array.isArray(srcData.col_colors))
            ? srcData.col_colors.slice(0, numCols)
            : new Array(numCols).fill('');

        renderTableGrid(tableId);
    }

    function renderTableGrid(tableId) {
        var grid = tableGrids[tableId];
        if (!grid || !grid.length) return;
        var numRows = grid.length;
        var numCols = grid[0].length;
        var rColors = tableRowColors[tableId] || [];
        var cColors = tableColColors[tableId] || [];

        var html = '<table class="tbl-grid"><thead>';
        html += '<tr><th></th>';
        for (var c = 0; c < numCols; c++) {
            html += '<th class="col-head">C' + (c + 1) + '</th>';
        }
        html += '</tr>';
        html += '<tr><th></th>';
        for (var c = 0; c < numCols; c++) {
            var cc = cColors[c] || '';
            var ccStyle = cc ? 'background:' + cc + '; border-color:' + cc : '';
            html += '<th class="col-color-wrap"><span class="rc-color-btn" title="Color entire column C' + (c + 1) + ' (double-click to clear)" style="' + ccStyle + '">' +
                '<input type="color" class="rc-color-input col-color-picker" data-tbl="' + tableId + '" data-c="' + c + '" value="' + (cc || '#ffffff') + '"></span></th>';
        }
        html += '</tr></thead><tbody>';

        for (var r = 0; r < numRows; r++) {
            html += '<tr>';
            var rc = rColors[r] || '';
            var rcStyle = rc ? 'background:' + rc + '; border-color:' + rc : '';
            html += '<td class="row-head"><span class="rc-label">R' + (r + 1) + '</span>' +
                '<span class="rc-color-btn" title="Color entire row R' + (r + 1) + ' (double-click to clear)" style="' + rcStyle + '">' +
                '<input type="color" class="rc-color-input row-color-picker" data-tbl="' + tableId + '" data-r="' + r + '" value="' + (rc || '#ffffff') + '"></span></td>';
            for (var c = 0; c < numCols; c++) {
                var cell = grid[r][c];
                var effectiveBg = cell.bg_color || (rc ? rc : (cColors[c] || ''));
                var wrapBg = effectiveBg ? 'background:' + effectiveBg + '20' : '';
                var boldClass = cell.bold ? ' active' : '';
                html += '<td><div class="cell-wrap" style="' + wrapBg + '">' +
                    '<input type="text" class="cell-text" data-tbl="' + tableId + '" data-r="' + r + '" data-c="' + c + '" value="' + escapeHtml(cell.text) + '" placeholder="..." style="' + (cell.bold ? 'font-weight:700' : '') + '">' +
                    '<span class="cell-bold-btn' + boldClass + '" title="Bold" data-tbl="' + tableId + '" data-r="' + r + '" data-c="' + c + '">B</span></div></td>';
            }
            html += '</tr>';
        }
        html += '</tbody></table>';
        $('#grid_' + tableId).html(html);
        serializeTableGrid(tableId);
    }

    function serializeTableGrid(tableId) {
        var grid = tableGrids[tableId];
        if (!grid) return;
        var title = $('.tbl-title[data-tbl="' + tableId + '"]').val() || '';
        var rounded = $('.tbl-rounded[data-tbl="' + tableId + '"]').is(':checked');
        var rows = grid.map(function(row) {
            return row.map(function(cell) {
                if (!cell.bg_color && !cell.bold) return cell.text;
                var obj = { text: cell.text };
                if (cell.bg_color) obj.bg_color = cell.bg_color;
                if (cell.bold) obj.bold = true;
                return obj;
            });
        });
        var payload = { rows: rows };
        if (title) payload.title = title;
        if (rounded) payload.rounded = true;

        var rColors = tableRowColors[tableId] || [];
        var cColors = tableColColors[tableId] || [];
        if (rColors.some(function(c) { return c; })) payload.row_colors = rColors;
        if (cColors.some(function(c) { return c; })) payload.col_colors = cColors;

        $('#json_' + tableId).val(JSON.stringify(payload));
    }

    function resizeGrid(tableId, newCols, newRows) {
        var grid = tableGrids[tableId] || [[]];
        var result = [];
        for (var r = 0; r < newRows; r++) {
            var row = [];
            for (var c = 0; c < newCols; c++) {
                if (grid[r] && grid[r][c]) {
                    row.push(Object.assign({}, grid[r][c]));
                } else {
                    row.push({ text: '', bg_color: '', bold: false });
                }
            }
            result.push(row);
        }
        tableGrids[tableId] = result;

        var oldRC = tableRowColors[tableId] || [];
        var oldCC = tableColColors[tableId] || [];
        tableRowColors[tableId] = Array.from({ length: newRows }, function(_, i) { return oldRC[i] || ''; });
        tableColColors[tableId] = Array.from({ length: newCols }, function(_, i) { return oldCC[i] || ''; });

        renderTableGrid(tableId);
    }

    // --- Table Grid Event Delegation ---------------------------------------
    $(document).on('input', '.cell-text', function() {
        var t = $(this).data('tbl'), r = $(this).data('r'), c = $(this).data('c');
        if (tableGrids[t]) { tableGrids[t][r][c].text = $(this).val(); serializeTableGrid(t); }
    });

    $(document).on('click', '.cell-bold-btn', function() {
        var t = $(this).data('tbl'), r = $(this).data('r'), c = $(this).data('c');
        if (tableGrids[t]) {
            tableGrids[t][r][c].bold = !tableGrids[t][r][c].bold;
            $(this).toggleClass('active');
            $(this).closest('.cell-wrap').find('.cell-text').css('font-weight', tableGrids[t][r][c].bold ? 700 : '');
            serializeTableGrid(t);
        }
    });

    $(document).on('input change', '.tbl-title, .tbl-rounded', function() {
        serializeTableGrid($(this).data('tbl'));
    });

    $(document).on('change', '.tbl-cols, .tbl-rows', function() {
        var t = $(this).data('tbl');
        var cols = parseInt($('.tbl-cols[data-tbl="' + t + '"]').val()) || 1;
        var rows = parseInt($('.tbl-rows[data-tbl="' + t + '"]').val()) || 1;
        resizeGrid(t, cols, rows);
    });

    $(document).on('click', '.tbl-add-row', function() {
        var t = $(this).data('tbl'), g = tableGrids[t]; if (!g) return;
        var cols = g[0] ? g[0].length : 3;
        g.push(Array.from({ length: cols }, function() { return { text: '', bg_color: '', bold: false }; }));
        (tableRowColors[t] = tableRowColors[t] || []).push('');
        $('.tbl-rows[data-tbl="' + t + '"]').val(g.length);
        renderTableGrid(t);
    });

    $(document).on('click', '.tbl-add-col', function() {
        var t = $(this).data('tbl'), g = tableGrids[t]; if (!g) return;
        g.forEach(function(row) { row.push({ text: '', bg_color: '', bold: false }); });
        (tableColColors[t] = tableColColors[t] || []).push('');
        $('.tbl-cols[data-tbl="' + t + '"]').val(g[0].length);
        renderTableGrid(t);
    });

    $(document).on('click', '.tbl-del-row', function() {
        var t = $(this).data('tbl'), g = tableGrids[t]; if (!g || g.length <= 1) return;
        g.pop();
        (tableRowColors[t] || []).pop();
        $('.tbl-rows[data-tbl="' + t + '"]').val(g.length);
        renderTableGrid(t);
    });

    $(document).on('click', '.tbl-del-col', function() {
        var t = $(this).data('tbl'), g = tableGrids[t]; if (!g || g[0].length <= 1) return;
        g.forEach(function(row) { row.pop(); });
        (tableColColors[t] || []).pop();
        $('.tbl-cols[data-tbl="' + t + '"]').val(g[0].length);
        renderTableGrid(t);
    });

    // --- Row Color Picker --------------------------------------------------
    $(document).on('input', '.row-color-picker', function() {
        var t = $(this).data('tbl'), r = $(this).data('r');
        if (!tableGrids[t]) return;
        tableRowColors[t][r] = $(this).val();
        serializeTableGrid(t);
        renderTableGrid(t);
    });

    $(document).on('dblclick', '.row-color-picker', function(e) {
        e.preventDefault(); e.stopPropagation();
        var t = $(this).data('tbl'), r = $(this).data('r');
        if (!tableGrids[t]) return;
        tableRowColors[t][r] = '';
        serializeTableGrid(t);
        renderTableGrid(t);
    });

    // --- Column Color Picker -----------------------------------------------
    $(document).on('input', '.col-color-picker', function() {
        var t = $(this).data('tbl'), c = $(this).data('c');
        if (!tableGrids[t]) return;
        tableColColors[t][c] = $(this).val();
        serializeTableGrid(t);
        renderTableGrid(t);
    });

    $(document).on('dblclick', '.col-color-picker', function(e) {
        e.preventDefault(); e.stopPropagation();
        var t = $(this).data('tbl'), c = $(this).data('c');
        if (!tableGrids[t]) return;
        tableColColors[t][c] = '';
        serializeTableGrid(t);
        renderTableGrid(t);
    });

    /* =======================================================================
     * BEAT GRID (SENTENCE VISUAL) BUILDER
     * ======================================================================= */

    function initBeatGrid(gridId, data) {
        // Read mode from data root (new format), or from first row (legacy), default 'cells'
        var mode = data.mode || (data.rows && data.rows[0] && data.rows[0].mode) || 'cells';
        var rows = [];

        // Detect canonical format by STRUCTURE, not by `type` field.
        // The backend mapper (SentenceVisualMapper) strips the `type` key from
        // its output, so we cannot rely on data.type === 'sentence_visual'.
        // Canonical rows always have a `cells` array (cells-mode) or a `segments`
        // array (held-mode). Legacy rows have a `beats` array instead.
        var firstRow = data.rows && data.rows[0];
        var isCanonical = data.rows && Array.isArray(data.rows) && (
            (data.type === 'sentence_visual' || data.type === 'sentence_block') ||
            (firstRow && (Array.isArray(firstRow.cells) || Array.isArray(firstRow.segments)))
        );

        if (isCanonical) {
            rows = canonicalRowsToEditorRows(data.rows, data.beats || [], mode);
            if ($('.bg-title[data-bg="' + gridId + '"]').length && data.label) {
                $('.bg-title[data-bg="' + gridId + '"]').val(data.label);
            }
        } else if (data.rows && Array.isArray(data.rows)) {
            // Legacy format: rows have `beats` array of raw beat objects
            rows = data.rows.map(function(r) {
                var row = { title: r.title || r.label || '' };
                if (mode === 'held') {
                    row.segments = (r.segments || []).map(function(s) {
                        return { world: s.world || 'T', beat_from: parseInt(s.beat_from) || 0, beat_to: parseInt(s.beat_to) || 0, weight: parseFloat(s.weight) || 1, notes: s.notes || '', root: s.root || '' };
                    });
                } else {
                    row.beats = (r.beats || []).map(function(b) { return normBeat(b); });
                }
                return row;
            });
        } else if (data.beats && Array.isArray(data.beats)) {
            rows = [{ title: '', beats: data.beats.map(function(b) { return normBeat(b); }) }];
        } else {
            var defaultBeats = [newEmptyBeat(), newEmptyBeat(), newEmptyBeat(), newEmptyBeat()];
            if (mode === 'held') {
                rows = [{ title: '', segments: [] }];
            } else {
                rows = [{ title: '', beats: defaultBeats }];
            }
        }
        beatGrids[gridId] = { rows: rows };
        // Set the global mode selector
        $('.bg-mode[data-bg="' + gridId + '"]').val(mode);
        renderBeatGrid(gridId);
    }

    function buildChordSubHtml(gridId, rowIdx, beatIdx, side, chord, color) {
        if (color === undefined) color = getFunctionColor(chord && chord.function);
        var sup = chord.superscript ? '<sup>' + escapeHtml(chord.superscript) + '</sup>' : '';
        var notes = chord.notes ? chord.notes.split(/\s+/).filter(function(n) { return n; }) : [];
        var nHtml = notes.map(function(n) { return '<span class="beat-note">' + escapeHtml(n) + '</span>'; }).join(' ');

        var h = '<div class="pair-side">';
        h += '<div class="beat-preview">';
        if (chord.annotation) h += '<div class="beat-ann">' + escapeHtml(chord.annotation) + '</div>';
        if (chord.root_annotation) h += '<div class="beat-ann" style="color:#64748b">' + escapeHtml(chord.root_annotation) + '</div>';
        h += '<div class="beat-func" style="color:' + color + '">' + escapeHtml(chord.function) + sup + '</div>';
        h += '<div class="beat-notes-row">' + nHtml + '</div>';
        h += '<div class="beat-bar" style="background:' + color + '"></div></div>';
        h += '<div class="beat-fields mt-1">';
        h += '<div class="d-flex gap-1 align-items-end">';
        h += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Func</div>' +
            '<select class="form-select form-select-sm bg-pair-func" data-bg="' + gridId + '" data-idx="' + beatIdx + '" data-row="' + rowIdx + '" data-side="' + side + '" style="width:55px">' +
            '<option value="T"' + (chord.function === 'T' ? ' selected' : '') + '>T</option>' +
            '<option value="S"' + (chord.function === 'S' ? ' selected' : '') + '>S</option>' +
            '<option value="D"' + (chord.function === 'D' ? ' selected' : '') + '>D</option>' +
            '<option value="_custom"' + (['T','S','D',''].indexOf(chord.function) === -1 ? ' selected' : '') + '>Other</option></select></div>';
        h += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">#</div>' +
            '<input type="text" class="form-control form-control-sm bg-pair-super" data-bg="' + gridId + '" data-idx="' + beatIdx + '" data-row="' + rowIdx + '" data-side="' + side + '" value="' + escapeHtml(chord.superscript) + '" placeholder="#" style="width:38px"></div></div>';
        if (['T','S','D',''].indexOf(chord.function) === -1) {
            h += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Custom Label</div>' +
                '<input type="text" class="form-control form-control-sm bg-pair-func-custom-in" data-bg="' + gridId + '" data-idx="' + beatIdx + '" data-row="' + rowIdx + '" data-side="' + side + '" value="' + escapeHtml(chord.function) + '" placeholder="Label"></div>';
        }
        h += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Notes</div>' +
            '<input type="text" class="form-control form-control-sm bg-pair-notes" data-bg="' + gridId + '" data-idx="' + beatIdx + '" data-row="' + rowIdx + '" data-side="' + side + '" value="' + escapeHtml(chord.notes) + '" placeholder="1 3 5"></div>';
        h += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Top Annotation</div>' +
            '<input type="text" class="form-control form-control-sm bg-pair-ann" data-bg="' + gridId + '" data-idx="' + beatIdx + '" data-row="' + rowIdx + '" data-side="' + side + '" value="' + escapeHtml(chord.annotation) + '" placeholder="e.g. OLD/NEW"></div>';
        h += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Root Label</div>' +
            '<input type="text" class="form-control form-control-sm bg-pair-root-ann-in" data-bg="' + gridId + '" data-idx="' + beatIdx + '" data-row="' + rowIdx + '" data-side="' + side + '" value="' + escapeHtml(chord.root_annotation || '') + '" placeholder="e.g. 1 = A♯"></div>';
        h += '</div></div>';
        return h;
    }

    function getFunctionColor(func) {
        var fn = (func || 'T').toUpperCase();
        if (fn === 'T') return '#3b82f6';
        if (fn === 'S') return '#f59e0b';
        if (fn === 'D') return '#ef4444';
        if (fn === 'T#') return '#8b5cf6';
        if (fn === 'S#') return '#14b8a6';
        if (fn === 'D#') return '#ec4899';
        return '#94a3b8';
    }

    /**
     * Build HTML for a single beat card (normal, empty, or pair).
     * This is extracted from renderBeatGrid so individual cards can be
     * appended/replaced without full re-render.
     */
    function buildSingleBeatCardHtml(gridId, rowIdx, beat, i) {
        // Beat numbers cycle 1-8 (restart_at_8) or increment continuously
        var numMode = ($('.bg-numbering-mode[data-bg="' + gridId + '"]').val() || 'restart_at_8');
        var beatNum = numMode === 'continuous' ? (i + 1) : ((i % 8) + 1);
        var isOctaveStart = numMode !== 'continuous' && i > 0 && i % 8 === 0;
        var html = '';

        if (beat.type === 'pair' || beat.type === 'pair_v_a' || beat.type === 'pair_v_b') {
            html += '<div class="beat-card beat-pair" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '">';
            html += '<div class="beat-num' + (isOctaveStart ? ' beat-num-cycle-start' : '') + '">' + beatNum + '</div>';
            var leftColor = getFunctionColor(beat.left && beat.left.function);
            var rightColor = getFunctionColor(beat.right && beat.right.function);
            html += '<div class="d-flex align-items-stretch gap-0" style="flex:1;min-height:0;">';
            html += buildChordSubHtml(gridId, rowIdx, i, 'left', beat.left, leftColor);
            html += '<div class="pair-arrow">&#x276F;</div>';
            html += buildChordSubHtml(gridId, rowIdx, i, 'right', beat.right, rightColor);
            html += '</div>';
            html += '<div class="beat-fields mt-1"><div class="d-flex gap-1 align-items-end flex-wrap">';
            html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Type</div>' +
                '<select class="form-select form-select-sm bg-type-sel" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" style="width:auto">' +
                '<option value="normal">Normal</option><option value="empty">Empty</option>' +
                '<option value="pair_v_a"' + ((beat.variant || 'B') === 'A' ? ' selected' : '') + '>Pair V-A</option>' +
                '<option value="pair_v_b"' + ((beat.variant || 'B') === 'B' ? ' selected' : '') + '>Pair V-B</option></select></div>' +
                '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Editable</div>' +
                '<label class="beat-checkbox-control" title="Allow the learner to edit this beat"><input type="checkbox" class="form-check-input bg-editable-toggle" aria-label="Editable" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '"' + (beat.editable ? ' checked' : '') + '><span>Allow</span></label></div>' +
                '<div style="min-width:80px"><div style="font-size:9px;color:#aaa;margin-bottom:1px">Allow Notes</div>' +
                '<input type="text" class="form-control form-control-sm bg-allowable-notes-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml((beat.allowable_notes || []).join(' ')) + '" placeholder="1 3 5"></div>' +
                '<div style="min-width:70px"><div style="font-size:9px;color:#aaa;margin-bottom:1px">Centre Voicing</div>' +
                '<input type="text" class="form-control form-control-sm bg-centre-voicing-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(beat.centre_voicing || '') + '" placeholder="C4"></div>' +
                '<div style="min-width:50px"><div style="font-size:9px;color:#aaa;margin-bottom:1px">Oct Range</div>' +
                '<input type="number" class="form-control form-control-sm bg-octave-range-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + (beat.octave_range !== undefined ? beat.octave_range : '') + '" placeholder="±2" style="width:55px"></div>' +
                '</div></div>';
            html += '</div>'; // close .beat-card
            return html;
        }

        // Treat any type that is not explicitly 'empty' as a chord/normal beat.
        // This prevents Normal-type cards from collapsing when type is missing/unrecognised.
        var isEmpty = beat.type === 'empty';
        var isChord = !isEmpty; // everything that is not empty is a chord (normal)
        var effectiveFunc = (beat.function && beat.function.trim()) ? beat.function : 'T';
        var barColor = isChord ? getFunctionColor(effectiveFunc) : '#cbd5e1';
        html += '<div class="beat-card" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '">';
        html += '<div class="beat-num' + (isOctaveStart ? ' beat-num-cycle-start' : '') + '">' + beatNum + '</div>';
        html += '<div class="beat-preview' + (isEmpty ? ' is-empty' : '') + '">';
        if (isChord) {
            if (beat.annotation) html += '<div class="beat-ann">' + escapeHtml(beat.annotation) + '</div>';
            var sup = beat.superscript ? '<sup>' + escapeHtml(beat.superscript) + '</sup>' : '';
            html += '<div class="beat-func" style="color:' + barColor + '">' + escapeHtml(effectiveFunc) + sup + '</div>';
            var notesArr = beat.notes ? beat.notes.split(/\s+/).filter(function(n) { return n; }) : [];
            var nHtml = notesArr.map(function(n) { return '<span class="beat-note">' + escapeHtml(n) + '</span>'; }).join(' ');
            html += '<div class="beat-notes-row">' + nHtml + '</div><div class="beat-bar" style="background:' + barColor + '"></div>';
        } else {
            // isEmpty
            if (beat.annotation) html += '<div class="beat-ann">' + escapeHtml(beat.annotation) + '</div>';
            html += '<div class="beat-dash">-</div>';
        }
        html += '</div>';
        html += '<div class="beat-fields">';
        html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Type</div>' +
            '<select class="form-select form-select-sm bg-type-sel" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '">' +
            '<option value="normal"' + (isChord ? ' selected' : '') + '>Normal</option>' +
            '<option value="empty"' + (isEmpty ? ' selected' : '') + '>Empty</option>' +
            '<option value="pair_v_a">Pair V-A</option><option value="pair_v_b">Pair V-B</option></select></div>';

        if (isChord) {
            html += '<div class="d-flex gap-1 align-items-end" style="margin-top:4px">';
            html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Func</div>' +
                '<select class="form-select form-select-sm bg-func-sel" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" style="width:55px">' +
                '<option value="T"' + (effectiveFunc === 'T' ? ' selected' : '') + '>T</option>' +
                '<option value="S"' + (effectiveFunc === 'S' ? ' selected' : '') + '>S</option>' +
                '<option value="D"' + (effectiveFunc === 'D' ? ' selected' : '') + '>D</option>' +
                '<option value="_custom"' + (['T','S','D',''].indexOf(effectiveFunc) === -1 ? ' selected' : '') + '>Other</option></select></div>';
            html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">#</div>' +
                '<input type="text" class="form-control form-control-sm bg-super-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(beat.superscript || '') + '" placeholder="#" style="width:38px"></div></div>';
            if (['T','S','D',''].indexOf(effectiveFunc) === -1) {
                html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Custom Label</div>' +
                    '<input type="text" class="form-control form-control-sm bg-func-custom-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(effectiveFunc) + '" placeholder="Label"></div>';
            }
            html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Notes</div>' +
                '<input type="text" class="form-control form-control-sm bg-notes-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(beat.notes || '') + '" placeholder="1 3 5"></div>';
        }
        html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Top Annotation</div>' +
            '<input type="text" class="form-control form-control-sm bg-ann-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(beat.annotation || '') + '" placeholder="e.g. OLD, NEW"></div>';
        html += '</div></div>';
        return html;
    }

    /**
     * Replace a single beat card in the DOM by its gridId, rowIdx, and beat index.
     * No full re-render needed - the state is already up to date.
     */
    function replaceBeatCard(gridId, rowIdx, i) {
        var grid = beatGrids[gridId];
        if (!grid || !grid.rows[rowIdx] || !grid.rows[rowIdx].beats[i]) return;
        var beat = grid.rows[rowIdx].beats[i];
        var newHtml = buildSingleBeatCardHtml(gridId, rowIdx, beat, i);
        var $card = $('.beat-card[data-bg="' + gridId + '"][data-row="' + rowIdx + '"][data-idx="' + i + '"]');
        $card.replaceWith(newHtml);
    }

    function renderBeatGrid(gridId) {
        var grid = beatGrids[gridId];
        if (!grid) return;
        var mode = $('.bg-mode[data-bg="' + gridId + '"]').val() || 'cells';
        var html = '';

        grid.rows.forEach(function(row, rowIdx) {
            html += '<div class="beat-grid-row-container mb-3 border-bottom pb-2">';
            html += '<div class="beat-row-header d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<input type="text" class="form-control form-control-sm bg-row-title fw-bold text-muted border-0 bg-transparent px-0" data-bg="' + gridId + '" data-row="' + rowIdx + '" value="' + escapeHtml(row.title) + '" placeholder="Row label" style="width: 180px; font-size:12px; letter-spacing:0.5px; text-transform:uppercase; opacity:' + (row.title ? '1' : '0.45') + ';">';
            var startCellMax = mode === 'held' ? (row.segments || []).length - 1 : (row.beats || []).length - 1;
            if (startCellMax < 0) startCellMax = 0;
            html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Start Cell (0-based)</div>' +
                '<input type="number" min="0" max="' + startCellMax + '" class="form-control form-control-sm bg-start-from-cell" data-bg="' + gridId + '" data-row="' + rowIdx + '" value="' + (row.start_from_cell !== undefined ? row.start_from_cell : '') + '" placeholder="0" style="width:60px"></div>';
            html += '</div>';
            html += '<div class="d-flex gap-1">';
            if (mode === 'held') {
                html += '<button type="button" class="btn btn-sm btn-outline-success bg-held-add-segment me-2" data-bg="' + gridId + '" data-row="' + rowIdx + '">+ Segment</button>';
            } else {
                html += '<button type="button" class="btn btn-sm btn-outline-success bg-add-beat me-2" data-bg="' + gridId + '" data-row="' + rowIdx + '">+ Add Beat</button>';
                html += '<button type="button" class="btn btn-sm btn-outline-danger bg-del-beat me-2" data-bg="' + gridId + '" data-row="' + rowIdx + '">- Remove Beat</button>';
            }
            html += '<button type="button" class="btn btn-sm btn-outline-danger bg-del-row" data-bg="' + gridId + '" data-row="' + rowIdx + '">Remove Row</button></div></div>';

            // Wrap ALL beat cards in scrollable horizontal container with uniform cards
            html += '<div class="beat-grid-scroll mb-2"><div class="beat-grid-row">';
            if (mode === 'held') {
                (row.segments || []).forEach(function(segment, sIdx) {
                    var barColor = getFunctionColor(segment.world);
                    var rootHtml = segment.root ? '<div class="beat-ann" style="color:#64748b">' + escapeHtml(segment.root) + '</div>' : '';
                    var notesArr = (segment.notes || '').split(/\\s+/).filter(function(n) { return n; });
                    var nHtml = notesArr.map(function(n) { return '<span class="beat-note">' + escapeHtml(n) + '</span>'; }).join(' ');
                    html += '<div class="beat-card held-segment-card">' +
                        '<div class="beat-num">' + (parseInt(segment.beat_from) + 1) + '-' + (parseInt(segment.beat_to) + 1) + '</div>' +
                        '<div class="beat-preview">' + rootHtml + '<div class="beat-func" style="color:' + barColor + '">' + escapeHtml(segment.world) + '</div>' +
                        '<div class="beat-notes-row">' + nHtml + '</div><div class="beat-bar" style="background:' + barColor + '"></div></div>' +
                        '<div class="beat-fields mt-1">' +
                        '<div class="d-flex gap-1 align-items-end">' +
                        '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Func</div><input class="form-control form-control-sm bg-held-world" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-seg="' + sIdx + '" value="' + escapeHtml(segment.world) + '" placeholder="T#" style="width:55px"></div>' +
                        '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">From</div><input type="number" min="0" class="form-control form-control-sm bg-held-from" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-seg="' + sIdx + '" value="' + segment.beat_from + '" style="width:45px"></div>' +
                        '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">To</div><input type="number" min="0" class="form-control form-control-sm bg-held-to" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-seg="' + sIdx + '" value="' + segment.beat_to + '" style="width:45px"></div></div>' +
                        '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Notes</div><input class="form-control form-control-sm bg-held-notes" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-seg="' + sIdx + '" value="' + escapeHtml(segment.notes || '') + '" placeholder="1 3# 5"></div>' +
                        '<div class="d-flex gap-1 align-items-end" style="margin-top:4px">' +
                        '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Weight</div><input type="number" step="0.1" min="0.1" class="form-control form-control-sm bg-held-weight" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-seg="' + sIdx + '" value="' + (segment.weight || 1) + '" style="width:50px"></div>' +
                        '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Root Label</div><input class="form-control form-control-sm bg-held-root" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-seg="' + sIdx + '" value="' + escapeHtml(segment.root || '') + '" placeholder="1 = A#" style="width:90px"></div></div>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger mt-2 bg-held-del-segment" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-seg="' + sIdx + '">Remove</button></div></div>';
                });
                html += '</div></div></div>';
                return;
            }

            var numMode = ($('.bg-numbering-mode[data-bg="' + gridId + '"]').val() || 'restart_at_8');
            row.beats.forEach(function(beat, i) {
                var beatNum = numMode === 'continuous' ? (i + 1) : ((i % 8) + 1);
                var isOctaveStart = numMode !== 'continuous' && i > 0 && i % 8 === 0;

                if (beat.type === 'pair' || beat.type === 'pair_v_a' || beat.type === 'pair_v_b') {
                    html += buildSingleBeatCardHtml(gridId, rowIdx, beat, i);
                    return;
                }

                // Treat any type that is not explicitly 'empty' as a chord/normal beat.
                // This prevents Normal-type cards from collapsing when type is missing/unrecognised.
                var isEmpty = beat.type === 'empty';
                var isChord = !isEmpty;
                var effectiveFunc = (beat.function && beat.function.trim()) ? beat.function : 'T';
                var barColor = isChord ? getFunctionColor(effectiveFunc) : '#cbd5e1';
                html += '<div class="beat-card" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '">';
                html += '<div class="beat-num' + (isOctaveStart ? ' beat-num-cycle-start' : '') + '">' + beatNum + '</div>';
                html += '<div class="beat-preview' + (isEmpty ? ' is-empty' : '') + '">';
                if (isChord) {
                    if (beat.annotation) html += '<div class="beat-ann">' + escapeHtml(beat.annotation) + '</div>';
                    var sup = beat.superscript ? '<sup>' + escapeHtml(beat.superscript) + '</sup>' : '';
                    html += '<div class="beat-func" style="color:' + barColor + '">' + escapeHtml(effectiveFunc) + sup + '</div>';
                    var notes = beat.notes ? beat.notes.split(/\s+/).filter(function(n) { return n; }) : [];
                    var nHtml = notes.map(function(n) { return '<span class="beat-note">' + escapeHtml(n) + '</span>'; }).join(' ');
                    html += '<div class="beat-notes-row">' + nHtml + '</div><div class="beat-bar" style="background:' + barColor + '"></div>';
                } else {
                    // isEmpty
                    if (beat.annotation) html += '<div class="beat-ann">' + escapeHtml(beat.annotation) + '</div>';
                    html += '<div class="beat-dash">-</div>';
                }
                html += '</div>';
                html += '<div class="beat-fields">';
                html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Type</div>' +
                    '<select class="form-select form-select-sm bg-type-sel" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '">' +
                    '<option value="normal"' + (isChord ? ' selected' : '') + '>Normal</option>' +
                    '<option value="empty"' + (isEmpty ? ' selected' : '') + '>Empty</option>' +
                    '<option value="pair_v_a">Pair V-A</option><option value="pair_v_b">Pair V-B</option></select></div>';

                if (isChord) {
                    html += '<div class="d-flex gap-1 align-items-end" style="margin-top:4px">';
                    html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">Func</div>' +
                        '<select class="form-select form-select-sm bg-func-sel" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" style="width:55px">' +
                        '<option value="T"' + (effectiveFunc === 'T' ? ' selected' : '') + '>T</option>' +
                        '<option value="S"' + (effectiveFunc === 'S' ? ' selected' : '') + '>S</option>' +
                        '<option value="D"' + (effectiveFunc === 'D' ? ' selected' : '') + '>D</option>' +
                        '<option value="_custom"' + (['T','S','D',''].indexOf(effectiveFunc) === -1 ? ' selected' : '') + '>Other</option></select></div>';
                    html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px">#</div>' +
                        '<input type="text" class="form-control form-control-sm bg-super-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(beat.superscript || '') + '" placeholder="#" style="width:38px"></div></div>';
                    if (['T','S','D',''].indexOf(effectiveFunc) === -1) {
                        html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Custom Label</div>' +
                            '<input type="text" class="form-control form-control-sm bg-func-custom-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(effectiveFunc) + '" placeholder="Label"></div>';
                    }
                    html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Notes</div>' +
                        '<input type="text" class="form-control form-control-sm bg-notes-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(beat.notes || '') + '" placeholder="1 3 5"></div>';
                }
                html += '<div><div style="font-size:9px;color:#aaa;margin-bottom:1px;margin-top:4px">Top Annotation</div>' +
                    '<input type="text" class="form-control form-control-sm bg-ann-in" data-bg="' + gridId + '" data-row="' + rowIdx + '" data-idx="' + i + '" value="' + escapeHtml(beat.annotation || '') + '" placeholder="e.g. OLD, NEW"></div>';
                html += '</div></div>';
            });
            html += '</div></div></div>';
        });

        $('#grid_' + gridId).html(html);
        serializeBeatGrid(gridId);
    }

    function serializeBeatGrid(gridId) {
        var grid = beatGrids[gridId];
        if (!grid) return;
        // --- Read global fields from form ------------------------------------
        var visualTitle = $('.bg-title[data-bg="' + gridId + '"]').val() || '';
        var exampleTitle = $('.bg-example-title[data-bg="' + gridId + '"]').val() || '';
        var exampleDesc = $('.bg-example-desc[data-bg="' + gridId + '"]').val() || '';
        var tapToPlay = $('.bg-tap-play[data-bg="' + gridId + '"]').is(':checked');
        var showRoot = $('.bg-show-root[data-bg="' + gridId + '"]').is(':checked');
        var showReframeLabels = $('.bg-reframe-labels[data-bg="' + gridId + '"]').is(':checked');
        var playbackLabel = $('.bg-playback[data-bg="' + gridId + '"]').val() || '';
        var mode = $('.bg-mode[data-bg="' + gridId + '"]').val() || 'cells';
        var beatNumberingMode = $('.bg-numbering-mode[data-bg="' + gridId + '"]').val() || 'restart_at_8';

        var canonicalRows = grid.rows.map(function(row, rowIdx) {
            if (mode === 'held') {
                var heldRow = {
                    mode: 'held',
                    segments: (row.segments || []).map(function(s) {
                        return {
                            world: s.world || 'T',
                            beat_from: parseInt(s.beat_from) || 0,
                            beat_to: parseInt(s.beat_to) || 0,
                            weight: parseFloat(s.weight) || 1,
                            notes: noteObjects(s.notes || '', s.world || 'T'),
                            root: rootObject(s.root || ''),
                        };
                    })
                };
                if (row.title && row.title.trim()) heldRow.label = row.title.trim();
                return heldRow;
            }

            // Read start_from_cell from DOM
            var $startInput = $('.bg-start-from-cell[data-bg="' + gridId + '"][data-row="' + rowIdx + '"]');
            var startFromCell = $startInput.length ? parseInt($startInput.val()) : (row.start_from_cell || 0);
            if (isNaN(startFromCell) || startFromCell < 0) startFromCell = 0;
            var maxStartFromCell = Math.max(0, (row.beats || []).length - 1);
            if (startFromCell > maxStartFromCell) startFromCell = maxStartFromCell;

            var cells = (row.beats || []).map(function(beat, slotIdx) {
                var slot = slotIdx + 1;
                var cellType = 'normal';
                if (beat.type === 'pair' || beat.type === 'pair_v_a' || beat.type === 'pair_v_b') cellType = beat.variant === 'A' ? 'pair_v_a' : 'pair_v_b';
                else if (beat.type === 'empty') cellType = 'empty';
                var cell = { type: cellType, beat: slot, functions: [] };
                if (beat.annotation) {
                    cell.annotation = beat.annotation;
                }
                if (beat.type === 'chord' || beat.type === 'normal') {
                    cell.functions = [chordFunction(beat)];
                } else if (beat.type === 'pair' || beat.type === 'pair_v_a' || beat.type === 'pair_v_b') {
                    cell.functions = [chordFunction(beat.left || {}, 'old'), chordFunction(beat.right || {}, 'new')];
                    cell.reframe = {
                        variant: beat.variant || 'B',
                        roots: {
                            old: rootObject((beat.left || {}).root_annotation || ''),
                            new: rootObject((beat.right || {}).root_annotation || ''),
                        }
                    };
                    // Pair-specific editable fields
                    if (beat.editable) cell.editable = true;
                    if (beat.allowable_notes && beat.allowable_notes.length) cell.allowable_notes = beat.allowable_notes;
                    if (beat.centre_voicing) cell.centre_voicing = beat.centre_voicing;
                    if (beat.octave_range !== undefined && beat.octave_range !== null && beat.octave_range !== '') {
                        cell.octave_range = parseInt(beat.octave_range);
                    }
                }
                return cell;
            });
            var rowPayload = { mode: 'cells', cells: cells };
            if (row.title && row.title.trim()) rowPayload.label = row.title.trim();
            if (startFromCell > 0) rowPayload.start_from_cell = startFromCell;
            return rowPayload;
        });

        var maxBeat = 0;
        grid.rows.forEach(function(row) {
            if (row.beats && row.beats.length > maxBeat) {
                maxBeat = row.beats.length;
            }
        });
        if (maxBeat === 0) maxBeat = 4;
        var beats = Array.from({ length: maxBeat }, function(_, i) {
            return { label: String(i + 1) };
        });

        var payload = {
            type: 'sentence_visual',
            id: '',
            mode: mode,
            beat_numbering_mode: beatNumberingMode,
            // persist under both keys for backward compat with existing API consumers
            title: exampleTitle,
            example_title: exampleTitle,
            description: exampleDesc,
            example_description: exampleDesc,
            label: visualTitle,
            tap_to_play: tapToPlay,
            show_root_toggle: showRoot,
            show_reframe_labels: showReframeLabels,
            playback_label: playbackLabel,
            beats: beats,
            rows: canonicalRows
        };
        // Omit optional fields if blank
        if (!exampleTitle) { delete payload.title; delete payload.example_title; }
        if (!exampleDesc) { delete payload.description; delete payload.example_description; }
        if (!visualTitle) delete payload.label;

        $('#json_' + gridId).val(JSON.stringify(payload));
    }

    // --- Beat Grid Event Delegation ---------------------------------------
    $(document).on('change', '.bg-type-sel', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (!beatGrids[g]) return;
        var newType = $(this).val();
        if (newType === 'pair_v_a' || newType === 'pair_v_b') {
            beatGrids[g].rows[r].beats[i] = newPairBeat(newType === 'pair_v_a' ? 'A' : 'B');
        } else if (newType === 'empty') {
            beatGrids[g].rows[r].beats[i] = newEmptyBeat();
        } else {
            beatGrids[g].rows[r].beats[i] = newChordBeat();
        }
        replaceBeatCard(g, r, i);
        serializeBeatGrid(g);
    });

    $(document).on('change', '.bg-func-sel', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || !beatGrids[g].rows[r].beats[i]) return;
        var val = $(this).val();
        if (val === '_custom') {
            val = 'Custom';
        }
        beatGrids[g].rows[r].beats[i].function = val;
        replaceBeatCard(g, r, i);
        serializeBeatGrid(g);
    });

    $(document).on('input', '.bg-func-custom-in', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            beatGrids[g].rows[r].beats[i].function = $(this).val();
            
            var sup = beatGrids[g].rows[r].beats[i].superscript || '';
            var supHtml = sup ? '<sup>' + escapeHtml(sup) + '</sup>' : '';
            var fnText = $(this).val() || 'T';
            $(this).closest('.beat-card').find('.beat-func').html(escapeHtml(fnText) + supHtml);
            
            serializeBeatGrid(g);
        }
    });

    $(document).on('input', '.bg-super-in', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            beatGrids[g].rows[r].beats[i].superscript = $(this).val();
            
            var fnText = beatGrids[g].rows[r].beats[i].function || 'T';
            var supHtml = $(this).val() ? '<sup>' + escapeHtml($(this).val()) + '</sup>' : '';
            $(this).closest('.beat-card').find('.beat-func').html(escapeHtml(fnText) + supHtml);
            
            serializeBeatGrid(g);
        }
    });

    $(document).on('input', '.bg-notes-in', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            beatGrids[g].rows[r].beats[i].notes = $(this).val();
            
            var notesArr = $(this).val() ? $(this).val().split(/\s+/).filter(function(n) { return n; }) : [];
            var nHtml = notesArr.map(function(n) { return '<span class="beat-note">' + escapeHtml(n) + '</span>'; }).join(' ');
            $(this).closest('.beat-card').find('.beat-notes-row').html(nHtml);
            
            serializeBeatGrid(g);
        }
    });

    $(document).on('input', '.bg-ann-in', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            var val = $(this).val();
            beatGrids[g].rows[r].beats[i].annotation = val;
            
            var $card = $(this).closest('.beat-card');
            var $ann = $card.find('.beat-preview .beat-ann');
            if (val && val.trim()) {
                if ($ann.length) {
                    $ann.text(val);
                } else {
                    $card.find('.beat-preview').prepend('<div class="beat-ann">' + escapeHtml(val) + '</div>');
                }
            } else {
                if ($ann.length) {
                    $ann.remove();
                }
            }
            
            serializeBeatGrid(g);
        }
    });


    $(document).on('change', '.bg-mode', function() {
        var g = $(this).data('bg');
        if (!beatGrids[g]) return;
        var newMode = $(this).val();
        beatGrids[g].rows.forEach(function(row) {
            row.mode = newMode;
            if (newMode === 'held') {
                row._savedBeats = row.beats;
                if (!row.segments || row.segments.length === 0) {
                    row.segments = [{ world: 'T', beat_from: 0, beat_to: 3, weight: 1, notes: '', root: '' }];
                }
                delete row.beats;
            } else {
                row.beats = row._savedBeats || row.beats || [newEmptyBeat(), newEmptyBeat(), newEmptyBeat(), newEmptyBeat()];
                delete row._savedBeats;
                delete row.segments;
            }
        });
        renderBeatGrid(g);
    });

    $(document).on('click', '.bg-add-row', function(e) {
        e.stopImmediatePropagation();
        var g = $(this).data('bg');
        if (beatGrids[g]) {
            var currentMode = $('.bg-mode[data-bg="' + g + '"]').val() || 'cells';
            if (currentMode === 'held') {
                beatGrids[g].rows.push({ title: '', mode: 'held', segments: [{ world: 'T', beat_from: 0, beat_to: 3, weight: 1, notes: '', root: '' }] });
            } else {
                beatGrids[g].rows.push({ title: '', mode: 'cells', beats: [newEmptyBeat(), newEmptyBeat(), newEmptyBeat(), newEmptyBeat()] });
            }
            renderBeatGrid(g);
        }
    });

    $(document).on('click', '.bg-del-row', function(e) {
        e.stopImmediatePropagation();
        var g = $(this).data('bg'), r = $(this).data('row');
        if (beatGrids[g] && beatGrids[g].rows.length > 1) {
            beatGrids[g].rows.splice(r, 1);
            renderBeatGrid(g);
        }
    });

    $(document).on('click', '.bg-add-beat', function(e) {
        e.stopImmediatePropagation();
        var g = $(this).data('bg'), r = $(this).data('row');
        if (beatGrids[g] && beatGrids[g].rows[r]) {
            var newIdx = beatGrids[g].rows[r].beats.length;
            beatGrids[g].rows[r].beats.push(newEmptyBeat());
            var newHtml = buildSingleBeatCardHtml(g, r, newEmptyBeat(), newIdx);
            var $beatRow = $(this).closest('.beat-grid-row-container').find('.beat-grid-scroll > .beat-grid-row');
            $beatRow.append(newHtml);
            serializeBeatGrid(g);
        }
    });

    $(document).on('click', '.bg-del-beat', function(e) {
        e.stopImmediatePropagation();
        var g = $(this).data('bg'), r = $(this).data('row');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats.length > 1) {
            beatGrids[g].rows[r].beats.pop();
            var $container = $(this).closest('.beat-grid-row-container');
            $container.find('.beat-card[data-bg="' + g + '"][data-row="' + r + '"]').last().remove();
            // Update beat numbering - cycles every 8 beats
            $container.find('.beat-num').each(function(idx) {
                var cycleNum = (idx % 8) + 1;
                $(this).text(cycleNum);
                if (idx > 0 && idx % 8 === 0) {
                    $(this).addClass('beat-num-cycle-start');
                } else {
                    $(this).removeClass('beat-num-cycle-start');
                }
            });
            serializeBeatGrid(g);
        }
    });

    // --- Pair beat event handlers ------------------------------------------
    $(document).on('change', '.bg-pair-func', function() {
        var g = $(this).data('bg'), row = $(this).data('row'), idx = $(this).data('idx'), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[row] || !beatGrids[g].rows[row].beats[idx]) return;
        var val = $(this).val();
        if (val === '_custom') {
            val = 'Custom';
        }
        if (side === 'left') {
            beatGrids[g].rows[row].beats[idx].left.function = val;
        } else {
            beatGrids[g].rows[row].beats[idx].right.function = val;
        }
        replaceBeatCard(g, row, idx);
        serializeBeatGrid(g);
    });

    $(document).on('input', '.bg-pair-super', function() {
        var g = $(this).data('bg'), row = $(this).data('row'), idx = $(this).data('idx'), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[row] || !beatGrids[g].rows[row].beats[idx]) return;
        if (side === 'left') beatGrids[g].rows[row].beats[idx].left.superscript = $(this).val();
        else beatGrids[g].rows[row].beats[idx].right.superscript = $(this).val();
        
        var bSide = side === 'left' ? beatGrids[g].rows[row].beats[idx].left : beatGrids[g].rows[row].beats[idx].right;
        var fnText = bSide.function || 'T';
        var supHtml = $(this).val() ? '<sup>' + escapeHtml($(this).val()) + '</sup>' : '';
        var $sideCont = side === 'left' ? $(this).closest('.beat-card').find('.pair-side').first() : $(this).closest('.beat-card').find('.pair-side').last();
        $sideCont.find('.beat-func').html(escapeHtml(fnText) + supHtml);
        
        serializeBeatGrid(g);
    });

    $(document).on('input', '.bg-pair-func-custom-in', function() {
        var g = $(this).data('bg'), row = $(this).data('row'), idx = $(this).data('idx'), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[row] || !beatGrids[g].rows[row].beats[idx]) return;
        if (side === 'left') beatGrids[g].rows[row].beats[idx].left.function = $(this).val();
        else beatGrids[g].rows[row].beats[idx].right.function = $(this).val();
        
        var bSide = side === 'left' ? beatGrids[g].rows[row].beats[idx].left : beatGrids[g].rows[row].beats[idx].right;
        var sup = bSide.superscript || '';
        var supHtml = sup ? '<sup>' + escapeHtml(sup) + '</sup>' : '';
        var fnText = $(this).val() || 'T';
        var $sideCont = side === 'left' ? $(this).closest('.beat-card').find('.pair-side').first() : $(this).closest('.beat-card').find('.pair-side').last();
        $sideCont.find('.beat-func').html(escapeHtml(fnText) + supHtml);
        
        serializeBeatGrid(g);
    });

    $(document).on('input', '.bg-pair-notes', function() {
        var g = $(this).data('bg'), row = $(this).data('row'), idx = $(this).data('idx'), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[row] || !beatGrids[g].rows[row].beats[idx]) return;
        if (side === 'left') beatGrids[g].rows[row].beats[idx].left.notes = $(this).val();
        else beatGrids[g].rows[row].beats[idx].right.notes = $(this).val();
        
        var notesArr = $(this).val() ? $(this).val().split(/\s+/).filter(function(n) { return n; }) : [];
        var nHtml = notesArr.map(function(n) { return '<span class="beat-note">' + escapeHtml(n) + '</span>'; }).join(' ');
        var $sideCont = side === 'left' ? $(this).closest('.beat-card').find('.pair-side').first() : $(this).closest('.beat-card').find('.pair-side').last();
        $sideCont.find('.beat-notes-row').html(nHtml);
        
        serializeBeatGrid(g);
    });

    $(document).on('input', '.bg-pair-ann', function() {
        var g = $(this).data('bg'), row = $(this).data('row'), idx = $(this).data('idx'), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[row] || !beatGrids[g].rows[row].beats[idx]) return;
        if (side === 'left') beatGrids[g].rows[row].beats[idx].left.annotation = $(this).val();
        else beatGrids[g].rows[row].beats[idx].right.annotation = $(this).val();
        
        var $sideCont = side === 'left' ? $(this).closest('.beat-card').find('.pair-side').first() : $(this).closest('.beat-card').find('.pair-side').last();
        var $ann = $sideCont.find('.beat-ann');
        if ($ann.length) {
            $ann.text($(this).val());
        } else if ($(this).val()) {
            $sideCont.prepend('<div class="beat-ann">' + escapeHtml($(this).val()) + '</div>');
        }
        
        serializeBeatGrid(g);
    });

    $(document).on('input', '.bg-pair-root-ann-in', function() {
        var g = $(this).data('bg'), row = $(this).data('row'), idx = $(this).data('idx'), side = $(this).data('side');
        if (!beatGrids[g] || !beatGrids[g].rows[row] || !beatGrids[g].rows[row].beats[idx]) return;
        if (side === 'left') beatGrids[g].rows[row].beats[idx].left.root_annotation = $(this).val();
        else beatGrids[g].rows[row].beats[idx].right.root_annotation = $(this).val();
        serializeBeatGrid(g);
    });

    // --- Row title and mode changes ---------------------------------------
    $(document).on('input', '.bg-row-title', function() {
        var g = $(this).data('bg'), r = $(this).data('row');
        if (beatGrids[g] && beatGrids[g].rows[r]) {
            beatGrids[g].rows[r].title = $(this).val();
            $(this).css('opacity', $(this).val() ? '1' : '0.45');
            serializeBeatGrid(g);
        }
    });

    // --- New: start_from_cell per-row ----------------------------------------
    $(document).on('input change', '.bg-start-from-cell', function() {
        var g = $(this).data('bg'), r = $(this).data('row');
        if (beatGrids[g] && beatGrids[g].rows[r]) {
            var val = parseInt($(this).val());
            beatGrids[g].rows[r].start_from_cell = isNaN(val) || val < 0 ? 0 : val;
            serializeBeatGrid(g);
        }
    });

    // --- New: beat numbering mode (global per grid) ---------------------------
    $(document).on('change', '.bg-numbering-mode', function() {
        var g = $(this).data('bg');
        if (beatGrids[g]) {
            renderBeatGrid(g);
        }
    });

    // --- New: pair beat editable controls ------------------------------------
    $(document).on('change', '.bg-editable-toggle', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            beatGrids[g].rows[r].beats[i].editable = $(this).is(':checked');
            serializeBeatGrid(g);
        }
    });

    $(document).on('input', '.bg-allowable-notes-in', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            var notes = $(this).val().trim().split(/\s+/).filter(function(n) { return n; });
            beatGrids[g].rows[r].beats[i].allowable_notes = notes;
            serializeBeatGrid(g);
        }
    });

    $(document).on('input', '.bg-centre-voicing-in', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            beatGrids[g].rows[r].beats[i].centre_voicing = $(this).val();
            serializeBeatGrid(g);
        }
    });

    $(document).on('input', '.bg-octave-range-in', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), i = $(this).data('idx');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].beats[i]) {
            var val = $(this).val() !== '' ? parseInt($(this).val()) : undefined;
            beatGrids[g].rows[r].beats[i].octave_range = val;
            serializeBeatGrid(g);
        }
    });


    $(document).on('change', '.bg-row-mode', function() {
        var g = $(this).data('bg'), r = $(this).data('row');
        if (!beatGrids[g] || !beatGrids[g].rows[r]) return;
        var newMode = $(this).val();
        beatGrids[g].rows[r].mode = newMode;
        if (newMode === 'held' && !beatGrids[g].rows[r].segments) {
            beatGrids[g].rows[r].segments = [{ world: 'T', beat_from: 0, beat_to: 0, weight: 1, notes: '', root: '' }];
        }
        renderBeatGrid(g);
    });

    // --- Held mode segment events -----------------------------------------
    $(document).on('click', '.bg-held-add-segment', function() {
        var g = $(this).data('bg'), r = $(this).data('row');
        if (beatGrids[g] && beatGrids[g].rows[r]) {
            beatGrids[g].rows[r].segments.push({ world: 'T', beat_from: 0, beat_to: 0, weight: 1, notes: '', root: '' });
            renderBeatGrid(g);
        }
    });

    $(document).on('click', '.bg-held-del-segment', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), s = $(this).data('seg');
        if (beatGrids[g] && beatGrids[g].rows[r] && beatGrids[g].rows[r].segments.length > 1) {
            beatGrids[g].rows[r].segments.splice(s, 1);
            renderBeatGrid(g);
        }
    });

    $(document).on('input', '.bg-held-world, .bg-held-from, .bg-held-to, .bg-held-notes, .bg-held-weight, .bg-held-root', function() {
        var g = $(this).data('bg'), r = $(this).data('row'), s = $(this).data('seg');
        if (!beatGrids[g] || !beatGrids[g].rows[r] || !beatGrids[g].rows[r].segments[s]) return;
        var seg = beatGrids[g].rows[r].segments[s];
        if ($(this).hasClass('bg-held-world')) seg.world = $(this).val();
        if ($(this).hasClass('bg-held-from')) seg.beat_from = parseInt($(this).val()) || 0;
        if ($(this).hasClass('bg-held-to')) seg.beat_to = parseInt($(this).val()) || 0;
        if ($(this).hasClass('bg-held-notes')) seg.notes = $(this).val();
        if ($(this).hasClass('bg-held-weight')) seg.weight = parseFloat($(this).val()) || 1;
        if ($(this).hasClass('bg-held-root')) seg.root = $(this).val();
        serializeBeatGrid(g);
    });

    // --- Title, Example, Playback field serialization ---------------------
    $(document).on('input', '.bg-title, .bg-example-title, .bg-example-desc, .bg-playback', function() {
        serializeBeatGrid($(this).data('bg'));
    });

    $(document).on('change', '.bg-tap-play, .bg-show-root, .bg-reframe-labels', function() {
        serializeBeatGrid($(this).data('bg'));
    });

    /* =======================================================================
     * FNH ELEMENT BUILDERS
     * ======================================================================= */

    // -- template_form_table ----------------------------------------------
    function initTft(gridId, data) {
        fnhData[gridId] = {
            t_form_color: data.t_form_color || '#2457E6',
            t_sharp_form_color: data.t_sharp_form_color || '#E97B00',
            rows: Array.isArray(data.rows) && data.rows.length > 0
                ? data.rows.map(function(r) {
                    return {
                        template: r.template || '', t_form: r.t_form || '', t_sharp_form: r.t_sharp_form || '',
                        template_color: r.template_color || '', t_form_color: r.t_form_color || '', t_sharp_form_color: r.t_sharp_form_color || ''
                    };
                  })
                : [{template:'', t_form:'', t_sharp_form:'', template_color:'', t_form_color:'', t_sharp_form_color:''}]
        };
        renderTft(gridId);
    }

    function renderTft(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        var html = '<table class="table table-bordered table-sm mb-0"><thead><tr><th>TEMPLATE COLOR</th><th>TEMPLATE</th><th>T FORM COLOR</th><th>T FORM</th><th>T# FORM COLOR</th><th>T# FORM</th></tr></thead><tbody>';
        d.rows.forEach(function(r, i) {
            var tplBg = r.template_color ? ' style="background:' + r.template_color + '"' : '';
            var tfBg = r.t_form_color ? ' style="background:' + r.t_form_color + '"' : '';
            var tsfBg = r.t_sharp_form_color ? ' style="background:' + r.t_sharp_form_color + '"' : '';
            html += '<tr><td style="text-align:center;vertical-align:middle"><div class="d-flex flex-column align-items-center gap-1">' +
                '<input type="color" class="form-control form-control-color fnh-tft-tmpl-color" data-grid="' + gridId + '" data-r="' + i + '" value="' + (r.template_color || '#ffffff') + '" style="width:36px;height:28px;padding:2px;cursor:pointer"></div></td>' +
                '<td' + tplBg + '><input type="text" class="form-control form-control-sm fnh-tft-tmpl" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.template) + '" placeholder="Template name"></td>' +
                '<td style="text-align:center;vertical-align:middle"><div class="d-flex flex-column align-items-center gap-1">' +
                '<input type="color" class="form-control form-control-color fnh-tft-tf-color" data-grid="' + gridId + '" data-r="' + i + '" value="' + (r.t_form_color || '#ffffff') + '" style="width:36px;height:28px;padding:2px;cursor:pointer"></div></td>' +
                '<td' + tfBg + '><input type="text" class="form-control form-control-sm fnh-tft-tf" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.t_form) + '" placeholder="T->S->D->D#" style="color:' + d.t_form_color + '"></td>' +
                '<td style="text-align:center;vertical-align:middle"><div class="d-flex flex-column align-items-center gap-1">' +
                '<input type="color" class="form-control form-control-color fnh-tft-tsf-color" data-grid="' + gridId + '" data-r="' + i + '" value="' + (r.t_sharp_form_color || '#ffffff') + '" style="width:36px;height:28px;padding:2px;cursor:pointer"></div></td>' +
                '<td' + tsfBg + '><input type="text" class="form-control form-control-sm fnh-tft-tsf" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.t_sharp_form) + '" placeholder="T#->S#->D->D#" style="color:' + d.t_sharp_form_color + '"></td></tr>';
        });
        html += '</tbody></table>';
        $('#grid_' + gridId).html(html);
        serializeTft(gridId);
    }

    function serializeTft(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        d.t_form_color = $('.fnh-tft-t-color[data-grid="' + gridId + '"]').val() || d.t_form_color;
        d.t_sharp_form_color = $('.fnh-tft-ts-color[data-grid="' + gridId + '"]').val() || d.t_sharp_form_color;
        $('.fnh-tft-tmpl-color[data-grid="' + gridId + '"]').each(function() {
            var r = $(this).data('r'); if (d.rows[r]) d.rows[r].template_color = $(this).val() !== '#ffffff' ? $(this).val() : '';
        });
        $('.fnh-tft-tf-color[data-grid="' + gridId + '"]').each(function() {
            var r = $(this).data('r'); if (d.rows[r]) d.rows[r].t_form_color = $(this).val() !== '#ffffff' ? $(this).val() : '';
        });
        $('.fnh-tft-tsf-color[data-grid="' + gridId + '"]').each(function() {
            var r = $(this).data('r'); if (d.rows[r]) d.rows[r].t_sharp_form_color = $(this).val() !== '#ffffff' ? $(this).val() : '';
        });
        $('#json_' + gridId).val(JSON.stringify(d));
    }

    // --- TFT Event Delegation --------------------------------------------
    $(document).on('input', '.fnh-tft-tmpl', function() {
        var g = $(this).data('grid'), r = $(this).data('r');
        if (fnhData[g]) { fnhData[g].rows[r].template = $(this).val(); serializeTft(g); }
    });
    $(document).on('input', '.fnh-tft-tf', function() {
        var g = $(this).data('grid'), r = $(this).data('r');
        if (fnhData[g]) { fnhData[g].rows[r].t_form = $(this).val(); serializeTft(g); }
    });
    $(document).on('input', '.fnh-tft-tsf', function() {
        var g = $(this).data('grid'), r = $(this).data('r');
        if (fnhData[g]) { fnhData[g].rows[r].t_sharp_form = $(this).val(); serializeTft(g); }
    });
    $(document).on('input', '.fnh-tft-t-color, .fnh-tft-ts-color', function() {
        var g = $(this).data('grid');
        if (fnhData[g]) { serializeTft(g); renderTft(g); }
    });
    $(document).on('input', '.fnh-tft-tmpl-color, .fnh-tft-tf-color, .fnh-tft-tsf-color', function() {
        var g = $(this).data('grid'), r = $(this).data('r');
        if (fnhData[g] && fnhData[g].rows[r]) {
            var cls = $(this).hasClass('fnh-tft-tmpl-color') ? 'template_color'
                : $(this).hasClass('fnh-tft-tf-color') ? 't_form_color' : 't_sharp_form_color';
            var val = $(this).val();
            fnhData[g].rows[r][cls] = val !== '#ffffff' ? val : '';
            serializeTft(g);
            $(this).closest('td').next('td').css('background', fnhData[g].rows[r][cls] || '');
        }
    });
    $(document).on('dblclick', '.fnh-tft-tmpl-color, .fnh-tft-tf-color, .fnh-tft-tsf-color', function(e) {
        e.preventDefault(); e.stopPropagation();
        var g = $(this).data('grid'), r = $(this).data('r');
        if (fnhData[g] && fnhData[g].rows[r]) {
            var cls = $(this).hasClass('fnh-tft-tmpl-color') ? 'template_color'
                : $(this).hasClass('fnh-tft-tf-color') ? 't_form_color' : 't_sharp_form_color';
            fnhData[g].rows[r][cls] = '';
            $(this).val('#ffffff');
            $(this).closest('td').next('td').css('background', '');
            serializeTft(g);
        }
    });
    $(document).on('click', '.fnh-tft-add-row', function() {
        var g = $(this).data('grid');
        if (fnhData[g]) {
            fnhData[g].rows.push({template:'', t_form:'', t_sharp_form:'', template_color:'', t_form_color:'', t_sharp_form_color:''});
            renderTft(g);
        }
    });
    $(document).on('click', '.fnh-tft-del-row', function() {
        var g = $(this).data('grid');
        if (fnhData[g] && fnhData[g].rows.length > 1) { fnhData[g].rows.pop(); renderTft(g); }
    });

    // -- movement_quality_table -------------------------------------------
    function initMqt(gridId, data) {
        fnhData[gridId] = {
            rows: Array.isArray(data.rows) && data.rows.length > 0
                ? data.rows.map(function(r) { return {movement: r.movement||'', movement_color: r.movement_color||'#16A34A', emotional_quality: r.emotional_quality||'', from_t: r.from_t||'', from_t_sharp: r.from_t_sharp||''}; })
                : [{movement:'', movement_color:'#16A34A', emotional_quality:'', from_t:'', from_t_sharp:''}]
        };
        renderMqt(gridId);
    }

    function renderMqt(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        var html = '<table class="table table-bordered table-sm mb-0"><thead><tr><th style="width:20%">MOVEMENT</th><th>EMOTIONAL QUALITY</th><th>FROM T</th><th>FROM T#</th></tr></thead><tbody>';
        d.rows.forEach(function(r, i) {
            html += '<tr><td><div class="d-flex gap-1 align-items-center">' +
                '<input type="color" class="form-control form-control-color form-control-sm fnh-mqt-color" data-grid="' + gridId + '" data-r="' + i + '" value="' + r.movement_color + '" style="width:30px;height:30px;padding:1px">' +
                '<input type="text" class="form-control form-control-sm fnh-mqt-name" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.movement) + '" placeholder="EXPANSION" style="color:' + r.movement_color + ';font-weight:700">' +
                '</div></td><td><input type="text" class="form-control form-control-sm fnh-mqt-eq" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.emotional_quality) + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm fnh-mqt-ft" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.from_t) + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm fnh-mqt-fts" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.from_t_sharp) + '"></td></tr>';
        });
        html += '</tbody></table>';
        $('#grid_' + gridId).html(html);
        serializeMqt(gridId);
    }

    function serializeMqt(gridId) { var d = fnhData[gridId]; if (!d) return; $('#json_' + gridId).val(JSON.stringify(d)); }

    $(document).on('input', '.fnh-mqt-name', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].movement = $(this).val(); serializeMqt(g); } });
    $(document).on('input', '.fnh-mqt-color', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].movement_color = $(this).val(); renderMqt(g); } });
    $(document).on('input', '.fnh-mqt-eq', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].emotional_quality = $(this).val(); serializeMqt(g); } });
    $(document).on('input', '.fnh-mqt-ft', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].from_t = $(this).val(); serializeMqt(g); } });
    $(document).on('input', '.fnh-mqt-fts', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].from_t_sharp = $(this).val(); serializeMqt(g); } });
    $(document).on('click', '.fnh-mqt-add-row', function() { var g=$(this).data('grid'); if (fnhData[g]) { fnhData[g].rows.push({movement:'',movement_color:'#16A34A',emotional_quality:'',from_t:'',from_t_sharp:''}); renderMqt(g); } });
    $(document).on('click', '.fnh-mqt-del-row', function() { var g=$(this).data('grid'); if (fnhData[g] && fnhData[g].rows.length > 1) { fnhData[g].rows.pop(); renderMqt(g); } });

    // -- centre_character_table -------------------------------------------
    function initCct(gridId, data) {
        fnhData[gridId] = {
            rows: Array.isArray(data.rows) && data.rows.length > 0
                ? data.rows.map(function(r) { return {centre: r.centre||'', centre_color: r.centre_color||'#2457E6', character: r.character||'', what_home_feels_like: r.what_home_feels_like||''}; })
                : [{centre:'', centre_color:'#2457E6', character:'', what_home_feels_like:''}]
        };
        renderCct(gridId);
    }

    function renderCct(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        var html = '<table class="table table-bordered table-sm mb-0"><thead><tr><th style="width:15%">CENTRE</th><th style="width:25%">CHARACTER</th><th>WHAT HOME FEELS LIKE</th></tr></thead><tbody>';
        d.rows.forEach(function(r, i) {
            html += '<tr><td><div class="d-flex gap-1 align-items-center">' +
                '<input type="color" class="form-control form-control-color form-control-sm fnh-cct-color" data-grid="' + gridId + '" data-r="' + i + '" value="' + r.centre_color + '" style="width:30px;height:30px;padding:1px">' +
                '<input type="text" class="form-control form-control-sm fnh-cct-name" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.centre) + '" placeholder="T" style="color:' + r.centre_color + ';font-weight:700">' +
                '</div></td><td><input type="text" class="form-control form-control-sm fnh-cct-char" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.character) + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm fnh-cct-home" data-grid="' + gridId + '" data-r="' + i + '" value="' + escapeHtml(r.what_home_feels_like) + '"></td></tr>';
        });
        html += '</tbody></table>';
        $('#grid_' + gridId).html(html);
        serializeCct(gridId);
    }

    function serializeCct(gridId) { var d = fnhData[gridId]; if (!d) return; $('#json_' + gridId).val(JSON.stringify(d)); }

    $(document).on('input', '.fnh-cct-name', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].centre = $(this).val(); serializeCct(g); } });
    $(document).on('input', '.fnh-cct-color', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].centre_color = $(this).val(); renderCct(g); } });
    $(document).on('input', '.fnh-cct-char', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].character = $(this).val(); serializeCct(g); } });
    $(document).on('input', '.fnh-cct-home', function() { var g=$(this).data('grid'), r=$(this).data('r'); if (fnhData[g]) { fnhData[g].rows[r].what_home_feels_like = $(this).val(); serializeCct(g); } });
    $(document).on('click', '.fnh-cct-add-row', function() { var g=$(this).data('grid'); if (fnhData[g]) { fnhData[g].rows.push({centre:'',centre_color:'#2457E6',character:'',what_home_feels_like:''}); renderCct(g); } });
    $(document).on('click', '.fnh-cct-del-row', function() { var g=$(this).data('grid'); if (fnhData[g] && fnhData[g].rows.length > 1) { fnhData[g].rows.pop(); renderCct(g); } });

    // -- functional_flow --------------------------------------------------
    function initFf(gridId, data) {
        fnhData[gridId] = {
            title: data.title || '',
            steps: Array.isArray(data.steps) && data.steps.length > 0
                ? data.steps.map(function(s) { return {label: s.label||'', color: s.color||'#94A3B8'}; })
                : [{label:'T#', color:'#DC2626'}]
        };
        renderFf(gridId);
    }

    function renderFf(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        var html = '';
        d.steps.forEach(function(s, i) {
            if (i > 0) html += '<span class="text-muted fw-bold" style="font-size:18px">›</span>';
            html += '<div class="d-flex gap-1 align-items-center border rounded px-2 py-1">' +
                '<input type="color" class="form-control form-control-color form-control-sm fnh-ff-color" data-grid="' + gridId + '" data-i="' + i + '" value="' + s.color + '" style="width:24px;height:24px;padding:0;border:0">' +
                '<input type="text" class="form-control form-control-sm fnh-ff-label" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(s.label) + '" placeholder="T#" style="width:60px;color:' + s.color + ';font-weight:700"></div>';
        });
        $('#grid_' + gridId).html(html);
        serializeFf(gridId);
    }

    function serializeFf(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        d.title = $('.fnh-ff-title[data-grid="' + gridId + '"]').val() || d.title;
        $('#json_' + gridId).val(JSON.stringify(d));
    }

    $(document).on('input', '.fnh-ff-label', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].steps[i].label = $(this).val(); serializeFf(g); } });
    $(document).on('input', '.fnh-ff-color', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].steps[i].color = $(this).val(); renderFf(g); } });
    $(document).on('input', '.fnh-ff-title', function() { var g=$(this).data('grid'); if (fnhData[g]) serializeFf(g); });
    $(document).on('click', '.fnh-ff-add-step', function() { var g=$(this).data('grid'); if (fnhData[g]) { fnhData[g].steps.push({label:'',color:'#94A3B8'}); renderFf(g); } });
    $(document).on('click', '.fnh-ff-del-step', function() { var g=$(this).data('grid'); if (fnhData[g] && fnhData[g].steps.length > 1) { fnhData[g].steps.pop(); renderFf(g); } });

    // -- exploring_section ------------------------------------------------
    function initEs(gridId, data) {
        fnhData[gridId] = {
            title: data.title || '',
            subtitle: data.subtitle || '',
            formulas: Array.isArray(data.formulas) && data.formulas.length > 0
                ? data.formulas.map(function(f) { return {label: f.label||'', formula: f.formula||'', color: f.color||'#2457E6'}; })
                : [{label:'T', formula:'', color:'#2457E6'}]
        };
        renderEs(gridId);
    }

    function renderEs(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        var html = '';
        d.formulas.forEach(function(f, i) {
            html += '<div class="d-flex gap-2 align-items-center mb-2 border rounded p-2">' +
                '<input type="color" class="form-control form-control-color form-control-sm fnh-es-color" data-grid="' + gridId + '" data-i="' + i + '" value="' + f.color + '" style="width:30px;height:30px;padding:1px">' +
                '<input type="text" class="form-control form-control-sm fnh-es-label" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(f.label) + '" placeholder="T" style="width:50px;font-weight:700;color:' + f.color + '">' +
                '<span class="text-muted">:</span>' +
                '<input type="text" class="form-control form-control-sm fnh-es-formula" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(f.formula) + '" placeholder="T->S->D->(open)"></div>';
        });
        $('#grid_' + gridId).html(html);
        serializeEs(gridId);
    }

    function serializeEs(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        d.title = $('.fnh-es-title[data-grid="' + gridId + '"]').val() || d.title;
        d.subtitle = $('.fnh-es-subtitle[data-grid="' + gridId + '"]').val() || d.subtitle;
        $('#json_' + gridId).val(JSON.stringify(d));
    }

    $(document).on('input', '.fnh-es-label', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].formulas[i].label = $(this).val(); serializeEs(g); } });
    $(document).on('input', '.fnh-es-formula', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].formulas[i].formula = $(this).val(); serializeEs(g); } });
    $(document).on('input', '.fnh-es-color', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].formulas[i].color = $(this).val(); renderEs(g); } });
    $(document).on('input', '.fnh-es-title, .fnh-es-subtitle', function() { var g=$(this).data('grid'); if (fnhData[g]) serializeEs(g); });
    $(document).on('click', '.fnh-es-add', function() { var g=$(this).data('grid'); if (fnhData[g]) { fnhData[g].formulas.push({label:'',formula:'',color:'#2457E6'}); renderEs(g); } });
    $(document).on('click', '.fnh-es-del', function() { var g=$(this).data('grid'); if (fnhData[g] && fnhData[g].formulas.length > 1) { fnhData[g].formulas.pop(); renderEs(g); } });

    // -- centre_identity_card ---------------------------------------------
    function initCic(gridId, data) {
        fnhData[gridId] = {
            centres: Array.isArray(data.centres) && data.centres.length > 0
                ? data.centres.map(function(c) { return {centre: c.centre||'', character: c.character||'', description: c.description||'', colour: c.colour||'#2457E6'}; })
                : [{centre:'', character:'', description:'', colour:'#2457E6'}]
        };
        renderCic(gridId);
    }

    function renderCic(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        var html = '<div class="row g-2">';
        d.centres.forEach(function(c, i) {
            html += '<div class="col-md-6"><div class="border rounded p-2 bg-white">' +
                '<div class="d-flex gap-1 align-items-center mb-1">' +
                '<input type="color" class="form-control form-control-color form-control-sm fnh-cic-color" data-grid="' + gridId + '" data-i="' + i + '" value="' + c.colour + '" style="width:28px;height:28px;padding:1px">' +
                '<input type="text" class="form-control form-control-sm fnh-cic-centre" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(c.centre) + '" placeholder="T" style="font-weight:700;color:' + c.colour + '">' +
                '</div><input type="text" class="form-control form-control-sm mb-1 fnh-cic-char" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(c.character) + '" placeholder="Deep, grounded">' +
                '<input type="text" class="form-control form-control-sm fnh-cic-desc" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(c.description) + '" placeholder="Resting in depth"></div></div>';
        });
        html += '</div>';
        $('#grid_' + gridId).html(html);
        serializeCic(gridId);
    }

    function serializeCic(gridId) { var d = fnhData[gridId]; if (!d) return; $('#json_' + gridId).val(JSON.stringify(d)); }

    $(document).on('input', '.fnh-cic-centre', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].centres[i].centre = $(this).val(); serializeCic(g); } });
    $(document).on('input', '.fnh-cic-char', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].centres[i].character = $(this).val(); serializeCic(g); } });
    $(document).on('input', '.fnh-cic-desc', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].centres[i].description = $(this).val(); serializeCic(g); } });
    $(document).on('input', '.fnh-cic-color', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].centres[i].colour = $(this).val(); renderCic(g); } });
    $(document).on('click', '.fnh-cic-add', function() { var g=$(this).data('grid'); if (fnhData[g]) { fnhData[g].centres.push({centre:'',character:'',description:'',colour:'#2457E6'}); renderCic(g); } });
    $(document).on('click', '.fnh-cic-del', function() { var g=$(this).data('grid'); if (fnhData[g] && fnhData[g].centres.length > 1) { fnhData[g].centres.pop(); renderCic(g); } });

    // -- form_identity_card -----------------------------------------------
    function initFic(gridId, data) {
        fnhData[gridId] = {
            cards: Array.isArray(data.cards) && data.cards.length > 0
                ? data.cards.map(function(c) { return {label: c.label||'', character: c.character||'', description: c.description||'', colour: c.colour||'#2457E6'}; })
                : [{label:'', character:'', description:'', colour:'#2457E6'}]
        };
        renderFic(gridId);
    }

    function renderFic(gridId) {
        var d = fnhData[gridId]; if (!d) return;
        var html = '<div class="row g-2">';
        d.cards.forEach(function(c, i) {
            html += '<div class="col-md-6"><div class="border rounded p-2 bg-white">' +
                '<div class="d-flex gap-1 align-items-center mb-1">' +
                '<input type="color" class="form-control form-control-color form-control-sm fnh-fic-color" data-grid="' + gridId + '" data-i="' + i + '" value="' + c.colour + '" style="width:28px;height:28px;padding:1px">' +
                '<input type="text" class="form-control form-control-sm fnh-fic-label" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(c.label) + '" placeholder="T Form (Minor)" style="font-weight:700;color:' + c.colour + '">' +
                '</div><input type="text" class="form-control form-control-sm mb-1 fnh-fic-char" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(c.character) + '" placeholder="Darker, introspective">' +
                '<input type="text" class="form-control form-control-sm fnh-fic-desc" data-grid="' + gridId + '" data-i="' + i + '" value="' + escapeHtml(c.description) + '" placeholder="Tension is closer to home"></div></div>';
        });
        html += '</div>';
        $('#grid_' + gridId).html(html);
        serializeFic(gridId);
    }

    function serializeFic(gridId) { var d = fnhData[gridId]; if (!d) return; $('#json_' + gridId).val(JSON.stringify(d)); }

    $(document).on('input', '.fnh-fic-label', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].cards[i].label = $(this).val(); serializeFic(g); } });
    $(document).on('input', '.fnh-fic-char', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].cards[i].character = $(this).val(); serializeFic(g); } });
    $(document).on('input', '.fnh-fic-desc', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].cards[i].description = $(this).val(); serializeFic(g); } });
    $(document).on('input', '.fnh-fic-color', function() { var g=$(this).data('grid'), i=$(this).data('i'); if (fnhData[g]) { fnhData[g].cards[i].colour = $(this).val(); renderFic(g); } });
    $(document).on('click', '.fnh-fic-add', function() { var g=$(this).data('grid'); if (fnhData[g] && fnhData[g].cards.length < 2) { fnhData[g].cards.push({label:'',character:'',description:'',colour:'#DC2626'}); renderFic(g); } });
    $(document).on('click', '.fnh-fic-del', function() { var g=$(this).data('grid'); if (fnhData[g] && fnhData[g].cards.length > 1) { fnhData[g].cards.pop(); renderFic(g); } });

    // -- FNH Grid Initializer ---------------------------------------------
    function initFnhGrid(gridId, info) {
        switch(info.type) {
            case 'template_form_table': initTft(gridId, info.data); break;
            case 'movement_quality_table': initMqt(gridId, info.data); break;
            case 'centre_character_table': initCct(gridId, info.data); break;
            case 'functional_flow': initFf(gridId, info.data); break;
            case 'exploring_section': initEs(gridId, info.data); break;
            case 'centre_identity_card': initCic(gridId, info.data); break;
            case 'form_identity_card': initFic(gridId, info.data); break;
        }
    }

    // --- Initial filter state --------------------------------------------
    filterGroupsByCategory();
    var initialCat = $('#category').val();
    if (initialCat === 'case_study' || initialCat === 'fnh_architecture') {
        $('#groupField').show();
    } else {
        $('#groupField').hide();
    }

    // -- Form submit handler: guarantee all active grids serialize prior to POST --
    $('#learningForm').on('submit', function() {
        reindexAll();
        if (window.tableGrids) {
            Object.keys(window.tableGrids).forEach(function(tableId) {
                if (typeof serializeTableGrid === 'function') {
                    serializeTableGrid(tableId);
                }
            });
        }
        if (window.beatGrids) {
            Object.keys(window.beatGrids).forEach(function(gridId) {
                if (typeof serializeBeatGrid === 'function') {
                    serializeBeatGrid(gridId);
                }
            });
        }
        if (window.fnhData) {
            Object.keys(window.fnhData).forEach(function(gridId) {
                if (typeof serializeTft === 'function') serializeTft(gridId);
                if (typeof serializeMqt === 'function') serializeMqt(gridId);
                if (typeof serializeCct === 'function') serializeCct(gridId);
                if (typeof serializeFf === 'function') serializeFf(gridId);
                if (typeof serializeEs === 'function') serializeEs(gridId);
                if (typeof serializeCic === 'function') serializeCic(gridId);
                if (typeof serializeFic === 'function') serializeFic(gridId);
            });
        }
    });

    console.log('✅ Learning form initialized and fully ready!');

}); // ← End $(document).ready()
</script>
@endpush
