@extends('backend.app')

@section('title', 'Create Quiz')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Create Quiz</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('quiz.index') }}">Quizzes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('quiz.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="learning_id">Learning</label>
                            <select name="learning_id" id="learning_id"
                                class="form-control @error('learning_id') is-invalid @enderror">
                                <option value="">Select Learning</option>
                                @foreach ($learnings as $learning)
                                    <option value="{{ $learning->id }}" @selected(old('learning_id') == $learning->id)>
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
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">Create Quiz</button>
                            <a href="{{ route('quiz.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
