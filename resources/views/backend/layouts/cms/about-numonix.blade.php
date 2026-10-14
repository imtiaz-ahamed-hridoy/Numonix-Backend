@extends('backend.app')

@section('title', 'CMS: About NumoniX')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">CMS: About NumoniX</h1>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">Manage About NumoniX</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.about-numonix.update') }}" method="POST">
                    @csrf

                    <div class="form-group mb-3">
                        <label class="form-label">Page Title</label>
                        <input type="text" class="form-control" name="title" value="{{ old('title', $about->title ?? 'About NumoniX') }}" required placeholder="e.g. About NumoniX">
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Page Heading</label>
                        <input type="text" class="form-control" name="heading" value="{{ old('heading', $about->heading ?? 'About NumoniX') }}" required>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0 text-primary">Sections & Bullet Points</h4>
                    </div>

                    <div id="sections-container">
                        @php
                            $sections = old('sections', $about ? $about->sections->toArray() : []);
                        @endphp

                        @forelse($sections as $sIndex => $section)
                            <div class="section-group border border-primary p-4 mb-4 about-section">
                                <input type="hidden" name="sections[{{$sIndex}}][id]" value="{{ $section['id'] ?? '' }}">

                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1 me-3">
                                        <label class="form-label text-primary fw-bold">Section Title</label>
                                        <input type="text" class="form-control fw-bold"
                                            name="sections[{{$sIndex}}][heading]"
                                            value="{{ $section['heading'] }}" required>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-about-section mt-4" style="cursor: pointer;" title="Remove section">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="form-label">Section Description</label>
                                    <textarea class="form-control"
                                        name="sections[{{$sIndex}}][description]"
                                        rows="2">{{ $section['description'] ?? '' }}</textarea>
                                </div>

                                <div class="card shadow-none border">
                                    <div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">Bullet Points</h5>
                                    </div>
                                    <div class="card-body p-3 about-items-container" data-section="{{$sIndex}}">
                                        @php
                                            $items = $section['items'] ?? [];
                                        @endphp

                                        @forelse($items as $iIndex => $item)
                                            <div class="about-item border p-3 mb-3 d-flex align-items-start gap-2">
                                                <input type="hidden" name="sections[{{$sIndex}}][items][{{$iIndex}}][id]" value="{{ $item['id'] ?? '' }}">
                                                <input type="hidden" name="sections[{{$sIndex}}][items][{{$iIndex}}][title]" value="">
                                                <span class="badge bg-primary mt-2 item-number" style="min-width: 28px; min-height: 28px; line-height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    {{ $iIndex + 1 }}
                                                </span>
                                                <div class="flex-grow-1">
                                                    <input type="text" class="form-control" name="sections[{{$sIndex}}][items][{{$iIndex}}][body]"
                                                        value="{{ $item['body'] ?? '' }}" placeholder="Enter bullet point text..." required>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-about-item mt-2" style="cursor: pointer;" title="Remove bullet point">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0 text-center py-3 about-empty">No bullet points yet. Click "Add Bullet Point" below.</p>
                                        @endforelse

                                        <button type="button" class="btn btn-outline-primary btn-sm add-about-item mt-2" data-section="{{$sIndex}}">
                                            <i class="fe fe-plus me-1"></i> Add Bullet Point
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0 text-center py-3" id="about-empty-state">No sections yet. Click "Add Section" below to create one.</p>
                        @endforelse
                    </div>

                    <button type="button" class="btn btn-outline-primary add-about-section mt-2">
                        <i class="fe fe-plus me-1"></i> Add Section
                    </button>

                    <div class="mt-4 border-top pt-4">
                        <button type="submit" class="btn btn-success btn-lg px-5">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
(function() {
    var ready = function() {
        if (typeof jQuery === 'undefined') {
            setTimeout(ready, 100);
            return;
        }
        jQuery(function($) {

            // ---- Add Section (with persistent counter) ----
            var sectionCounter = {{ $about ? $about->sections->count() : 0 }};

            $(document).on('click', '.add-about-section', function() {
                var container = $('#sections-container');
                var newIndex = sectionCounter++;

                $('#about-empty-state').remove();

                var template = '<div class="section-group border border-primary p-4 mb-4 about-section">' +
                    '<input type="hidden" name="sections[' + newIndex + '][id]" value="">' +
                    '<div class="d-flex justify-content-between align-items-start mb-3">' +
                        '<div class="flex-grow-1 me-3">' +
                            '<label class="form-label text-primary fw-bold">Section Title</label>' +
                            '<input type="text" class="form-control fw-bold" name="sections[' + newIndex + '][heading]" required>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm remove-about-section mt-4" style="cursor: pointer;" title="Remove section">' +
                            '<i class="fe fe-trash-2"></i>' +
                        '</button>' +
                    '</div>' +
                    '<div class="form-group mb-4">' +
                        '<label class="form-label">Section Description</label>' +
                        '<textarea class="form-control" name="sections[' + newIndex + '][description]" rows="2"></textarea>' +
                    '</div>' +
                    '<div class="card shadow-none border">' +
                        '<div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">' +
                            '<h5 class="card-title mb-0">Bullet Points</h5>' +
                        '</div>' +
                        '<div class="card-body p-3 about-items-container" data-section="' + newIndex + '">' +
                            '<p class="text-muted mb-0 text-center py-3 about-empty">No bullet points yet. Click "Add Bullet Point" below.</p>' +
                            '<button type="button" class="btn btn-outline-primary btn-sm add-about-item mt-2" data-section="' + newIndex + '">' +
                                '<i class="fe fe-plus me-1"></i> Add Bullet Point' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                container.append(template);
            });

            // ---- Add Bullet Point ----
            $(document).on('click', '.add-about-item', function() {
                var container = $(this).closest('.about-items-container');
                var sectionIndex = container.data('section');
                var items = container.find('.about-item');
                var newIndex = items.length;

                container.find('.about-empty').remove();

                var template = '<div class="about-item border p-3 mb-3 d-flex align-items-start gap-2">' +
                    '<input type="hidden" name="sections[' + sectionIndex + '][items][' + newIndex + '][id]" value="">' +
                    '<input type="hidden" name="sections[' + sectionIndex + '][items][' + newIndex + '][title]" value="">' +
                    '<span class="badge bg-primary mt-2 item-number" style="min-width: 28px; min-height: 28px; line-height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">' + (newIndex + 1) + '</span>' +
                    '<div class="flex-grow-1">' +
                        '<input type="text" class="form-control" name="sections[' + sectionIndex + '][items][' + newIndex + '][body]" placeholder="Enter bullet point text..." required>' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-about-item mt-2" style="cursor: pointer;" title="Remove bullet point">' +
                        '<i class="fe fe-trash-2"></i>' +
                    '</button>' +
                '</div>';

                $(template).insertBefore(this);
            });

            // ---- Remove Section ----
            $(document).on('click', '.remove-about-section', function() {
                $(this).closest('.about-section').remove();
            });

            // ---- Remove Bullet Point ----
            $(document).on('click', '.remove-about-item', function() {
                $(this).closest('.about-item').remove();
            });

        });
    };
    ready();
})();
</script>
