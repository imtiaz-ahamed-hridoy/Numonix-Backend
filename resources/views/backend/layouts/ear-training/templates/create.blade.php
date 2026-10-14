@extends('backend.app')

@section('title', 'Create Template')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Create Template</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.ear-training.templates.index') }}">Templates</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">New Template</h3>
                </div>
                <form action="{{ route('admin.ear-training.templates.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="transition">Transition</option>
                                <option value="pattern">Pattern</option>
                                <option value="contrast">Contrast</option>
                                <option value="hint">Hint</option>
                                <option value="prompt">Prompt</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>From Function</label>
                                <input type="text" name="from_function" class="form-control" placeholder="e.g. T">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>To Function</label>
                                <input type="text" name="to_function" class="form-control" placeholder="e.g. D">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Phrase</label>
                            <textarea name="phrase" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Pattern</label>
                            <input type="text" name="pattern" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Body</label>
                            <textarea name="body" class="form-control" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Hint Text</label>
                                <input type="text" name="hint_text" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Display Value</label>
                                <input type="text" name="display_value" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Exercise Type</label>
                                <input type="text" name="exercise_type" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Prompt Text</label>
                                <input type="text" name="prompt_text" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success">Save Template</button>
                        <a href="{{ route('admin.ear-training.templates.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
