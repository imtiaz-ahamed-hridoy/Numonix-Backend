@extends('backend.app')

@section('title', 'Firebase Settings')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Firebase Settings</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Firebase</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            @if(isset($activeConfig) && $activeConfig['configured'])
                <div class="alert alert-success d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fe fe-check-circle fs-20 me-3 text-success"></i>
                        <div>
                            <h5 class="alert-heading mb-1 fw-bold text-success">Firebase Credentials Active & Configured</h5>
                            <p class="mb-0">
                                <strong>Project ID:</strong> <code>{{ $activeConfig['project_id'] }}</code> &nbsp;|&nbsp;
                                <strong>Client Email:</strong> <code>{{ $activeConfig['client_email'] }}</code>
                            </p>
                        </div>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('firebase.setting.destroy') }}" onsubmit="return confirm('Are you sure you want to delete the active Firebase credentials? Push notifications will be paused until new credentials are provided.');" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fe fe-trash-2 me-1"></i> Remove Credentials
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <i class="fe fe-alert-triangle fs-20 me-3 text-warning"></i>
                    <div>
                        <h5 class="alert-heading mb-1 fw-bold text-warning">Firebase Credentials Not Yet Configured</h5>
                        <p class="mb-0">Upload your Firebase Service Account JSON file below to enable FCM push notifications and social login token verification.</p>
                    </div>
                </div>
            @endif

            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Upload Firebase Service Account Key</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('firebase.setting.update') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="alert alert-info">
                            <h5 class="alert-heading mb-1"><i class="fe fe-info me-2"></i>How to get your Firebase Credentials:</h5>
                            <p class="mb-0">Visit your <a href="https://console.firebase.google.com/" target="_blank" class="fw-bold text-primary text-decoration-underline">Firebase Console</a> &gt; <strong>Project Settings</strong> &gt; <strong>Service accounts</strong> tab &gt; Click <strong>Generate new private key</strong>. Upload the downloaded <code>.json</code> file below.</p>
                        </div>

                        <div class="form-group mb-4">
                            <label for="firebase_credentials" class="form-label fw-bold">Firebase Service Account JSON File:</label>
                            <input type="file" class="dropify @error('firebase_credentials') is-invalid @enderror" 
                                name="firebase_credentials" 
                                id="firebase_credentials" 
                                data-allowed-file-extensions="json"
                                data-default-file="{{ (isset($activeConfig) && $activeConfig['configured']) ? 'Active Key: ' . $activeConfig['project_id'] : '' }}">
                            @error('firebase_credentials')
                                <span class="text-danger d-block mt-2 font-weight-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button class="btn btn-primary" type="submit"><i class="fe fe-upload me-2"></i>Upload & Apply Credentials</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
