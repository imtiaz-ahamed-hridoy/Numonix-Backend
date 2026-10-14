@extends('backend.app')

@section('title', 'Manage Questions')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Questions for: {{ $quiz->learning?->title ?? 'Quiz' }}</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('quiz.index') }}">Quizzes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Questions</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom" style="display: flex; justify-content: space-between;">
                    <h3 class="card-title">Questions List</h3>
                    <a href="{{ route('quiz.questions.create', $quiz->id) }}" class="btn btn-primary">Add New Question</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Question</th>
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
                                        <td style="white-space: normal;">
                                            @if ($question->image_url)
                                                <div class="d-flex align-items-center mb-1">
                                                    <img src="{{ $question->image_url }}" alt="Question Image" style="height: 36px; width: 36px; object-fit: cover; border-radius: 4px; margin-right: 8px; border: 1px solid #e2e8f0;">
                                                    <span class="badge bg-info-transparent text-info">{{ ucfirst($question->image_size ?? 'medium') }}</span>
                                                </div>
                                            @endif
                                            {{ $question->question_text }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('quiz.questions.edit', [$quiz->id, $question->id]) }}"
                                                    class="btn btn-primary text-white"><i class="fe fe-edit"></i></a>
                                                <button onclick="deleteQuestion({{ $question->id }})"
                                                    class="btn btn-danger text-white"><i class="fe fe-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No questions added yet.</td>
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
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: quizQuestionDestroyUrlTemplate.replace('__ID__', id),
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(err) {
                            toastr.error('Error deleting question.');
                        }
                    });
                }
            });
        }
    </script>
@endpush
