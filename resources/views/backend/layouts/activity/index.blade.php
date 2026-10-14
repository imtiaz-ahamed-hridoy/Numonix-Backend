@extends('backend.app')

@section('title', 'Activity Logs')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Activity Logs</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">System</a></li>
                <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100" id="datatable">
                            <thead>
                                <tr>
                                    <th class="wd-5p">#</th>
                                    <th class="wd-10p">Log Name</th>
                                    <th class="wd-20p">Description</th>
                                    <th class="wd-10p">Model</th>
                                    <th class="wd-10p">Event</th>
                                    <th class="wd-15p">User</th>
                                    <th class="wd-25p">Changes</th>
                                    <th class="wd-5p">Action</th>
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
        const activityDestroyUrlTemplate = @json(route('activity.destroy', ['id' => '__ID__']));

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (!$.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable({
                    order: [[0, 'desc']],
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    processing: true,
                    serverSide: true,
                    pagingType: 'full_numbers',
                    dom: "<'row justify-content-between table-topbar'<'col-md-2 col-sm-4 px-0'l><'col-md-2 col-sm-4 px-0'f>>tipr",
                    ajax: "{{ route('activity.index') }}",
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'log_name', name: 'log_name' },
                        { data: 'description', name: 'description' },
                        { data: 'subject_type', name: 'subject_type' },
                        { data: 'event', name: 'event' },
                        { data: 'causer', name: 'causer' },
                        { data: 'attributes', name: 'attributes', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ]
                });
            }
        });

        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this log?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'DELETE',
                        url: activityDestroyUrlTemplate.replace('__ID__', id),
                        success: function(resp) {
                            $('#datatable').DataTable().ajax.reload();
                            if (resp['t-success']) {
                                toastr.success(resp.message);
                            } else {
                                toastr.error(resp.message);
                            }
                        },
                        error: function() {
                            toastr.error('An error occurred. Please try again.');
                        }
                    });
                }
            });
        }
    </script>
@endpush
