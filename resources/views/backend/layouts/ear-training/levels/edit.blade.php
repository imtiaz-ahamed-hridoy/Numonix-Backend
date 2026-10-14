@extends('backend.app')
@section('title', 'Edit Level')
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Edit Level (Engine Config)</h1></div>
</div>
<form action="{{ route('admin.ear-training.levels.update', $level->id) }}" method="POST">
    @csrf

    <!-- CORE IDENTITY -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Core Identity</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Level Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $level->title }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Display Order (Level Number)</label>
                    <input type="number" name="level_number" class="form-control" value="{{ $level->level_number }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="2">{{ $level->description }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_premium" value="1" {{ $level->is_premium ? 'checked' : '' }}>
                        <label class="form-check-label">Is Premium (Locked)</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ $level->is_active ? 'selected' : '' }}>Published (Active)</option>
                        <option value="0" {{ !$level->is_active ? 'selected' : '' }}>Draft (Inactive)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Guest Access Override</label>
                    <select name="guest_access_override" class="form-select">
                        <option value="Use Level Rule" {{ ($level->guest_access_override ?? 'Use Level Rule') === 'Use Level Rule' ? 'selected' : '' }}>🟢 Use Level Rule (Default)</option>
                        <option value="Unlocked" {{ $level->guest_access_override === 'Unlocked' ? 'selected' : '' }}>🔵 Always Unlocked</option>
                        <option value="Locked" {{ $level->guest_access_override === 'Locked' ? 'selected' : '' }}>🔴 Always Locked</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ENGINE GENERATION PARAMETERS -->
    <div class="card mb-4" style="border: 1px solid #fd7e14;">
        <div class="card-header text-white" style="background-color: #fd7e14;">
            <h5 class="card-title mb-0"><i class="fa fa-cog me-2"></i> Engine Generation Parameters</h5>
        </div>
        <div class="card-body bg-light">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Exercise Type</label>
                    <select name="exercise_type" class="form-select">
                        <option value="identify_function" {{ $level->exercise_type === 'identify_function' ? 'selected' : '' }}>Identify Function (FNH)</option>
                        <option value="identify_progression" {{ $level->exercise_type === 'identify_progression' ? 'selected' : '' }}>Identify Progression</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Number of Exercises</label>
                    <input type="number" name="exercise_count" class="form-control" value="{{ $level->exercise_count }}">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label d-block">Function Patterns (Beat Grids)</label>
                <div id="function-patterns-container">
                    <!-- Rows will be injected here by JS -->
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-pattern-btn" style="background-color: #6c757d; border-color: #6c757d;">
                    + Add Pattern
                </button>
                <small class="text-muted d-block mt-2">Enter the function token for each beat. Leave empty to hold the previous chord (rendered as <code>null</code>).</small>
            </div>

            <h6 class="fw-bold mb-3">Musical Configuration</h6>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tempo (BPM)</label>
                    <input type="number" name="tempo_bpm" class="form-control" value="{{ $level->tempo_bpm }}">
                    @error('tempo_bpm') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">MIDI Program (0-127)</label>
                    <input type="number" name="midi_program" class="form-control" value="{{ $level->midi_program }}">
                    @error('midi_program') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bar Length (Beats)</label>
                    <input type="number" name="bar_length" class="form-control" value="{{ $level->bar_length }}">
                    @error('bar_length') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Repetitions</label>
                    <input type="number" name="repetitions" class="form-control" value="{{ $level->repetitions }}">
                    @error('repetitions') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Chord Voicing</label>
                    <select name="chord_voicing" class="form-select">
                        <option value="block" {{ $level->chord_voicing === 'block' ? 'selected' : '' }}>Block</option>
                        <option value="arpeggiated" {{ $level->chord_voicing === 'arpeggiated' ? 'selected' : '' }}>Arpeggiated</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chord Size (Notes)</label>
                    <select name="chord_size" class="form-select">
                        <option value="3" {{ $level->chord_size === '3' ? 'selected' : '' }}>3 (Triad)</option>
                        <option value="4" {{ $level->chord_size === '4' ? 'selected' : '' }}>4 (Tetrad)</option>
                        <option value="5" {{ $level->chord_size === '5' ? 'selected' : '' }}>5 (Pentad)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chord Purity</label>
                    <select name="chord_purity" class="form-select">
                        <option value="pure" {{ $level->chord_purity === 'pure' ? 'selected' : '' }}>Pure</option>
                        <option value="mixed" {{ $level->chord_purity === 'mixed' ? 'selected' : '' }}>Mixed</option>
                        <option value="any" {{ $level->chord_purity === 'any' ? 'selected' : '' }}>Any</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chord Regularity</label>
                    <select name="chord_regularity" class="form-select">
                        <option value="regular" {{ $level->chord_regularity === 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="irregular" {{ $level->chord_regularity === 'irregular' ? 'selected' : '' }}>Irregular</option>
                        <option value="any" {{ $level->chord_regularity === 'any' ? 'selected' : '' }}>Any</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Answer Display</label>
                    <select name="answer_display" class="form-select">
                        <option value="leadership_only" {{ $level->answer_display === 'leadership_only' ? 'selected' : '' }}>Leadership Only</option>
                        <option value="with_influences" {{ $level->answer_display === 'with_influences' ? 'selected' : '' }}>With Influences</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="closes_on_following_one" value="1" {{ $level->closes_on_following_one ? 'checked' : '' }}>
                        <label class="form-check-label">Closes on following One</label>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3">Overrides</h6>
            <div class="mb-2">
                <label class="form-label">Question Text Override (Optional)</label>
                <input type="text" name="question_text_override" class="form-control" value="{{ $level->question_text_override }}">
            </div>
        </div>
    </div>

    <!-- LEVEL COMPLETE SCREEN -->
    <div class="card mb-4" style="border: 1px solid #c9cdd2;">
        <div class="card-header bg-light text-dark">
            <h5 class="card-title mb-0"><i class="fa fa-flag-checkered me-2" style="color:#6f42c1;"></i> Level Complete Screen</h5>
        </div>
        <div class="card-body bg-light">
            <div class="mb-3">
                <label class="form-label">Completion Heading</label>
                <input type="text" name="completion_heading" class="form-control" value="{{ $level->completion_heading }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Completion Body (Paragraphs)</label>
                <textarea name="completion_body" class="form-control" rows="3">{{ $level->completion_body }}</textarea>
                <small class="text-muted">Use new lines for paragraphs.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Completion Footer (Quote)</label>
                <input type="text" name="completion_footer" class="form-control" value="{{ $level->completion_footer }}">
            </div>
        </div>
    </div>

    <div class="d-flex mb-5">
        <button type="submit" class="btn btn-warning me-3" style="background-color: #fd7e14; border-color: #fd7e14; color: white;">Save Configuration</button>
        <a href="{{ route('admin.ear-training.levels.index', $stage->id) }}" class="btn btn-danger" style="background-color: #e83e8c; border-color: #e83e8c;">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('function-patterns-container');
    const addBtn = document.getElementById('add-pattern-btn');
    const barLengthInput = document.querySelector('input[name="bar_length"]');
    
    // Initial data from DB
    const initialData = @json($level->function_patterns ?? []);
    let patternIndex = 0;

    function renderRow(tokens) {
        let barLength = parseInt(barLengthInput.value) || 4;
        
        // If we have existing tokens, use that length or barLength (whichever is greater for safety)
        if (tokens && tokens.length > 0) {
            barLength = Math.max(barLength, tokens.length);
        }

        const rowDiv = document.createElement('div');
        rowDiv.className = 'pattern-row d-flex align-items-center mb-2';
        
        for (let i = 0; i < barLength; i++) {
            const input = document.createElement('input');
            input.type = 'text';
            input.name = `function_patterns[${patternIndex}][]`;
            input.className = 'form-control me-2';
            input.placeholder = `Beat ${i+1}`;
            input.style.width = '100px';
            
            if (tokens && i < tokens.length) {
                // If it's null in DB, we leave the input blank
                input.value = tokens[i] === null ? '' : tokens[i];
            }
            
            rowDiv.appendChild(input);
        }

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger btn-sm';
        removeBtn.innerHTML = 'Remove';
        removeBtn.onclick = function() {
            rowDiv.remove();
        };

        rowDiv.appendChild(removeBtn);
        container.appendChild(rowDiv);
        patternIndex++;
    }

    // Initialize with existing data
    if (Array.isArray(initialData) && initialData.length > 0) {
        initialData.forEach(pattern => {
            if (Array.isArray(pattern)) {
                renderRow(pattern);
            }
        });
    } else {
        // Add one empty row by default
        renderRow(null);
    }

    // Add new pattern
    addBtn.addEventListener('click', function() {
        renderRow(null);
    });

    // Optional: Re-render rows if bar_length changes
    barLengthInput.addEventListener('change', function() {
        // We could dynamically adjust inputs per row here, but let's keep it simple.
        // It will take effect on the next "Add Pattern" click, or after a page reload.
    });
});
</script>
@endsection