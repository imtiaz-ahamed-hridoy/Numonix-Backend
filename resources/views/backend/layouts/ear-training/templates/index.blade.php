@extends('backend.app')

@section('title', 'Ear Training Templates')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Ear Training Templates</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Ear Training</a></li>
                <li class="breadcrumb-item active" aria-current="page">Templates</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom" style="display: flex; justify-content: space-between;">
                    <h3 class="card-title">All Templates</h3>
                    <a href="{{ route('admin.ear-training.templates.create') }}" class="btn btn-primary">Create New Template</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Pattern/Phrase</th>
                                    <th>Hint Text</th>
                                    <th>Prompt Text</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $index => $template)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($template->type) }}</span></td>
                                        <td>{{ $template->title ?: 'N/A' }}</td>
                                        <td>{{ $template->pattern ?: ($template->phrase ?: 'N/A') }}</td>
                                        <td>{{ Str::limit($template->hint_text, 60) ?: 'N/A' }}</td>
                                        <td>{{ Str::limit($template->prompt_text, 60) ?: 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('admin.ear-training.templates.edit', $template->id) }}"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <form action="{{ route('admin.ear-training.templates.destroy', $template->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
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
