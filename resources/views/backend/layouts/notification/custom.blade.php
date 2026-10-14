@extends('backend.app')

@section('title', 'Custom Notifications')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Custom Notifications</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Notifications</a></li>
                <li class="breadcrumb-item active" aria-current="page">Custom</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Send Notification</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('custom-notifications.send') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="recipient_type">Recipients</label>
                            <select name="recipient_type" id="recipient_type" class="form-control" required>
                                <option value="all" {{ old('recipient_type', 'all') === 'all' ? 'selected' : '' }}>All Active Users</option>
                                <option value="specific" {{ old('recipient_type') === 'specific' ? 'selected' : '' }}>Specific Users</option>
                            </select>
                            @error('recipient_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3" id="user_ids_wrap">
                            <label class="form-label" for="user_ids">Select Users</label>
                            <select name="user_ids[]" id="user_ids" class="form-control select2" multiple>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', [])))>
                                        {{ $user->name }}{{ $user->email ? ' - ' . $user->email : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_ids')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" maxlength="120" required>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="body">Message</label>
                            <textarea name="body" id="body" class="form-control" rows="5" maxlength="500" required>{{ old('body') }}</textarea>
                            @error('body')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="data">Optional Data JSON</label>
                            <textarea name="data" id="data" class="form-control" rows="3" placeholder='{"screen":"notifications"}'>{{ old('data') }}</textarea>
                            @error('data')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">Send Notification</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Recent Custom Notifications</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Sent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->data['title'] ?? '-' }}</td>
                                        <td>{{ $notification->data['message'] ?? '-' }}</td>
                                        <td>{{ $notification->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No custom notifications sent yet.</td>
                                    </tr>
                                @endforelse
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
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            function toggleUserSelect() {
                const showUsers = $('#recipient_type').val() === 'specific';
                $('#user_ids_wrap').toggle(showUsers);
            }

            $('#recipient_type').on('change', toggleUserSelect);
            toggleUserSelect();
        });
    </script>
@endpush
