@extends('backend.app')

@section('title', 'Ear Training Stages')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Ear Training Stages</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Ear Training</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stages</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom" style="display: flex; justify-content: space-between;">
                    <h3 class="card-title">All Stages</h3>
                    <a href="{{ route('admin.ear-training.stages.create') }}" class="btn btn-primary">Create New Stage</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Subtitle</th>
                                    <th>Premium</th>
                                    <th>Status</th>
                                    <th>Levels</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stages as $stage)
                                    <tr>
                                        <td>{{ $stage->sort_order }}</td>
                                        <td>{{ $stage->title }}</td>
                                        <td>{{ $stage->subtitle ?? '-' }}</td>
                                        <td>
                                            @if($stage->is_premium)
                                                <span class="badge bg-danger"><i class="fa fa-lock"></i> Premium</span>
                                            @else
                                                <span class="badge bg-success"><i class="fa fa-unlock"></i> Free</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $stage->is_active ? 'success' : 'warning' }}">
                                                {{ $stage->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ear-training.levels.index', $stage->id) }}"
                                                class="btn btn-sm btn-info">
                                                Manage Levels ({{ $stage->levels()->count() }})
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ear-training.stages.edit', $stage->id) }}"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <button onclick="deleteStage({{ $stage->id }})"
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
        function deleteStage(id) {
            if (confirm('Are you sure you want to delete this stage? This will delete all associated levels.')) {
                $.ajax({
                    url: "{{ route('admin.ear-training.stages.destroy', ':id') }}".replace(':id', id),
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
