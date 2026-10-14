@php
    $module = $module ?? null;
    $hasIcon = $module && $module->icon && file_exists(public_path($module->icon));
@endphp

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ $formTitle }}</h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- -- Title ---------------------------------------- --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Learn Lesson"
                            value="{{ old('title', $module->title ?? '') }}" required>
                    </div>

                    {{-- -- Icon SVG Upload ------------------------------ --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Icon (SVG)</label>
                        <div class="d-flex gap-3 align-items-start">
                            {{-- Upload Zone --}}
                            <div class="flex-grow-1">
                                <div id="iconDropZone" class="border border-2 rounded p-3 text-center"
                                     style="cursor:pointer;min-height:100px;display:flex;align-items:center;justify-content:center;flex-direction:column;background:#f8f9fa;transition:all 0.2s;border-style:dashed!important">
                                    <input type="file" name="icon" id="iconFileInput" accept=".svg,image/svg+xml" class="d-none">
                                    <div id="iconDropLabel">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="17 8 12 3 7 8"/>
                                            <line x1="12" y1="3" x2="12" y2="15"/>
                                        </svg>
                                        <p class="mb-0 text-muted small">Click or drag SVG file to upload</p>
                                        <small class="text-muted">Optional - upload to replace current icon</small>
                                    </div>
                                    <div id="iconPreviewArea" class="d-none">
                                        <img id="iconPreviewImg" src="" alt="Icon preview" style="width:48px;height:48px;object-fit:contain" class="mb-1">
                                        <p class="mb-0 small text-success" id="iconFileName"></p>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="removeIconBtn" style="font-size:11px">✕ Remove</button>
                                    </div>
                                </div>
                            </div>
                            {{-- Current Icon Preview --}}
                            @if($hasIcon)
                                <div class="text-center p-3 border rounded bg-white" style="min-width:100px">
                                    <small class="text-muted d-block mb-2">Current</small>
                                    <img src="{{ url($module->icon) }}" alt="Current icon" style="width:48px;height:48px;object-fit:contain">
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- -- Description ---------------------------------- --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Module description shown on home page" required>{{ old('description', $module->description ?? '') }}</textarea>
                    </div>

                    {{-- -- Badge, CTA ----------------------------------- --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Badge Text</label>
                            <input type="text" name="badge_text" class="form-control" placeholder="e.g. Lesson, Analysis"
                                value="{{ old('badge_text', $module->badge_text ?? '') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">CTA Button Text <span class="text-danger">*</span></label>
                            <input type="text" name="cta_text" class="form-control" placeholder="e.g. Start, Explore"
                                value="{{ old('cta_text', $module->cta_text ?? 'Start') }}" required>
                        </div>
                    </div>

                    {{-- -- Toggles --------------------------------------- --}}
                    <div class="row mb-4">
                        <div class="col-md-12 d-flex flex-column justify-content-center pt-2">
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="is_premium" value="0">
                                <input type="checkbox" class="form-check-input" name="is_premium" value="1" id="is_premium"
                                    {{ old('is_premium', $module->is_premium ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_premium">🔒 Premium (locked for Foundation users)</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active"
                                    {{ old('is_active', $module->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">✅ Active (visible on home page)</label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- -- Preview Card --------------------------------- --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Live Preview</label>
                        <div id="modulePreview" class="border rounded p-3" style="max-width:420px;">
                            <div class="d-flex align-items-start gap-2">
                                <div id="preview-icon-wrap" style="width:32px;height:32px;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                                    @if($hasIcon)
                                        <img id="preview-icon" src="{{ url($module->icon) }}" alt="" style="width:28px;height:28px;object-fit:contain">
                                    @else
                                        <img id="preview-icon" src="" alt="" style="width:28px;height:28px;object-fit:contain;display:none">
                                        <span id="preview-icon-placeholder" style="width:28px;height:28px;display:inline-block;background:#e9ecef;border-radius:4px"></span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong id="preview-title" style="font-size:14px">{{ $module->title ?? 'Module Title' }}</strong>
                                        <span id="preview-badge" class="badge" style="background:#6B7280;font-size:10px">{{ $module->badge_text ?? 'Badge' }}</span>
                                    </div>
                                    <p class="text-muted mb-2" id="preview-desc" style="font-size:12px;line-height:1.4">{{ \Illuminate\Support\Str::limit($module->description ?? 'Description goes here...', 100) }}</p>
                                    <button type="button" class="btn btn-sm btn-primary" id="preview-cta" style="border-radius:16px;font-size:11px;padding:3px 14px">{{ $module->cta_text ?? 'Start' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">💾 {{ $submitLabel }}</button>
                        <a href="{{ route('home-module.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // -- SVG Icon Upload ------------------------------------------
    const dropZone = document.getElementById('iconDropZone');
    const fileInput = document.getElementById('iconFileInput');
    const dropLabel = document.getElementById('iconDropLabel');
    const previewArea = document.getElementById('iconPreviewArea');
    const previewImg = document.getElementById('iconPreviewImg');
    const fileName = document.getElementById('iconFileName');
    const removeBtn = document.getElementById('removeIconBtn');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#2457E6';
        dropZone.style.background = '#e8f0fe';
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '';
        dropZone.style.background = '#f8f9fa';
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '';
        dropZone.style.background = '#f8f9fa';
        const files = e.dataTransfer.files;
        if (files.length && (files[0].type === 'image/svg+xml' || files[0].name.endsWith('.svg'))) {
            fileInput.files = files;
            showIconPreview(files[0]);
        } else {
            toastr?.error?.('Only SVG files are allowed.');
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length) showIconPreview(this.files[0]);
    });

    function showIconPreview(file) {
        const url = URL.createObjectURL(file);
        previewImg.src = url;
        fileName.textContent = file.name;
        dropLabel.classList.add('d-none');
        previewArea.classList.remove('d-none');
        // Update live card preview
        const liveIcon = document.getElementById('preview-icon');
        const placeholder = document.getElementById('preview-icon-placeholder');
        liveIcon.src = url;
        liveIcon.style.display = '';
        if (placeholder) placeholder.style.display = 'none';
    }

    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        previewImg.src = '';
        fileName.textContent = '';
        dropLabel.classList.remove('d-none');
        previewArea.classList.add('d-none');
        const liveIcon = document.getElementById('preview-icon');
        const placeholder = document.getElementById('preview-icon-placeholder');
        liveIcon.src = '';
        liveIcon.style.display = 'none';
        if (placeholder) placeholder.style.display = '';
    });



    function updatePreview() {
        const title = document.querySelector('input[name="title"]')?.value || 'Module Title';
        const desc = document.querySelector('textarea[name="description"]')?.value || 'Description...';
        const badge = document.querySelector('input[name="badge_text"]')?.value || '';
        const cta = document.querySelector('input[name="cta_text"]')?.value || 'Start';

        document.getElementById('preview-title').textContent = title;
        document.getElementById('preview-desc').textContent = desc.substring(0, 100) + (desc.length > 100 ? '...' : '');
        document.getElementById('preview-badge').textContent = badge;
        document.getElementById('preview-cta').textContent = cta;
    }

    document.querySelectorAll('input[type="text"], input[type="number"], input[type="color"], textarea').forEach(el => {
        el.addEventListener('input', updatePreview);
    });

    updatePreview();
</script>
@endpush
