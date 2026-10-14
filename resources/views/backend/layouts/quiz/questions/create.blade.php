@extends('backend.app')

@section('title', 'Add Question')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Add Question to: {{ $quiz->learning?->title ?? 'Quiz' }}</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('quiz.questions.index', $quiz->id) }}">Questions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('quiz.questions.store', $quiz->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="question_text">Question Text</label>
                                    <textarea name="question_text" id="question_text"
                                        class="form-control @error('question_text') is-invalid @enderror"
                                        rows="2">{{ old('question_text') }}</textarea>
                                    @error('question_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type">Question Type</label>
                                    <select name="type" id="type_selector" class="form-control">
                                        <option value="mcq">MCQ</option>

                                        <option value="tap_order">Tap Order</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">Question Image <small class="text-muted">(Optional)</small></label>
                                    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image_size">Image Display Size</label>
                                    <select name="image_size" id="image_size" class="form-control">
                                        <option value="medium" {{ old('image_size') === 'medium' ? 'selected' : '' }}>Medium (~200px)</option>
                                        <option value="small" {{ old('image_size') === 'small' ? 'selected' : '' }}>Small (~120px)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- MCQ Section --}}
                        <div id="mcq_section" class="type_section">
                            <h4>MCQ Options</h4>
                            <div id="mcq_options_container">
                                <div class="input-group mb-2">
                                    <div class="input-group-text">
                                        <input type="radio" name="mcq_correct" value="0" checked>
                                    </div>
                                    <input type="text" name="mcq_options[]" class="form-control" placeholder="Option 1"
                                        required>
                                </div>
                                <div class="input-group mb-2">
                                    <div class="input-group-text">
                                        <input type="radio" name="mcq_correct" value="1">
                                    </div>
                                    <input type="text" name="mcq_options[]" class="form-control" placeholder="Option 2"
                                        required>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-info" id="add_mcq_option">Add Option</button>
                        </div>



                        {{-- Tap Order Section --}}
                        <div id="tap_order_section" class="type_section" style="display:none;">
                            <div class="alert alert-info py-2">
                                <i class="fe fe-info me-2"></i> <strong>Simple Guide:</strong> List the items students need
                                to tap, and assign a number to show the correct order (1 first, 2 second, etc.).
                            </div>

                            <h5 class="mb-3 text-primary mt-4">Items and Their Order</h5>
                            <div id="tap_items_container">
                                <div class="row align-items-center mb-2">
                                    <div class="col-8">
                                        <input type="text" name="tap_items[]" class="form-control" placeholder="e.g. Note C"
                                            required>
                                    </div>
                                    <div class="col-3">
                                        <input type="number" name="tap_order[]" class="form-control" placeholder="Order"
                                            value="1" min="1" required>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-2">
                                    <div class="col-8">
                                        <input type="text" name="tap_items[]" class="form-control" placeholder="e.g. Note D"
                                            required>
                                    </div>
                                    <div class="col-3">
                                        <input type="number" name="tap_order[]" class="form-control" placeholder="Order"
                                            value="2" min="1" required>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add_tap_item">
                                <i class="fe fe-plus"></i> Add Item
                            </button>
                        </div>

                        <hr>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">Save Question</button>
                            <a href="{{ route('quiz.questions.index', $quiz->id) }}"
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
            // Section Switcher
            $('#type_selector').on('change', function () {
                $('.type_section').hide();
                $('.type_section').find('input, textarea, select').prop('disabled', true);

                let selectedSection = $('#' + $(this).val() + '_section');
                selectedSection.show();
                selectedSection.find('input, textarea, select').prop('disabled', false);
            });
            $('#type_selector').trigger('change');

            // --- MCQ Logic ---
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



            // --- Tap Order Logic ---
            $('#add_tap_item').click(function () {
                $('#tap_items_container').append(`
                        <div class="row align-items-center mb-2 tap-item">
                            <div class="col-8">
                                <input type="text" name="tap_items[]" class="form-control" placeholder="Item" required>
                            </div>
                            <div class="col-3">
                                <input type="number" name="tap_order[]" class="form-control" placeholder="Order" min="1" required>
                            </div>
                            <div class="col-1">
                                <button type="button" class="btn btn-danger btn-sm remove_tap_row"><i class="fe fe-trash"></i></button>
                            </div>
                        </div>
                    `);
            });

            // Global Removal Helpers
            $(document).on('click', '.remove_row', function () { $(this).closest('.input-group').remove(); });

            $(document).on('click', '.remove_tap_row', function () { $(this).closest('.tap-item').remove(); });




        });
    </script>
@endpush
