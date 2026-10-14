@extends('backend.app')

@section('title', 'CMS: Complete Glossary')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">CMS: Complete Glossary</h1>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">Manage Complete Glossary</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.complete-glossary.update') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="form-label">Page Title</label>
                        <input type="text" class="form-control" name="title" value="{{ old('title', $glossary->title ?? 'Complete Glossary') }}" required placeholder="e.g. Complete Glossary">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Sections & Items</h4>
                        <button type="button" class="btn btn-sm btn-primary" id="add-section-btn">
                            <i class="fe fe-plus"></i> Add Section Group
                        </button>
                    </div>

                    <div id="sections-container">
                        @php
                            $sections = old('sections', $glossary ? $glossary->sections->toArray() : []);
                        @endphp
                        
                        @forelse($sections as $sIndex => $section)
                            <div class="section-group border border-primary p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div class="flex-grow-1 me-3">
                                        <label class="form-label text-primary">Section Label (e.g. I. FIXED NUMERIC SYSTEM)</label>
                                        <input type="text" class="form-control fw-bold" name="sections[{{$sIndex}}][label]" value="{{ $section['label'] }}" required style="text-transform: uppercase;">
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-section-btn mt-4" style="cursor: pointer;" title="Remove Section">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </div>

                                <div class="card shadow-none border">
                                    <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-light">
                                        <h5 class="card-title mb-0">Items in this Section</h5>
                                        <button type="button" class="btn btn-sm btn-secondary add-item-btn" data-section-index="{{$sIndex}}">
                                            <i class="fe fe-plus"></i> Add Item
                                        </button>
                                    </div>
                                    <div class="card-body p-3 items-container-{{$sIndex}}">
                                        @php
                                            $items = $section['items'] ?? [];
                                        @endphp
                                        
                                        @forelse($items as $iIndex => $item)
                                            <div class="item-card border p-3 mb-3">
                                                <div class="d-flex align-items-start gap-2">
                                                    <div class="flex-grow-1">
                                                        <div class="row">
                                                            <div class="col-md-12 mb-2">
                                                                <label class="form-label">Title (Optional)</label>
                                                                <input type="text" class="form-control" name="sections[{{$sIndex}}][items][{{$iIndex}}][title]" value="{{ $item['title'] ?? '' }}">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label">Body</label>
                                                                <textarea class="form-control" name="sections[{{$sIndex}}][items][{{$iIndex}}][body]" rows="2" required>{{ $item['body'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn mt-4" style="cursor: pointer;" title="Remove Item">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <!-- No items -->
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- No sections -->
                        @endforelse
                    </div>

                    <div class="mt-4 border-top pt-4">
                        <button type="submit" class="btn btn-success btn-lg">Save Complete Glossary</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Remove buttons use native JS event delegation (works immediately, no jQuery dependency) --}}
<script>
document.addEventListener('click', function(e) {
    // Remove section
    var sectionBtn = e.target.closest('.remove-section-btn');
    if (sectionBtn) {
        var group = sectionBtn.closest('.section-group');
        if (group) group.remove();
    }
    // Remove item
    var itemBtn = e.target.closest('.remove-item-btn');
    if (itemBtn) {
        var card = itemBtn.closest('.item-card');
        if (card) card.remove();
    }
});
</script>

{{-- Add section and add item require jQuery --}}
<script>
(function waitForJquery() {
    if (typeof jQuery === 'undefined') { setTimeout(waitForJquery, 50); return; }
    jQuery(function($) {
        var sectionCount = {{ count($sections ?? []) }};
        var itemCounts = {};
        @foreach($sections as $sIndex => $section)
            itemCounts[{{$sIndex}}] = {{ count($section['items'] ?? []) }};
        @endforeach

        $('#add-section-btn').on('click', function() {
            var sIndex = sectionCount++;
            itemCounts[sIndex] = 0;
            
            var html = '<div class="section-group border border-primary p-4 mb-4">' +
                '<div class="d-flex justify-content-between align-items-start mb-4">' +
                    '<div class="flex-grow-1 me-3">' +
                        '<label class="form-label text-primary">Section Label (e.g. I. FIXED NUMERIC SYSTEM)</label>' +
                        '<input type="text" class="form-control fw-bold" name="sections[' + sIndex + '][label]" required style="text-transform: uppercase;">' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-section-btn mt-4" style="cursor: pointer;" title="Remove Section">' +
                        '<i class="fe fe-trash-2"></i>' +
                    '</button>' +
                '</div>' +
                '<div class="card shadow-none border">' +
                    '<div class="card-header border-bottom d-flex justify-content-between align-items-center bg-light">' +
                        '<h5 class="card-title mb-0">Items in this Section</h5>' +
                        '<button type="button" class="btn btn-sm btn-secondary add-item-btn" data-section-index="' + sIndex + '">' +
                            '<i class="fe fe-plus"></i> Add Item' +
                        '</button>' +
                    '</div>' +
                    '<div class="card-body p-3 items-container-' + sIndex + '"></div>' +
                '</div>' +
            '</div>';
            $('#sections-container').append(html);
        });

        $(document).on('click', '.add-item-btn', function() {
            var sIndex = $(this).data('section-index');
            var iIndex = itemCounts[sIndex] || 0;
            itemCounts[sIndex] = iIndex + 1;
            
            var html = '<div class="item-card border p-3 mb-3">' +
                '<div class="d-flex align-items-start gap-2">' +
                    '<div class="flex-grow-1">' +
                        '<div class="row">' +
                            '<div class="col-md-12 mb-2">' +
                                '<label class="form-label">Title (Optional)</label>' +
                                '<input type="text" class="form-control" name="sections[' + sIndex + '][items][' + iIndex + '][title]">' +
                            '</div>' +
                            '<div class="col-md-12">' +
                                '<label class="form-label">Body</label>' +
                                '<textarea class="form-control" name="sections[' + sIndex + '][items][' + iIndex + '][body]" rows="2" required></textarea>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-item-btn mt-4" style="cursor: pointer;" title="Remove Item">' +
                        '<i class="fe fe-trash-2"></i>' +
                    '</button>' +
                '</div>' +
            '</div>';
            $('.items-container-' + sIndex).append(html);
        });
    });
})();
</script>
@endsection
