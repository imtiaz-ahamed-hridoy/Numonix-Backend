@extends('backend.app')

@section('title', 'Ear Training Levels')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Levels for {{ $stage->title }}</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.ear-training.stages.index') }}">Stages</a></li>
                <li class="breadcrumb-item active" aria-current="page">Levels</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom" style="display: flex; justify-content: space-between;">
                    <h3 class="card-title">All Levels</h3>
                    <a href="{{ route('admin.ear-training.levels.create', $stage->id) }}" class="btn btn-primary">Create New Level</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th>Level #</th>
                                    <th>Title</th>
                                    <th>Exercise Type</th>
                                    <th>Premium</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($levels as $level)
                                    <tr>
                                        <td>{{ $level->level_number }}</td>
                                        <td>{{ $level->title ?? '-' }}</td>
                                        <td>{{ $level->exercise_type === 'identify_function' ? 'Identify Function' : 'Identify Progression' }}</td>
                                        <td>
                                            @if($level->is_premium)
                                                <span class="badge bg-danger"><i class="fa fa-lock"></i> Premium</span>
                                            @else
                                                <span class="badge bg-success"><i class="fa fa-unlock"></i> Free</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $level->is_active ? 'success' : 'warning' }}">
                                                {{ $level->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ear-training.levels.edit', $level->id) }}"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <button onclick="deleteLevel({{ $level->id }})"
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
        function deleteLevel(id) {
            if (confirm('Are you sure you want to delete this level?')) {
                $.ajax({
                    url: "{{ route('admin.ear-training.levels.destroy', ':id') }}".replace(':id', id),
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