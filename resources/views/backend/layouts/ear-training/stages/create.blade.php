@extends('backend.app')
@section('title', 'Create Stage')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-0 fs-3">Create Ear Training Stage</h1>
    </div>
    <div class="pageheader-btn">
        <ol class="breadcrumb bg-transparent p-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.ear-training.stages.index') }}" class="text-warning text-decoration-none">Stages</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Create</li>
        </ol>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-5">
        <form action="{{ route('admin.ear-training.stages.store') }}" method="POST">
            @csrf

            <!-- CORE -->
            <h6 class="fw-bold mb-4 text-muted text-uppercase fs-7 tracking-wide">Core</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Stage</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" selected>Published</option>
                        <option value="0">Draft</option>
                    </select>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Short Description (Subtitle)</label>
                    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Display Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order') }}">
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label text-muted small">Full Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <hr class="mb-5 text-muted">

            <!-- STAGE DISPLAY -->
            <h6 class="fw-bold mb-4 text-muted text-uppercase fs-7 tracking-wide">
                <i class="fa fa-paint-brush text-danger me-2"></i> Stage Display
            </h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Accent Colour (HEX)</label>
                    <input type="text" name="accent_colour" class="form-control" value="{{ old('accent_colour', '#3B82F6') }}" placeholder="#3B82F6">
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Tint colour used for this stage's card on mobile</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Patterns Label</label>
                    <input type="text" name="patterns_label" class="form-control" value="{{ old('patterns_label') }}">
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Short pattern string shown below the stage name</small>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-muted small">Next Stage Teaser</label>
                <input type="text" name="next_stage_teaser" class="form-control" value="{{ old('next_stage_teaser') }}">
                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">One-line preview shown when this stage is locked</small>
            </div>
            
            <div class="mb-5">
                <label class="form-label text-muted small">Unlock Criteria (JSON Array)</label>
                <textarea name="unlock_criteria" class="form-control" rows="2" placeholder='e.g. ["stage_1_completed"]'>{{ old('unlock_criteria') }}</textarea>
                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Conditions to unlock the stage (e.g. minimum accuracy in the previous stage).</small>
            </div>

            <hr class="mb-5 text-muted">

            <!-- ACCESS CONTROL -->
            <h6 class="fw-bold mb-4 text-muted text-uppercase fs-7 tracking-wide">
                <i class="fa fa-lock text-warning me-2"></i> Access Control
            </h6>
            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label text-muted small">Is Premium?</label>
                    <select name="is_premium" class="form-select">
                        <option value="0" selected>No (Foundation / Free)</option>
                        <option value="1">Yes (Subscription Required)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Guest Access Override</label>
                    <select name="guest_access_override" class="form-select">
                        <option value="Use Level Rule" selected>🟢 Use Level Rule (Default)</option>
                        <option value="Unlocked">🔵 Always Unlocked</option>
                        <option value="Locked">🔴 Always Locked</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4 pt-1">
                        <input class="form-check-input" type="checkbox" name="is_locked_by_default" value="1">
                        <label class="form-check-label text-muted small mt-1 ms-1">Is Locked By Default</label>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <button type="submit" class="btn text-white me-2 px-4 py-2 fw-semibold" style="background-color: #fd7e14; border-radius: 6px;">Create Stage</button>
                <a href="{{ route('admin.ear-training.stages.index') }}" class="btn text-white px-4 py-2 fw-semibold" style="background-color: #e83e8c; border-radius: 6px;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
