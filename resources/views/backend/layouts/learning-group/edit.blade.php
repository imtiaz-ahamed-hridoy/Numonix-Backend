@extends('backend.app')
@section('title', 'Edit Learning Group')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Edit Learning Group</h4>
                <a href="{{ route('learning-group.index') }}" class="btn btn-outline-secondary">← Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('learning-group.update', $group->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="case_study" @selected($group->category === 'case_study')>Case Study</option>
                                <option value="fnh_architecture" @selected($group->category === 'fnh_architecture')>FNH Architecture</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Label <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" value="{{ old('label', $group->label) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Color <small class="text-muted fw-normal">(pick a color)</small></label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="colorPicker" name="color" class="form-control form-control-color" value="{{ old('color', $group->color ?? '#059669') }}" style="width:60px;height:38px;cursor:pointer;">
                                <input type="text" id="colorHex" class="form-control" value="{{ old('color', $group->color ?? '#059669') }}" maxlength="7" style="width:100px;font-family:monospace;" placeholder="#059669">
                            </div>
                            @push('scripts')
                            <script>
                                (function() {
                                    const picker = document.getElementById('colorPicker');
                                    const hex = document.getElementById('colorHex');
                                    if (picker && hex) {
                                        picker.addEventListener('input', function() {
                                            hex.value = this.value;
                                        });
                                        hex.addEventListener('input', function() {
                                            if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
                                                picker.value = this.value;
                                            }
                                        });
                                    }
                                })();
                            </script>
                            @endpush
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $group->description) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Is Premium? (Requires Subscription)</label>
                            <select name="is_premium" class="form-select">
                                <option value="0" @selected(!$group->is_premium)>No (Foundation / Free)</option>
                                <option value="1" @selected($group->is_premium)>Yes (Locked)</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">💾 Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
