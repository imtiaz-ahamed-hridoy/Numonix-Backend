@extends('backend.app')

@section('title', 'Founder Programme Dashboard')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Founder Programme</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('founders.export') }}" class="btn btn-primary btn-icon text-white me-2">
                <span><i class="fe fe-download"></i></span> Export CSV
            </a>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Founders</li>
            </ol>
        </div>
    </div>
    {{-- PAGE-HEADER --}}

    {{-- Stats Row --}}
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-2">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold text-primary">{{ $stats['totalFounders'] }}</h3>
                            <p class="text-muted fs-13 mb-0">Total Founders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-2">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold text-success">{{ $stats['activeTrials'] }}</h3>
                            <p class="text-muted fs-13 mb-0">Active Trials</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-2">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold text-warning">{{ $stats['expiredTrials'] }}</h3>
                            <p class="text-muted fs-13 mb-0">Expired Trials</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-2">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold text-info">{{ $stats['converted'] }}</h3>
                            <p class="text-muted fs-13 mb-0">Converted (&pound;24.99)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-2">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold text-secondary">{{ $stats['conversionRate'] }}%</h3>
                            <p class="text-muted fs-13 mb-0">Conversion Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-2">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold text-dark">{{ $stats['reviewCompletionRate'] }}%</h3>
                            <p class="text-muted fs-13 mb-0">Review Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs and Table --}}
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('founders.index') }}">All Founders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') === 'active' ? 'active' : '' }}" href="{{ route('founders.index', ['status' => 'active']) }}">Active Trials</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') === 'expired' ? 'active' : '' }}" href="{{ route('founders.index', ['status' => 'expired']) }}">Expired</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') === 'converted' ? 'active' : '' }}" href="{{ route('founders.index', ['status' => 'converted']) }}">Converted</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">#</th>
                                    <th class="border-bottom-0">Name</th>
                                    <th class="border-bottom-0">Email</th>
                                    <th class="border-bottom-0">Trial Ends</th>
                                    <th class="border-bottom-0">Review</th>
                                    <th class="border-bottom-0">Converted</th>
                                    <th class="border-bottom-0">Day 25 Sent</th>
                                    <th class="border-bottom-0">Day 30 Sent</th>
                                    <th class="border-bottom-0">Tier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($founders as $founder)
                                <tr>
                                    <td>{{ $founder->id }}</td>
                                    <td>{{ $founder->name }}</td>
                                    <td>{{ $founder->email }}</td>
                                    <td>
                                        @if($founder->founder_trial_ends_at)
                                            <span class="{{ $founder->founder_trial_ends_at->isPast() ? 'text-danger' : 'text-success' }} fw-semibold">
                                                {{ $founder->founder_trial_ends_at->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($founder->founder_review_completed)
                                            <span class="badge bg-success-transparent text-success">Done</span>
                                        @else
                                            <span class="badge bg-light text-muted">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($founder->founder_converted_at)
                                            <span class="badge bg-primary-transparent text-primary">{{ $founder->founder_converted_at->format('d M Y') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $founder->founder_day25_sent_at?->format('d M Y') ?? '-' }}</td>
                                    <td>{{ $founder->founder_day30_sent_at?->format('d M Y') ?? '-' }}</td>
                                    <td><span class="badge bg-secondary-transparent text-secondary">{{ $founder->subscription_tier }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No founders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($founders->hasPages())
                    <div class="d-flex justify-content-end mt-4">
                        {{ $founders->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
