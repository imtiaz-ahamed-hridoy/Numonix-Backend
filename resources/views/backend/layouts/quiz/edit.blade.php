@extends('backend.app')

@section('title', 'Edit Quiz')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Quiz</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('quiz.index') }}">Quizzes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('quiz.update', $quiz->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="form-group">
                            <label for="learning_id">Learning</label>
                            <select name="learning_id" id="learning_id"
                                class="form-control @error('learning_id') is-invalid @enderror">
                                <option value="">Select Learning</option>
                                @foreach ($learnings as $learning)
                                    <option value="{{ $learning->id }}" @selected(old('learning_id', $quiz->learning_id) == $learning->id)>
                                        {{ $learning->title }} ({{ ucwords(str_replace('_', ' ', $learning->category)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('learning_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="published" {{ $quiz->status == 'published' ? 'selected' : '' }}>Published
                                </option>
                                <option value="draft" {{ $quiz->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">Update Quiz Details</button>
                            <a href="{{ route('quiz.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <hr class="my-5">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="card-title mb-0">Quiz Questions</h3>
                        <a href="{{ route('quiz.questions.create', $quiz->id) }}" class="btn btn-sm btn-success">
                            <i class="fe fe-plus"></i> Add New Question
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th class="wd-5p">#</th>
                                    <th>Type</th>
                                    <th>Question Text</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quiz->questions as $question)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if ($question->type === 'mcq')
                                                <span class="badge bg-primary">MCQ</span>

                                            @else
                                                <span class="badge bg-secondary">Tap Order</span>
                                            @endif
                                        </td>
                                        <td style="white-space: normal; max-width: 400px;">
                                            {{ Str::limit($question->question_text, 100) }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('quiz.questions.edit', [$quiz->id, $question->id]) }}"
                                                    class="btn btn-primary text-white" title="Edit Content">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                <button onclick="deleteQuestion({{ $question->id }})"
                                                    class="btn btn-danger text-white" title="Delete">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-5 text-muted">
                                            No questions added to this quiz yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const quizQuestionDestroyUrlTemplate = @json(route('quiz.questions.destroy', ['quizId' => $quiz->id, 'id' => '__ID__']));

        function deleteQuestion(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Delete this question?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: quizQuestionDestroyUrlTemplate.replace('__ID__', id),
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            location.reload();
                        },
                        error: function (err) {
                            toastr.error('Error deleting question.');
                        }
                    });
                }
            });
        }
    </script>
@endpush
