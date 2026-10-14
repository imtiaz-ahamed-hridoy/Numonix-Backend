@extends('backend.app')

@section('title', 'System Settings')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">System Settings</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">System Settings</li>
            </ol>
        </div>
    </div>
    {{-- PAGE-HEADER --}}


    <div class="row">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <div class="card box-shadow-0">
                <div class="card-body">
                    <form method="post" action="{{ route('system.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-label">Title:</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                        name="title" placeholder="title" id="title"
                                        value="{{ old('title', $setting->title ?? '') }}">
                                    @error('title')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="systemName" class="form-label">System Name:</label>
                                    <input type="text" class="form-control @error('system_name') is-invalid @enderror"
                                        name="system_name" placeholder="System Name" id="systemName"
                                        value="{{ old('system_name', $setting->system_name ?? '') }}">
                                    @error('system_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email:</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" placeholder="Enter Email here" id="email"
                                        value="{{ old('email', $setting->email ?? '') }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number" class="form-label">Phone Number:</label>
                                    <input type="text" class="form-control @error('number') is-invalid @enderror"
                                        name="number" placeholder="Enter Phone Number" id="number"
                                        value="{{ old('number', $setting->number ?? '') }}">
                                    @error('number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address" class="form-label">Address:</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                        name="address" placeholder="Enter Address" id="address"
                                        value="{{ old('address', $setting->address ?? '') }}">
                                    @error('address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="copyRights" class="form-label">Copy Rights Text:</label>
                                    <input type="text" class="form-control @error('copyright_text') is-invalid @enderror"
                                        name="copyright_text" placeholder="Copy Rights Text" id="copyRights"
                                        value="{{ old('copyright_text', $setting->copyright_text ?? '') }}">
                                    @error('copyright_text')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="summernote" class="form-label">About System:</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="summernote"
                                name="description"
                                placeholder="About System">{{ old('description', $setting->description ?? '') }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo" class="form-label">Logo:</label>
                                    <input type="hidden" name="remove_logo" value="0">
                                    <input type="file" class="dropify @error('logo') is-invalid @enderror" name="logo"
                                        id="logo"
                                        data-default-file="@isset($setting->logo){{ asset('storage/' . $setting->logo) }}@endisset">
                                    @error('logo')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo" class="form-label">Favicon:</label>
                                    <input type="hidden" name="remove_favicon" value="0">
                                    <input type="file" class="dropify @error('favicon') is-invalid @enderror" name="favicon"
                                        id="favicon"
                                        data-default-file="@isset($setting->favicon){{ asset('storage/' . $setting->favicon) }}@endisset">
                                    @error('favicon')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facebook_url" class="form-label">Facebook URL:</label>
                                    <input type="url" class="form-control @error('facebook_url') is-invalid @enderror"
                                        name="facebook_url" placeholder="Enter facebook url" id="facebook_url"
                                        value="{{ old('facebook_url', $setting->facebook_url ?? '') }}">
                                    @error('facebook_url')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="twitter_url" class="form-label">Twitter URL:</label>
                                    <input type="url" class="form-control @error('twitter_url') is-invalid @enderror"
                                        name="twitter_url" placeholder="twitter url" id="twitter_url"
                                        value="{{ old('twitter_url', $setting->twitter_url ?? '') }}">
                                    @error('twitter_url')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="linkedin_url" class="form-label">Linked In URL:</label>
                                    <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror"
                                        name="linkedin_url" placeholder="linkedin url" id="linkedin_url"
                                        value="{{ old('linkedin_url', $setting->linkedin_url ?? '') }}">
                                    @error('linkedin_url')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instagram_url" class="form-label">Instagram URL:</label>
                                    <input type="url" class="form-control @error('instagram_url') is-invalid @enderror"
                                        name="instagram_url" placeholder="instagram url" id="instagram_url"
                                        value="{{ old('instagram_url', $setting->instagram_url ?? '') }}">
                                    @error('instagram_url')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="youtube_url" class="form-label">Youtube URL:</label>
                                    <input type="url" class="form-control @error('youtube_url') is-invalid @enderror"
                                        name="youtube_url" placeholder="youtube url" id="youtube_url"
                                        value="{{ old('youtube_url', $setting->youtube_url ?? '') }}">
                                    @error('youtube_url')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="whatsapp_url" class="form-label">Whatsapp URL:</label>
                                    <input type="url" class="form-control @error('whatsapp_url') is-invalid @enderror"
                                        name="whatsapp_url" placeholder="whatsapp url" id="whatsapp_url"
                                        value="{{ old('whatsapp_url', $setting->whatsapp_url ?? '') }}">
                                    @error('whatsapp_url')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telegram_url" class="form-label">Telegram URL:</label>
                                    <input type="url" class="form-control @error('telegram_url') is-invalid @enderror"
                                        name="telegram_url" placeholder="telegram url" id="telegram_url"
                                        value="{{ old('telegram_url', $setting->telegram_url ?? '') }}">
                                    @error('telegram_url')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="firebase_credentials" class="form-label">Firebase Credentials
                                        (JSON):</label>
                                    <input type="file"
                                        class="form-control @error('firebase_credentials') is-invalid @enderror"
                                        name="firebase_credentials" id="firebase_credentials" accept=".json">
                                    @error('firebase_credentials')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div> --}}

                        <div class="form-group">
                            <button class="btn btn-primary" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection