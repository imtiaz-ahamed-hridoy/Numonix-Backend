@extends('backend.app')
@section('title', 'Home Modules')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">Home Modules</h4>
                    <p class="text-muted mb-0">Manage the modules shown on the app home page.</p>
                </div>
                <a href="{{ route('home-module.create') }}" class="btn btn-primary">+ Add Module</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="homeModuleTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Badge</th>
                                <th>Premium</th>
                                <th>Status</th>
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
    const moduleDestroyUrlTemplate = @json(route('home-module.destroy', ['id' => '__ID__']));

    $(document).ready(function() {
        $('#homeModuleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('home-module.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title', name: 'title' },
                { data: 'badge_text', name: 'badge_text' },
                { data: 'is_premium', name: 'is_premium' },
                { data: 'is_active', name: 'is_active' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });

    function deleteModule(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove the module from the home page!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: moduleDestroyUrlTemplate.replace('__ID__', id),
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#homeModuleTable').DataTable().ajax.reload();
                            toastr.success(response.message);
                        }
                    }
                });
            }
        })
    }
</script>
@endpush
