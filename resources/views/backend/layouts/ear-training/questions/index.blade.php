@extends('backend.app')

@section('title', 'Quiz Questions')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Questions for {{ ucfirst($quiz->level->level) }} - Quiz</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ear-training.levels.index') }}">Levels</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('ear-training.quizzes.index', $quiz->ear_training_level_id) }}">Quizzes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Questions</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom" style="display: flex; justify-content: space-between;">
                    <h3 class="card-title">Questions List</h3>
                    <a href="{{ route('ear-training.questions.create', $quiz->id) }}" class="btn btn-primary">Add New
                        Question</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question Text</th>
                                    <th>Audio</th>
                                    <th>Hint</th>
                                    <th>Options</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quiz->questions as $index => $question)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $question->question_text }}</td>
                                        <td>
                                            @if($question->audio_url)
                                                <audio controls style="height: 30px;">
                                                    <source src="{{ $question->audio_url }}" type="audio/mpeg">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            @endif
                                        </td>
                                        <td>{{ $question->tonic_root_hint ?? 'N/A' }}</td>
                                        <td>{{ count($question->options) }} Options</td>
                                        <td>
                                            <a href="{{ route('ear-training.questions.edit', [$quiz->id, $question->id]) }}"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <button onclick="deleteQuestion({{ $question->id }})"
                                                class="btn btn-sm btn-danger">Delete</button>
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
@endsection

@push('scripts')
    <script>
        function deleteQuestion(id) {
            if (confirm('Are you sure you want to delete this question?')) {
                $.ajax({
                    url: "{{ route('ear-training.questions.destroy', [$quiz->id, ':id']) }}".replace(':id', id),
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