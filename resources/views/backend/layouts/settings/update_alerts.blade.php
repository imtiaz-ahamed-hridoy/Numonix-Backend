@extends('backend.app')

@section('title', 'Update Alerts')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Update Alerts</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Update Alerts</li>
            </ol>
        </div>
    </div>
    {{-- END PAGE-HEADER --}}

    <div class="row">
        <div class="col-lg-12">
            <div class="card box-shadow-0">
                <div class="card-header">
                    <h4 class="card-title mb-0">App Update Alert Settings</h4>
                </div>
                <div class="card-body">

                    <p class="text-muted mb-4">
                        Control the update alert behaviour on the mobile app. When a toggle is enabled the
                        corresponding API endpoint returns <code>true</code>, and the app reacts accordingly.
                        <br>
                        API endpoints (public, no auth required):
                        <br>
                        <code>GET {{ url('/api/app-update/soft') }}</code><br>
                        <code>GET {{ url('/api/app-update/force') }}</code>
                    </p>

                    <form method="POST" action="{{ route('update-alerts.update') }}">
                        @csrf
                        @method('PATCH')

                        {{-- -- Update Details (optional metadata) --------------- --}}
                        <div class="card border mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-info me-1"></i> Update Details (optional)
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">
                                    These fields are returned alongside the <code>data</code> flag in both APIs so the
                                    update dialog can deep-link users directly to the latest version in the stores.
                                </p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="latest_version" class="form-label">Latest Version:</label>
                                            <input type="text" class="form-control @error('latest_version') is-invalid @enderror"
                                                id="latest_version" name="latest_version" placeholder="e.g. 2.1.0"
                                                value="{{ old('latest_version', $setting->latest_version ?? '') }}">
                                            @error('latest_version')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="app_store_url" class="form-label">App Store URL (iOS):</label>
                                            <input type="text" class="form-control @error('app_store_url') is-invalid @enderror"
                                                id="app_store_url" name="app_store_url"
                                                placeholder="https://apps.apple.com/app/idXXXXXXXX"
                                                value="{{ old('app_store_url', $setting->app_store_url ?? '') }}">
                                            @error('app_store_url')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="play_store_url" class="form-label">Play Store URL (Android):</label>
                                            <input type="text" class="form-control @error('play_store_url') is-invalid @enderror"
                                                id="play_store_url" name="play_store_url"
                                                placeholder="https://play.google.com/store/apps/details?id=com.example"
                                                value="{{ old('play_store_url', $setting->play_store_url ?? '') }}">
                                            @error('play_store_url')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- -- Soft Update Alert -------------------------------- --}}
                        <div class="card border mb-4">
                            <div class="card-body d-flex align-items-start gap-4">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">
                                        <span class="badge bg-warning-transparent me-2">Soft</span>
                                        Soft Update Alert
                                    </h5>
                                    <p class="text-muted mb-0">
                                        Shows a <strong>dismissible</strong> dialog on the mobile app - the user
                                        can skip and continue using the app. Use this when a new version is
                                        available but not critical.
                                    </p>
                                    <small class="text-secondary">
                                        API: <code>GET /api/app-update/soft</code>
                                        returns <code>{{ $setting && $setting->soft_update_alert ? 'true' : 'false' }}</code> currently.
                                    </small>
                                </div>
                                <div class="d-flex flex-column align-items-center" style="min-width:80px;">
                                    <label class="custom-switch" style="cursor:pointer;" title="Toggle Soft Update Alert">
                                        <input
                                            id="soft_update_alert"
                                            type="checkbox"
                                            name="soft_update_alert"
                                            value="1"
                                            class="custom-switch-input"
                                            {{ $setting && $setting->soft_update_alert ? 'checked' : '' }}
                                        >
                                        <span class="custom-switch-indicator"></span>
                                    </label>
                                    <small class="mt-1 fw-semibold soft-status-text {{ $setting && $setting->soft_update_alert ? 'text-success' : 'text-secondary' }}">
                                        {{ $setting && $setting->soft_update_alert ? 'Enabled' : 'Disabled' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- -- Force Update Alert ------------------------------- --}}
                        <div class="card border mb-4" id="force-alert-card">
                            <div class="card-body d-flex align-items-start gap-4">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">
                                        <span class="badge bg-danger-transparent me-2">Force</span>
                                        Force Update Alert
                                    </h5>
                                    <p class="text-muted mb-0">
                                        Shows a <strong>blocking</strong> dialog - the user <strong>cannot</strong>
                                        dismiss it and must update before continuing. Use this only for critical
                                        breaking changes.
                                    </p>
                                    <small class="text-secondary">
                                        API: <code>GET /api/app-update/force</code>
                                        returns <code>{{ $setting && $setting->force_update_alert ? 'true' : 'false' }}</code> currently.
                                    </small>
                                </div>
                                <div class="d-flex flex-column align-items-center" style="min-width:80px;">
                                    <label class="custom-switch" style="cursor:pointer;" title="Toggle Force Update Alert">
                                        <input
                                            id="force_update_alert"
                                            type="checkbox"
                                            name="force_update_alert"
                                            value="1"
                                            class="custom-switch-input"
                                            {{ $setting && $setting->force_update_alert ? 'checked' : '' }}
                                        >
                                        <span class="custom-switch-indicator"></span>
                                    </label>
                                    <small class="mt-1 fw-semibold force-status-text {{ $setting && $setting->force_update_alert ? 'text-danger' : 'text-secondary' }}">
                                        {{ $setting && $setting->force_update_alert ? 'Enabled' : 'Disabled' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Warning when force update is on --}}
                        <div id="force-warning"
                             class="alert alert-danger d-flex align-items-center gap-2 {{ $setting && $setting->force_update_alert ? '' : 'd-none' }}"
                             role="alert">
                            <i class="fe fe-alert-triangle fs-5"></i>
                            <div>
                                <strong>Warning:</strong> Force Update is currently <strong>enabled</strong>.
                                All mobile users will be blocked from using the app until they update.
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fe fe-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const softToggle  = document.getElementById('soft_update_alert');
        const forceToggle = document.getElementById('force_update_alert');
        const softLabel   = document.querySelector('.soft-status-text');
        const forceLabel  = document.querySelector('.force-status-text');
        const forceCard   = document.getElementById('force-alert-card');
        const forceWarn   = document.getElementById('force-warning');

        function refreshSoftLabel() {
            if (softToggle.checked) {
                softLabel.textContent = 'Enabled';
                softLabel.classList.replace('text-secondary', 'text-success');
            } else {
                softLabel.textContent = 'Disabled';
                softLabel.classList.replace('text-success', 'text-secondary');
            }
        }

        function refreshForceLabel() {
            if (forceToggle.checked) {
                forceLabel.textContent = 'Enabled';
                forceLabel.classList.replace('text-secondary', 'text-danger');
                forceCard.classList.add('border-danger');
                forceWarn.classList.remove('d-none');
            } else {
                forceLabel.textContent = 'Disabled';
                forceLabel.classList.replace('text-danger', 'text-secondary');
                forceCard.classList.remove('border-danger');
                forceWarn.classList.add('d-none');
            }
        }

        softToggle.addEventListener('change', refreshSoftLabel);
        forceToggle.addEventListener('change', refreshForceLabel);

        // Initialise border on load if force is active
        if (forceToggle.checked) {
            forceCard.classList.add('border-danger');
        }
    })();
</script>
@endpush
