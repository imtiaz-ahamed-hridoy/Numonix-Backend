@extends('backend.app')

@section('title', 'Edit Ear Training Level')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Level (Engine Config)</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ear-training.quizzes.index', $quiz->ear_training_level_id) }}">Levels</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <form action="{{ route('ear-training.quizzes.update', $quiz->id) }}" method="POST">
                @csrf
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="card-title mb-0">Core Identity</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label for="title">Level Title</label>
                                <input type="text" name="title" id="title" class="form-control" value="{{ $quiz->title }}" required placeholder="e.g. Level 1 -- T and S only">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="order">Display Order</label>
                                <input type="number" name="order" id="order" class="form-control" value="{{ $quiz->order }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Description (Optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="2" placeholder="e.g. Two-function movements. Hear the contrast between home and departure.">{{ $quiz->description }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="custom-control custom-checkbox mt-4">
                                    <input type="checkbox" class="custom-control-input" name="is_premium" value="1" {{ $quiz->is_premium ? 'checked' : '' }}>
                                    <span class="custom-control-label">Is Premium (Locked)</span>
                                </label>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="published" {{ $quiz->status == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="draft" {{ $quiz->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0 text-white">⚙️ Engine Generation Parameters</h4>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="exercise_type">Exercise Type</label>
                                <select name="exercise_type" id="exercise_type" class="form-control">
                                    <option value="identify_function" {{ $quiz->exercise_type === 'identify_function' ? 'selected' : '' }}>Identify Function (FNH)</option>
                                    <option value="identify_progression" {{ $quiz->exercise_type === 'identify_progression' ? 'selected' : '' }}>Identify Progression</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="exercise_count">Number of Exercises</label>
                                <input type="number" name="exercise_count" id="exercise_count" class="form-control" value="{{ $quiz->exercise_count ?? 10 }}" min="1" max="100">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="function_patterns_text">Function Patterns <small class="text-muted">(One JSON pattern per line)</small></label>
                            <textarea name="function_patterns_text" id="function_patterns_text" class="form-control text-monospace bg-white" rows="6" placeholder='["T", "S", "T"]&#10;["T", "D", "T"]'>{{ $quiz->function_patterns ? implode("\n", array_map(function($p) { return is_string($p) ? $p : json_encode($p); }, (array)$quiz->function_patterns)) : '' }}</textarea>
                            <small class="text-muted">Enter patterns exactly as they will be parsed by the engine (e.g. `["T", "S", "T"]`).</small>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Musical Configuration</h6>

                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="tempo_bpm">Tempo (BPM)</label>
                                <input type="number" name="tempo_bpm" id="tempo_bpm" class="form-control" value="{{ $quiz->tempo_bpm ?? 80 }}" min="40" max="300">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="midi_program">MIDI Program (0-127)</label>
                                <input type="number" name="midi_program" id="midi_program" class="form-control" value="{{ $quiz->midi_program ?? 0 }}" min="0" max="127">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="bar_length">Bar Length (Beats)</label>
                                <input type="number" name="bar_length" id="bar_length" class="form-control" value="{{ $quiz->bar_length ?? 4 }}" min="2" max="16">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="repetitions">Repetitions</label>
                                <input type="number" name="repetitions" id="repetitions" class="form-control" value="{{ $quiz->repetitions ?? 1 }}" min="1" max="8">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="chord_voicing">Chord Voicing</label>
                                <select name="chord_voicing" id="chord_voicing" class="form-control">
                                    <option value="block" {{ $quiz->chord_voicing === 'block' ? 'selected' : '' }}>Block</option>
                                    <option value="arpeggiated" {{ $quiz->chord_voicing === 'arpeggiated' ? 'selected' : '' }}>Arpeggiated</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="chord_size">Chord Size (Notes)</label>
                                <select name="chord_size" id="chord_size" class="form-control">
                                    <option value="3" {{ $quiz->chord_size === '3' ? 'selected' : '' }}>3 (Triad)</option>
                                    <option value="4" {{ $quiz->chord_size === '4' ? 'selected' : '' }}>4</option>
                                    <option value="5" {{ $quiz->chord_size === '5' ? 'selected' : '' }}>5</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="chord_purity">Chord Purity</label>
                                <select name="chord_purity" id="chord_purity" class="form-control">
                                    <option value="pure" {{ $quiz->chord_purity === 'pure' ? 'selected' : '' }}>Pure</option>
                                    <option value="mixed" {{ $quiz->chord_purity === 'mixed' ? 'selected' : '' }}>Mixed</option>
                                    <option value="any" {{ $quiz->chord_purity === 'any' ? 'selected' : '' }}>Any</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="chord_regularity">Chord Regularity</label>
                                <select name="chord_regularity" id="chord_regularity" class="form-control">
                                    <option value="regular" {{ $quiz->chord_regularity === 'regular' ? 'selected' : '' }}>Regular</option>
                                    <option value="irregular" {{ $quiz->chord_regularity === 'irregular' ? 'selected' : '' }}>Irregular</option>
                                    <option value="any" {{ $quiz->chord_regularity === 'any' ? 'selected' : '' }}>Any</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="answer_display">Answer Display</label>
                                <select name="answer_display" id="answer_display" class="form-control">
                                    <option value="leadership_only" {{ $quiz->answer_display === 'leadership_only' ? 'selected' : '' }}>Leadership Only</option>
                                    <option value="with_influences" {{ $quiz->answer_display === 'with_influences' ? 'selected' : '' }}>With Influences</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="custom-control custom-checkbox mt-4">
                                    <input type="checkbox" class="custom-control-input" name="closes_on_following_one" value="1" {{ $quiz->closes_on_following_one ? 'checked' : '' }}>
                                    <span class="custom-control-label">Closes on following One</span>
                                </label>
                            </div>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Overrides</h6>
                        <div class="form-group mb-0">
                            <label for="question_text_override">Question Text Override (Optional)</label>
                            <input type="text" name="question_text_override" id="question_text_override" class="form-control bg-white" maxlength="255" value="{{ $quiz->question_text_override }}">
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="card-title mb-0">📱 Level Complete Screen</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="completion_heading">Completion Heading</label>
                            <input type="text" name="completion_heading" id="completion_heading" class="form-control" maxlength="255" value="{{ $quiz->completion_heading }}">
                        </div>
                        <div class="form-group">
                            <label for="completion_body">Completion Body (Paragraphs)</label>
                            <textarea name="completion_body" id="completion_body" class="form-control" rows="3">{{ $quiz->completion_body }}</textarea>
                            <small class="text-muted">Use new lines for paragraphs.</small>
                        </div>
                        <div class="form-group">
                            <label for="completion_footer">Completion Footer (Quote)</label>
                            <input type="text" name="completion_footer" id="completion_footer" class="form-control" maxlength="255" value="{{ $quiz->completion_footer }}">
                        </div>
                    </div>
                </div>

                <div class="mb-5 pb-5">
                    <button type="submit" class="btn btn-primary btn-lg px-5">Save Configuration</button>
                    <a href="{{ route('ear-training.quizzes.index', $quiz->ear_training_level_id) }}" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
