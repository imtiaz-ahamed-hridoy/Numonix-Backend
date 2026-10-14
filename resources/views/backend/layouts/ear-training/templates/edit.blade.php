@extends('backend.app')

@section('title', 'Edit Template')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Template</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.ear-training.templates.index') }}">Templates</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Edit Template #{{ $template->id }}</h3>
                </div>
                <form action="{{ route('admin.ear-training.templates.update', $template->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="transition" {{ $template->type == 'transition' ? 'selected' : '' }}>Transition</option>
                                <option value="pattern" {{ $template->type == 'pattern' ? 'selected' : '' }}>Pattern</option>
                                <option value="contrast" {{ $template->type == 'contrast' ? 'selected' : '' }}>Contrast</option>
                                <option value="hint" {{ $template->type == 'hint' ? 'selected' : '' }}>Hint</option>
                                <option value="prompt" {{ $template->type == 'prompt' ? 'selected' : '' }}>Prompt</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>From Function</label>
                                <input type="text" name="from_function" class="form-control" value="{{ old('from_function', $template->from_function) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>To Function</label>
                                <input type="text" name="to_function" class="form-control" value="{{ old('to_function', $template->to_function) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $template->title) }}">
                        </div>

                        <div class="form-group">
                            <label>Phrase</label>
                            <textarea name="phrase" class="form-control" rows="3">{{ old('phrase', $template->phrase) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Pattern</label>
                            <input type="text" name="pattern" class="form-control" value="{{ old('pattern', $template->pattern) }}">
                        </div>

                        <div class="form-group">
                            <label>Body</label>
                            <textarea name="body" class="form-control" rows="4">{{ old('body', $template->body) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Hint Text</label>
                                <input type="text" name="hint_text" class="form-control" value="{{ old('hint_text', $template->hint_text) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Display Value</label>
                                <input type="text" name="display_value" class="form-control" value="{{ old('display_value', $template->display_value) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Exercise Type</label>
                                <input type="text" name="exercise_type" class="form-control" value="{{ old('exercise_type', $template->exercise_type) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Prompt Text</label>
                                <input type="text" name="prompt_text" class="form-control" value="{{ old('prompt_text', $template->prompt_text) }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success">Update Template</button>
                        <a href="{{ route('admin.ear-training.templates.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
