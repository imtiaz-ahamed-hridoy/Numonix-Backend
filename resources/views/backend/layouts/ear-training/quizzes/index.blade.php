@extends('backend.app')

@section('title', 'Levels (Quizzes)')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Levels for Stage: {{ ucfirst($level->level) }}</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ear-training.levels.index') }}">Stages</a></li>
                <li class="breadcrumb-item active" aria-current="page">Levels</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Levels</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuizModal">Add New Level</button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th># / Order</th>
                                    <th>Title</th>
                                    <th>Engine Config</th>
                                    <th>Premium</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($level->quizzes as $index => $quiz)
                                    <tr>
                                        <td>{{ $quiz->order }}</td>
                                        <td>
                                            <strong>{{ $quiz->title ?? 'Level ' . ($index + 1) }}</strong>
                                            @if($quiz->description)
                                                <p class="text-muted mb-0"><small>{{ Str::limit($quiz->description, 50) }}</small></p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $quiz->exercise_type === 'identify_progression' ? 'Progression' : 'Function' }}</span>
                                            <small class="d-block text-muted mt-1">{{ count((array)$quiz->function_patterns) }} patterns • {{ $quiz->exercise_count }} exercises</small>
                                        </td>
                                        <td>
                                            @if($quiz->is_premium)
                                                <span class="badge bg-danger"><i class="fa fa-lock"></i> Premium</span>
                                            @else
                                                <span class="badge bg-success"><i class="fa fa-unlock"></i> Foundation (Free)</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $quiz->status == 'published' ? 'success' : 'warning' }}">
                                                {{ ucfirst($quiz->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('ear-training.quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-primary">Edit Engine Config</a>
                                            <button onclick="deleteQuiz({{ $quiz->id }})" class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Quiz Modal -->
    <div class="modal fade" id="addQuizModal" tabindex="-1" aria-labelledby="addQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('ear-training.quizzes.store', $level->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addQuizModalLabel">Add New Level (Engine Config)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 form-group mb-3">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control" required placeholder="e.g. Level 1 -- T and S only">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="order">Display Order</label>
                                <input type="number" name="order" id="order" class="form-control" value="0" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="description">Description (Optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="2" placeholder="e.g. Two-function movements..."></textarea>
                        </div>

                        <hr>
                        <h6 class="fw-semibold text-muted text-uppercase mb-3">Engine Config</h6>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="exercise_type">Exercise Type</label>
                                <select name="exercise_type" id="exercise_type" class="form-control">
                                    <option value="identify_function">Identify Function (FNH)</option>
                                    <option value="identify_progression">Identify Progression</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="exercise_count">Number of Exercises</label>
                                <input type="number" name="exercise_count" id="exercise_count" class="form-control" value="10" min="1" max="100">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="function_patterns_text">Function Patterns <small class="text-muted">(One pattern per line, e.g. ["T", "S", "T"])</small></label>
                            <textarea name="function_patterns_text" id="function_patterns_text" class="form-control text-monospace" rows="4" placeholder='["T", "S", "T"]&#10;["T", "D", "T"]'></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="custom-control custom-checkbox mt-4">
                                    <input type="checkbox" class="custom-control-input" name="is_premium" value="1">
                                    <span class="custom-control-label">Is Premium (Locked)</span>
                                </label>
                            </div>
                        </div>
                        <p class="text-muted small mt-2">More engine parameters (Tempo, Chords, etc.) can be configured after creation by clicking Edit.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Level</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function deleteQuiz(id) {
            if (confirm('Are you sure you want to delete this level?')) {
                $.ajax({
                    url: "{{ route('ear-training.quizzes.destroy', ':id') }}".replace(':id', id),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        if (response['t-success']) {
                            toastr.success(response.message);
                            location.reload();
                        }
                    }
                });
            }
        }
    </script>
@endpush