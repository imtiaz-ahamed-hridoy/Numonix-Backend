@extends('backend.app')
@section('title', 'Learning CMS')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">Learning CMS</h4>
                    <p class="text-muted mb-0">Manage Lessons, Core Theory, and Advanced Insights.</p>
                </div>
                <div>
                <a href="{{ route('learning-group.index') }}" class="btn btn-sm btn-outline-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                            View All Groups
                                        </a>
                <a href="{{ route('learning.create') }}" class="btn btn-outline-primary">Add Learning Content</a>
                                    </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="learningTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Premium</th>
                                <th>Published</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const learningDestroyUrlTemplate = @json(route('learning.destroy', ['id' => '__ID__']));

    $(document).ready(function() {
        $('#learningTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('learning.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'category', name: 'category' },
                { data: 'title', name: 'title' },
                { data: 'status', name: 'status' },
                { data: 'is_premium', name: 'is_premium' },
                { data: 'is_published', name: 'is_published' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });

    function showDeleteConfirm(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: learningDestroyUrlTemplate.replace('__ID__', id),
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response['t-success']) {
                            $('#learningTable').DataTable().ajax.reload();
                            toastr.success(response.message);
                        }
                    }
                });
            }
        })
    }
</script>
@endpush
