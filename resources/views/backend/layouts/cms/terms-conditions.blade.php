@extends('backend.app')

@section('title', 'CMS: Terms & Conditions')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">CMS: Terms & Conditions</h1>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">Manage Terms & Conditions</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.terms-conditions.update') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Page Title</label>
                        <input type="text" class="form-control" name="title" value="{{ old('title', $terms->title ?? 'Terms & Conditions') }}" required placeholder="e.g. Terms & Conditions">
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Last Updated Label</label>
                        <input type="text" class="form-control" name="last_updated_label" value="{{ old('last_updated_label', $terms->last_updated_label ?? 'January 2025') }}" required placeholder="e.g. January 2025">
                    </div>

                    <div class="card mt-4 border shadow-none">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Sections</h4>
                            <button type="button" class="btn btn-sm btn-primary" id="add-section-btn">
                                <i class="fe fe-plus"></i> Add Section
                            </button>
                        </div>
                        <div class="card-body p-3" id="sections-container">
                            @php
                                $sections = old('sections', $terms ? $terms->sections->toArray() : []);
                            @endphp
                            
                            @forelse($sections as $index => $section)
                                <div class="section-item border p-3 mb-3 bg-light">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Title</label>
                                                    <input type="text" class="form-control" name="sections[{{$index}}][title]" value="{{ $section['title'] }}" required>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Body</label>
                                                    <textarea class="form-control" name="sections[{{$index}}][body]" rows="3" required>{{ $section['body'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-section-btn mt-4" style="cursor: pointer;" title="Remove">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <!-- No sections initially -->
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Remove buttons use native JS event delegation (works immediately, no jQuery dependency) --}}
<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-section-btn');
    if (btn) {
        var item = btn.closest('.section-item');
        if (item) item.remove();
    }
});
</script>

{{-- Add section requires jQuery --}}
<script>
(function waitForJquery() {
    if (typeof jQuery === 'undefined') { setTimeout(waitForJquery, 50); return; }
    jQuery(function($) {
        var sectionCount = {{ count($sections ?? []) }};
        $('#add-section-btn').on('click', function() {
            var index = sectionCount++;
            var html = '<div class="section-item border p-3 mb-3 bg-light">' +
                '<div class="d-flex align-items-start gap-2">' +
                    '<div class="flex-grow-1">' +
                        '<div class="row">' +
                            '<div class="col-md-12 mb-3">' +
                                '<label class="form-label">Title</label>' +
                                '<input type="text" class="form-control" name="sections[' + index + '][title]" required>' +
                            '</div>' +
                            '<div class="col-md-12">' +
                                '<label class="form-label">Body</label>' +
                                '<textarea class="form-control" name="sections[' + index + '][body]" rows="3" required></textarea>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-section-btn mt-4" style="cursor: pointer;" title="Remove">' +
                        '<i class="fe fe-trash-2"></i>' +
                    '</button>' +
                '</div>' +
            '</div>';
            $('#sections-container').append(html);
        });
    });
})();
</script>
@endsection
