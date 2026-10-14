@extends('backend.app')

@section('title', 'CMS: Glossary')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">CMS: Glossary</h1>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">Manage Glossary Terms</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.glossary.update') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="form-label">Page Title</label>
                        <input type="text" class="form-control" name="title" value="{{ old('title', $glossary->title ?? 'Glossary') }}" required placeholder="e.g. Glossary">
                    </div>

                    <div class="card border shadow-none">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Terms</h4>
                            <button type="button" class="btn btn-sm btn-primary" id="add-term-btn">
                                <i class="fe fe-plus"></i> Add Term
                            </button>
                        </div>
                        <div class="card-body p-3" id="terms-container">
                            @php
                                $terms = old('terms', $glossary ? $glossary->terms->toArray() : []);
                            @endphp
                            
                            @forelse($terms as $index => $term)
                                <div class="term-item border p-3 mb-3 bg-light">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Term</label>
                                                    <input type="text" class="form-control" name="terms[{{$index}}][term]" value="{{ $term['term'] }}" required>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Definition</label>
                                                    <textarea class="form-control" name="terms[{{$index}}][definition]" rows="2" required>{{ $term['definition'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-term-btn mt-4" style="cursor: pointer;" title="Remove">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <!-- No terms initially -->
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
    var btn = e.target.closest('.remove-term-btn');
    if (btn) {
        var item = btn.closest('.term-item');
        if (item) item.remove();
    }
});
</script>

{{-- Add term requires jQuery --}}
<script>
(function waitForJquery() {
    if (typeof jQuery === 'undefined') { setTimeout(waitForJquery, 50); return; }
    jQuery(function($) {
        var termCount = {{ count($terms ?? []) }};
        $('#add-term-btn').on('click', function() {
            var index = termCount++;
            var html = '<div class="term-item border p-3 mb-3 bg-light">' +
                '<div class="d-flex align-items-start gap-2">' +
                    '<div class="flex-grow-1">' +
                        '<div class="row">' +
                            '<div class="col-md-12 mb-3">' +
                                '<label class="form-label">Term</label>' +
                                '<input type="text" class="form-control" name="terms[' + index + '][term]" required>' +
                            '</div>' +
                            '<div class="col-md-12">' +
                                '<label class="form-label">Definition</label>' +
                                '<textarea class="form-control" name="terms[' + index + '][definition]" rows="2" required></textarea>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-term-btn mt-4" style="cursor: pointer;" title="Remove">' +
                        '<i class="fe fe-trash-2"></i>' +
                    '</button>' +
                '</div>' +
            '</div>';
            $('#terms-container').append(html);
        });
    });
})();
</script>
@endsection
