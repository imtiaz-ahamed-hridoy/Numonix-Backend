@extends('backend.app')

@section('title', 'Edit Ear Training Question')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Question: {{ ucfirst($quiz->level->level) }} - Quiz</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ear-training.questions.index', $quiz->id) }}">Questions</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('ear-training.questions.update', [$quiz->id, $question->id]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="question_text">Question Text</label>
                                    <textarea name="question_text" id="question_text" class="form-control" rows="2"
                                        required>{{ old('question_text', $question->question_text) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="audio">Audio File (Leave empty to keep current)</label>
                                    <input type="file" name="audio" id="audio" class="form-control" accept="audio/*">
                                    @if($question->audio_url)
                                        <div class="mt-2">
                                            <audio controls style="height: 30px;">
                                                <source src="{{ $question->audio_url }}" type="audio/mpeg">
                                            </audio>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tonic_root_hint">Tonic Root Hint Title</label>
                                    <input type="text" name="tonic_root_hint" id="tonic_root_hint"
                                        class="form-control" value="{{ old('tonic_root_hint', $question->tonic_root_hint) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tonic_root_hint_description">Tonic Root Hint Description</label>
                                    <textarea name="tonic_root_hint_description" id="tonic_root_hint_description"
                                        class="form-control" rows="3">{{ old('tonic_root_hint_description', $question->tonic_root_hint_description) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correct_answer_description">Correct Answer Description</label>
                                    <textarea name="correct_answer_description" id="correct_answer_description"
                                        class="form-control" rows="3">{{ old('correct_answer_description', $question->correct_answer_description) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div id="mcq_section">
                            <h4>MCQ Options</h4>
                            <div id="mcq_options_container">
                                @foreach($question->options as $index => $option)
                                    <div class="input-group mb-2">
                                        <div class="input-group-text">
                                            <input type="radio" name="mcq_correct" value="{{ $index }}" {{ $question->correct_answer == $index ? 'checked' : '' }}>
                                        </div>
                                        <input type="text" name="mcq_options[]" class="form-control" value="{{ $option }}"
                                            required>
                                        @if($index > 1)
                                            <button type="button" class="btn btn-danger remove_row"><i
                                                    class="fe fe-trash"></i></button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-info" id="add_mcq_option">Add Option</button>
                        </div>

                        <div class="form-group mt-5">
                            <button type="submit" class="btn btn-primary btn-lg px-5">Update Question</button>
                            <a href="{{ route('ear-training.questions.index', $quiz->id) }}"
                                class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#add_mcq_option').click(function () {
                let index = $('#mcq_options_container .input-group').length;
                $('#mcq_options_container').append(`
                    <div class="input-group mb-2">
                        <div class="input-group-text">
                            <input type="radio" name="mcq_correct" value="${index}">
                        </div>
                        <input type="text" name="mcq_options[]" class="form-control" placeholder="Option ${index + 1}" required>
                        <button type="button" class="btn btn-danger remove_row"><i class="fe fe-trash"></i></button>
                    </div>
                `);
            });

            $(document).on('click', '.remove_row', function () {
                $(this).closest('.input-group').remove();
                $('#mcq_options_container input[type="radio"]').each(function (index) {
                    $(this).val(index);
                });
            });
        });
    </script>
@endpush