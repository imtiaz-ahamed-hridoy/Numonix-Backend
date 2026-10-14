@extends('backend.app')

@section('title', 'CMS: User Guide')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">CMS: User Guide</h1>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">Manage User Guide</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.user-guide.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="panel panel-primary tabs-style-2">
                        <div class="tab-menu-heading">
                            <div class="tabs-menu1">
                                <ul class="nav panel-tabs main-nav-line">
                                    <li><a href="#getting_started" class="nav-link active" data-bs-toggle="tab">Getting Started</a></li>
                                    <li><a href="#harmonic_builder" class="nav-link" data-bs-toggle="tab">Harmonic Builder</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="panel-body tabs-menu-body main-content-body-right border">
                            <div class="tab-content">

                                @php
                                    $welcomeTab = $tabs['getting_started'] ?? null;
                                    $harmonicTab = $tabs['harmonic_builder'] ?? null;
                                @endphp

                                <!-- WELCOME TAB -->
                                <div class="tab-pane active" id="getting_started">
                                    <input type="hidden" name="tabs[0][tab]" value="getting_started">

                                    <div class="form-group">
                                        <label class="form-label">Heading</label>
                                        <input type="text" class="form-control" name="tabs[0][heading]"
                                            value="{{ old('tabs.0.heading', $welcomeTab->heading ?? 'Welcome to NumoniX') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Intro Text</label>
                                        <textarea class="form-control" name="tabs[0][intro_text]" rows="3">{{ old('tabs.0.intro_text', $welcomeTab->intro_text ?? '') }}</textarea>
                                    </div>

                                    <div class="mt-4">
                                        <h4 class="card-title mb-3 text-primary">Sections & Bullet Points</h4>

                                        <div id="welcome-sections-container">
                                        @php
                                            $welcomeSections = old('tabs.0.sections', $welcomeTab ? $welcomeTab->sections->toArray() : []);
                                        @endphp

                                        @forelse($welcomeSections as $sIndex => $section)
                                            <div class="section-group border border-primary p-4 mb-4 welcome-section">
                                                <input type="hidden" name="tabs[0][sections][{{$sIndex}}][id]" value="{{ $section['id'] ?? '' }}">

                                                <div class="d-flex justify-content-between align-items-start mb-4">
                                                    <div class="flex-grow-1 me-3">
                                                        <label class="form-label text-primary fw-bold">Section Heading</label>
                                                        <input type="text" class="form-control fw-bold"
                                                            name="tabs[0][sections][{{$sIndex}}][heading]"
                                                            value="{{ $section['heading'] }}" required>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-welcome-section mt-4" style="cursor: pointer;" title="Remove section">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>

                                                <div class="card shadow-none border">
                                                    <div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">
                                                        <h5 class="card-title mb-0">Bullet Points</h5>
                                                    </div>
                                                    <div class="card-body p-3 welcome-items-container" data-tab="0" data-section="{{$sIndex}}">
                                                        @php
                                                            $items = $section['items'] ?? [];
                                                        @endphp

                                                        @forelse($items as $iIndex => $item)
                                                            <div class="welcome-item border p-3 mb-3 position-relative">
                                                                <input type="hidden" name="tabs[0][sections][{{$sIndex}}][items][{{$iIndex}}][id]" value="{{ $item['id'] ?? '' }}">
                                                                <div class="d-flex align-items-start gap-2">
                                                                    <span class="badge bg-primary mt-2 item-number" style="min-width: 28px; min-height: 28px; line-height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                                        {{ $iIndex + 1 }}
                                                                    </span>
                                                                    <div class="flex-grow-1">
                                                                        <div class="row">
                                                                            <div class="col-md-12 mb-2">
                                                                                <label class="form-label">Title (Optional)</label>
                                                                                <input type="text" class="form-control"
                                                                                    name="tabs[0][sections][{{$sIndex}}][items][{{$iIndex}}][title]"
                                                                                    value="{{ $item['title'] ?? '' }}">
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <label class="form-label">Body <span class="text-danger">*</span></label>
                                                                                <textarea class="form-control"
                                                                                    name="tabs[0][sections][{{$sIndex}}][items][{{$iIndex}}][body]"
                                                                                    rows="2" required>{{ $item['body'] ?? '' }}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-welcome-item mt-2" style="cursor: pointer;" title="Remove bullet point">
                                                                        <i class="fe fe-trash-2"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <p class="text-muted mb-0 text-center py-3 welcome-empty">No bullet points yet. Click "Add Bullet Point" below.</p>
                                                        @endforelse

                                                        <button type="button" class="btn btn-outline-primary btn-sm add-welcome-item mt-2" data-tab="0" data-section="{{$sIndex}}">
                                                            <i class="fe fe-plus me-1"></i> Add Bullet Point
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0 text-center py-3" id="welcome-empty-state">No sections yet. Click "Add Section" below to create one.</p>
                                        @endforelse
                                        </div>

                                        <button type="button" class="btn btn-outline-primary add-welcome-section mt-2">
                                            <i class="fe fe-plus me-1"></i> Add Section
                                        </button>
                                    </div>
                                </div>

                                <!-- HARMONIC BUILDER TAB -->
                                <div class="tab-pane" id="harmonic_builder">
                                    <input type="hidden" name="tabs[1][tab]" value="harmonic_builder">

                                    <div class="form-group">
                                        <label class="form-label">Heading</label>
                                        <input type="text" class="form-control" name="tabs[1][heading]"
                                            value="{{ old('tabs.1.heading', $harmonicTab->heading ?? 'Composition Builder') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Intro Text</label>
                                        <textarea class="form-control" name="tabs[1][intro_text]" rows="3">{{ old('tabs.1.intro_text', $harmonicTab->intro_text ?? '') }}</textarea>
                                    </div>

                                    <div class="mt-4">
                                        <h4 class="card-title mb-3 text-primary">Sections & Items</h4>

                                        <div id="harmonic-sections-container">
                                        @php
                                            $harmonicSections = old('tabs.1.sections', $harmonicTab ? $harmonicTab->sections->toArray() : []);
                                        @endphp

                                        @forelse($harmonicSections as $sIndex => $section)
                                            <div class="section-group border border-primary p-4 mb-4 harmonic-section">
                                                <input type="hidden" name="tabs[1][sections][{{$sIndex}}][id]" value="{{ $section['id'] ?? '' }}">

                                                <div class="d-flex justify-content-between align-items-start mb-4">
                                                    <div class="flex-grow-1 me-3">
                                                        <label class="form-label text-primary fw-bold">Section Heading</label>
                                                        <input type="text" class="form-control fw-bold"
                                                            name="tabs[1][sections][{{$sIndex}}][heading]"
                                                            value="{{ $section['heading'] ?? '' }}" required>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-harmonic-section mt-4" style="cursor: pointer;" title="Remove section">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>

                                                <div class="card shadow-none border">
                                                    <div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">
                                                        <h5 class="card-title mb-0">Items</h5>
                                                    </div>
                                                    <div class="card-body p-3 harmonic-items-container" data-tab="1" data-section="{{$sIndex}}">
                                                        @php
                                                            $items = $section['items'] ?? [];
                                                        @endphp

                                                        @forelse($items as $iIndex => $item)
                                                            <div class="harmonic-item border p-3 mb-3 position-relative">
                                                                <input type="hidden" name="tabs[1][sections][{{$sIndex}}][items][{{$iIndex}}][id]" value="{{ $item['id'] ?? '' }}">
                                                                
                                                                <div class="row">
                                                                    <div class="col-md-12 mb-3">
                                                                        <label class="form-label">Title</label>
                                                                        <input type="text" class="form-control" name="tabs[1][sections][{{$sIndex}}][items][{{$iIndex}}][title]" value="{{ $item['title'] ?? '' }}" required>
                                                                    </div>
                                                                    <div class="col-md-12 mb-3">
                                                                        <label class="form-label">Body</label>
                                                                        <textarea class="form-control" name="tabs[1][sections][{{$sIndex}}][items][{{$iIndex}}][body]" rows="3">{{ $item['body'] ?? '' }}</textarea>
                                                                    </div>
                                                                </div>

                                                                <!-- Images Block -->
                                                                <div class="card shadow-none border mb-3">
                                                                    <div class="card-header bg-light p-2"><h6 class="mb-0">Images</h6></div>
                                                                    <div class="card-body p-2">
                                                                        @php $images = $item['images'] ?? []; @endphp
                                                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                                                        @forelse($images as $imgIndex => $img)
                                                                            @if(isset($img['src']))
                                                                            <div class="position-relative existing-image-block" style="width: 80px; height: 80px;">
                                                                                <img src="{{ asset('storage/' . $img['src']) }}" class="img-thumbnail w-100 h-100" style="object-fit: cover;">
                                                                                <input type="hidden" name="tabs[1][sections][{{$sIndex}}][items][{{$iIndex}}][existing_images][]" value="{{ $img['src'] }}">
                                                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 remove-existing-image" style="width: 20px; height: 20px; line-height: 1;"><i class="fe fe-x" style="font-size: 12px;"></i></button>
                                                                            </div>
                                                                            @endif
                                                                        @empty
                                                                        @endforelse
                                                                        </div>
                                                                        <label class="form-label" style="font-size: 12px;">Upload New Images</label>
                                                                        <input type="file" class="form-control form-control-sm" name="tabs[1][sections][{{$sIndex}}][items][{{$iIndex}}][new_images][]" multiple accept="image/*">
                                                                    </div>
                                                                </div>

                                                                <!-- Keys Block -->
                                                                <div class="card shadow-none border mb-3">
                                                                    <div class="card-header bg-light p-2"><h6 class="mb-0">Numbered Marker Keys</h6></div>
                                                                    <div class="card-body p-2 harmonic-keys-container" data-section="{{$sIndex}}" data-item="{{$iIndex}}">
                                                                        @php $keys = $item['keys'] ?? []; @endphp
                                                                        @forelse($keys as $kIndex => $kText)
                                                                            <div class="harmonic-key-block d-flex gap-2 mb-2">
                                                                                <input type="text" class="form-control form-control-sm" name="tabs[1][sections][{{$sIndex}}][items][{{$iIndex}}][keys][]" value="{{ $kText }}" placeholder="Key label">
                                                                                <button type="button" class="btn btn-sm btn-outline-danger remove-key"><i class="fe fe-x"></i></button>
                                                                            </div>
                                                                        @empty
                                                                        @endforelse
                                                                        <button type="button" class="btn btn-sm btn-outline-secondary add-key">Add Key</button>
                                                                    </div>
                                                                </div>

                                                                <button type="button" class="btn btn-outline-danger btn-sm remove-harmonic-item mt-2 w-100">
                                                                    <i class="fe fe-trash-2"></i> Remove Item
                                                                </button>
                                                            </div>
                                                        @empty
                                                            <p class="text-muted mb-0 text-center py-3 harmonic-empty">No items yet. Click "Add Item" below.</p>
                                                        @endforelse

                                                        <button type="button" class="btn btn-outline-primary btn-sm add-harmonic-item mt-2" data-section="{{$sIndex}}">
                                                            <i class="fe fe-plus me-1"></i> Add Item
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0 text-center py-3" id="harmonic-empty-state">No sections yet. Click "Add Section" below.</p>
                                        @endforelse
                                        </div>

                                        <button type="button" class="btn btn-outline-primary add-harmonic-section mt-2">
                                            <i class="fe fe-plus me-1"></i> Add Section
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-4">
                        <button type="submit" class="btn btn-success btn-lg px-5">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var ready = function() {
        if (typeof jQuery === 'undefined') {
            setTimeout(ready, 100);
            return;
        }
        jQuery(function($) {

            // ---- HARMONIC BUILDER: Add Section ----
            var harmonicSectionCounter = {{ $harmonicTab ? $harmonicTab->sections->count() : 0 }};
            $(document).on('click', '.add-harmonic-section', function() {
                var sIndex = harmonicSectionCounter++;
                $('#harmonic-empty-state').remove();

                var template = '<div class="section-group border border-primary p-4 mb-4 harmonic-section">' +
                    '<input type="hidden" name="tabs[1][sections][' + sIndex + '][id]" value="">' +
                    '<div class="d-flex justify-content-between align-items-start mb-4">' +
                        '<div class="flex-grow-1 me-3">' +
                            '<label class="form-label text-primary fw-bold">Section Heading</label>' +
                            '<input type="text" class="form-control fw-bold" name="tabs[1][sections][' + sIndex + '][heading]" required>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm remove-harmonic-section mt-4"><i class="fe fe-trash-2"></i></button>' +
                    '</div>' +
                    '<div class="card shadow-none border">' +
                        '<div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">' +
                            '<h5 class="card-title mb-0">Items</h5>' +
                        '</div>' +
                        '<div class="card-body p-3 harmonic-items-container" data-tab="1" data-section="' + sIndex + '">' +
                            '<p class="text-muted mb-0 text-center py-3 harmonic-empty">No items yet. Click "Add Item" below.</p>' +
                            '<button type="button" class="btn btn-outline-primary btn-sm add-harmonic-item mt-2" data-section="' + sIndex + '">' +
                                '<i class="fe fe-plus me-1"></i> Add Item' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                $('#harmonic-sections-container').append(template);
            });

            $(document).on('click', '.remove-harmonic-section', function() {
                $(this).closest('.harmonic-section').remove();
            });

            // ---- HARMONIC BUILDER: Add Item ----
            $(document).on('click', '.add-harmonic-item', function() {
                var container = $(this).closest('.harmonic-items-container');
                var sIndex = container.data('section');
                var items = container.find('.harmonic-item');
                var iIndex = items.length;

                container.find('.harmonic-empty').remove();

                var template = '<div class="harmonic-item border p-3 mb-3 position-relative">' +
                    '<input type="hidden" name="tabs[1][sections][' + sIndex + '][items][' + iIndex + '][id]" value="">' +
                    '<div class="row">' +
                        '<div class="col-md-12 mb-3">' +
                            '<label class="form-label">Title</label>' +
                            '<input type="text" class="form-control" name="tabs[1][sections][' + sIndex + '][items][' + iIndex + '][title]" required>' +
                        '</div>' +
                        '<div class="col-md-12 mb-3">' +
                            '<label class="form-label">Body</label>' +
                            '<textarea class="form-control" name="tabs[1][sections][' + sIndex + '][items][' + iIndex + '][body]" rows="3"></textarea>' +
                        '</div>' +
                    '</div>' +
                    '<div class="card shadow-none border mb-3">' +
                        '<div class="card-header bg-light p-2"><h6 class="mb-0">Images</h6></div>' +
                        '<div class="card-body p-2">' +
                            '<label class="form-label" style="font-size: 12px;">Upload Images</label>' +
                            '<input type="file" class="form-control form-control-sm" name="tabs[1][sections][' + sIndex + '][items][' + iIndex + '][new_images][]" multiple accept="image/*">' +
                        '</div>' +
                    '</div>' +
                    '<div class="card shadow-none border mb-3">' +
                        '<div class="card-header bg-light p-2"><h6 class="mb-0">Numbered Marker Keys</h6></div>' +
                        '<div class="card-body p-2 harmonic-keys-container" data-section="' + sIndex + '" data-item="' + iIndex + '">' +
                            '<button type="button" class="btn btn-sm btn-outline-secondary add-key">Add Key</button>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-harmonic-item mt-2 w-100"><i class="fe fe-trash-2"></i> Remove Item</button>' +
                '</div>';

                $(template).insertBefore(this);
            });

            $(document).on('click', '.remove-harmonic-item', function() {
                $(this).closest('.harmonic-item').remove();
            });

            // ---- HARMONIC BUILDER: Remove Existing Image ----
            $(document).on('click', '.remove-existing-image', function() {
                $(this).closest('.existing-image-block').remove();
            });

            // ---- HARMONIC BUILDER: Add Key ----
            $(document).on('click', '.add-key', function() {
                var container = $(this).closest('.harmonic-keys-container');
                var sIndex = container.data('section');
                var iIndex = container.data('item');

                var template = '<div class="harmonic-key-block d-flex gap-2 mb-2">' +
                    '<input type="text" class="form-control form-control-sm" name="tabs[1][sections][' + sIndex + '][items][' + iIndex + '][keys][]" placeholder="Key label">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-key"><i class="fe fe-x"></i></button>' +
                '</div>';
                $(template).insertBefore(this);
            });

            $(document).on('click', '.remove-key', function() {
                $(this).closest('.harmonic-key-block').remove();
            });

            // ---- WELCOME TAB: Add Section (with persistent counter) ----
            var welcomeSectionCounter = {{ $welcomeTab ? $welcomeTab->sections->count() : 0 }};

            $(document).on('click', '.add-welcome-section', function() {
                var container = $('#welcome-sections-container');
                var newIndex = welcomeSectionCounter++;

                $('#welcome-empty-state').remove();

                var template = '<div class="section-group border border-primary p-4 mb-4 welcome-section">' +
                    '<input type="hidden" name="tabs[0][sections][' + newIndex + '][id]" value="">' +
                    '<div class="d-flex justify-content-between align-items-start mb-4">' +
                        '<div class="flex-grow-1 me-3">' +
                            '<label class="form-label text-primary fw-bold">Section Heading</label>' +
                            '<input type="text" class="form-control fw-bold" name="tabs[0][sections][' + newIndex + '][heading]" required>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm remove-welcome-section mt-4" style="cursor: pointer;" title="Remove section">' +
                            '<i class="fe fe-trash-2"></i>' +
                        '</button>' +
                    '</div>' +
                    '<div class="card shadow-none border">' +
                        '<div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">' +
                            '<h5 class="card-title mb-0">Bullet Points</h5>' +
                        '</div>' +
                        '<div class="card-body p-3 welcome-items-container" data-tab="0" data-section="' + newIndex + '">' +
                            '<p class="text-muted mb-0 text-center py-3 welcome-empty">No bullet points yet. Click "Add Bullet Point" below.</p>' +
                            '<button type="button" class="btn btn-outline-primary btn-sm add-welcome-item mt-2" data-tab="0" data-section="' + newIndex + '">' +
                                '<i class="fe fe-plus me-1"></i> Add Bullet Point' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                container.append(template);
            });

            // ---- WELCOME TAB: Add Bullet Point ----
            $(document).on('click', '.add-welcome-item', function() {
                var container = $(this).closest('.welcome-items-container');
                var tabIndex = container.data('tab');
                var sectionIndex = container.data('section');
                var items = container.find('.welcome-item');
                var newIndex = items.length;

                var itemCounterKey = 'welcome_item_counter_' + sectionIndex;
                if (typeof window[itemCounterKey] === 'undefined') {
                    window[itemCounterKey] = newIndex;
                }
                var itemIndex = window[itemCounterKey]++;

                container.find('.welcome-empty').remove();

                var template = '<div class="welcome-item border p-3 mb-3 position-relative">' +
                    '<input type="hidden" name="tabs[' + tabIndex + '][sections][' + sectionIndex + '][items][' + itemIndex + '][id]" value="">' +
                    '<div class="d-flex align-items-start gap-2">' +
                        '<span class="badge bg-primary mt-2 item-number" style="min-width: 28px; min-height: 28px; line-height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">' + (newIndex + 1) + '</span>' +
                        '<div class="flex-grow-1">' +
                            '<div class="row">' +
                                '<div class="col-md-12 mb-2">' +
                                    '<label class="form-label">Title (Optional)</label>' +
                                    '<input type="text" class="form-control" name="tabs[' + tabIndex + '][sections][' + sectionIndex + '][items][' + itemIndex + '][title]">' +
                                '</div>' +
                                '<div class="col-md-12">' +
                                    '<label class="form-label">Body <span class="text-danger">*</span></label>' +
                                    '<textarea class="form-control" name="tabs[' + tabIndex + '][sections][' + sectionIndex + '][items][' + itemIndex + '][body]" rows="2" required></textarea>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm remove-welcome-item mt-2" style="cursor: pointer;" title="Remove bullet point">' +
                            '<i class="fe fe-trash-2"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';

                $(template).insertBefore(this);
            });

            // ---- WELCOME TAB: Remove Section ----
            $(document).on('click', '.remove-welcome-section', function() {
                $(this).closest('.welcome-section').remove();
            });

            // ---- HARMONIC BUILDER: Remove Bullet Point ----
            $(document).on('click', '.remove-harmonic-item', function() {
                $(this).closest('.harmonic-item').remove();
            });

            // ---- WELCOME TAB: Remove Bullet Point ----
            $(document).on('click', '.remove-welcome-item', function() {
                $(this).closest('.welcome-item').remove();
            });

        });
    };
    ready();
})();
</script>

@endsection
