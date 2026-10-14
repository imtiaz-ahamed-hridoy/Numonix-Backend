@extends('backend.app')

@section('title', 'Guest Access Settings')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Access Control Settings</h1>
            <p class="text-muted">Manage how many lessons and modules your users can access across all subscription tiers.</p>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Access Control</li>
            </ol>
        </div>
    </div>
    {{-- PAGE-HEADER --}}

    {{-- OVERVIEW CARDS --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold">{{ $totalUsers }}</h3>
                            <p class="text-muted fs-13 mb-0">Total Users</p>
                        </div>
                        <div class="col col-auto">
                            <div class="counter-icon bg-primary dash ms-auto box-shadow-primary" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold">{{ $tier1Users }}</h3>
                            <p class="text-muted fs-13 mb-0">Foundation (Free) Users</p>
                        </div>
                        <div class="col col-auto">
                            <div class="counter-icon bg-warning dash ms-auto box-shadow-warning" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold">{{ $subscribedUsers }}</h3>
                            <p class="text-muted fs-13 mb-0">Subscribed Users</p>
                        </div>
                        <div class="col col-auto">
                            <div class="counter-icon bg-success dash ms-auto box-shadow-success" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2 fw-semibold">{{ $guestSessionsToday }}</h3>
                            <p class="text-muted fs-13 mb-0">Guest Sessions Today</p>
                        </div>
                        <div class="col col-auto">
                            <div class="counter-icon bg-danger dash ms-auto box-shadow-danger" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CATEGORY TIER 1 LIMITS --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Default Category Limits</h3>
                    <p class="text-muted mb-0 ms-3 fs-13">Set the default number of accessible items for each category</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('guest-access.update') }}" id="settingsForm">
                        @csrf

                        <div class="row">
                            {{-- TIER 1 Limits --}}
                            <div class="col-12 mb-2">
                                <h5 class="fw-semibold mb-1">Foundation (Free & Guests)</h5>
                                <p class="text-muted fs-13 mb-0">tier_1: Free lessons, 1st ear-training exercise, preview tools.</p>
                            </div>
                            @foreach([
                                'tier1_limit_lesson' => ['Lessons', 'lesson'],
                                'tier1_limit_core_theory' => ['Core Theory', 'core_theory'],
                                'tier1_limit_case_study' => ['Case Studies', 'case_study'],
                                'tier1_limit_fnh_architecture' => ['FNH Architecture', 'fnh_architecture'],
                                'tier1_limit_advanced_insight' => ['Advanced Insights', 'advanced_insight'],
                            ] as $key => [$label, $category])
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">{{ $label }}
                                        <span class="badge bg-light text-dark ms-1">{{ $category }}</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="{{ $key }}"
                                            value="{{ old($key, $settings[$key]) }}" min="0" max="100">
                                        <span class="input-group-text">items</span>
                                    </div>
                                    @error($key)
                                        <span class="text-danger fs-12">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach

                            {{-- TIER 2 Limits --}}
                            <div class="col-12 mb-2 mt-3">
                                <h5 class="fw-semibold mb-1">Tier 1</h5>
                                <p class="text-muted fs-13 mb-0">tier_2: Lessons 1–12, Harmonic Builder, all ear-training exercises. (Set to 99 for unlimited access)</p>
                            </div>
                            @foreach([
                                'tier2_limit_lesson' => ['Lessons', 'lesson'],
                                'tier2_limit_core_theory' => ['Core Theory', 'core_theory'],
                                'tier2_limit_case_study' => ['Case Studies', 'case_study'],
                                'tier2_limit_fnh_architecture' => ['FNH Architecture', 'fnh_architecture'],
                                'tier2_limit_advanced_insight' => ['Advanced Insights', 'advanced_insight'],
                            ] as $key => [$label, $category])
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">{{ $label }}
                                        <span class="badge bg-primary-transparent text-primary ms-1">{{ $category }}</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="{{ $key }}"
                                            value="{{ old($key, $settings[$key]) }}" min="0" max="100">
                                        <span class="input-group-text">items</span>
                                    </div>
                                    @error($key)
                                        <span class="text-danger fs-12">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach

                            {{-- TIER 3 Limits --}}
                            <div class="col-12 mb-2 mt-3">
                                <h5 class="fw-semibold mb-1">Tier 2</h5>
                                <p class="text-muted fs-13 mb-0">tier_3: Full access including Case Studies and Advanced Insights. (Set to 99 for unlimited access)</p>
                            </div>
                            @foreach([
                                'tier3_limit_lesson' => ['Lessons', 'lesson'],
                                'tier3_limit_core_theory' => ['Core Theory', 'core_theory'],
                                'tier3_limit_case_study' => ['Case Studies', 'case_study'],
                                'tier3_limit_fnh_architecture' => ['FNH Architecture', 'fnh_architecture'],
                                'tier3_limit_advanced_insight' => ['Advanced Insights', 'advanced_insight'],
                            ] as $key => [$label, $category])
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <label class="form-label fw-semibold">{{ $label }}
                                        <span class="badge bg-success-transparent text-success ms-1">{{ $category }}</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="{{ $key }}"
                                            value="{{ old($key, $settings[$key]) }}" min="0" max="100">
                                        <span class="input-group-text">items</span>
                                    </div>
                                    @error($key)
                                        <span class="text-danger fs-12">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach

                            {{-- Group-Level Premium Control (Case Studies & FNH Architecture) --}}
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                    <div>
                                        <label class="form-label fw-semibold mb-1">Group-Level Premium Control</label>
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-light text-dark">case_study</span>
                                            <span class="badge bg-light text-dark">fnh_architecture</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('learning-group.index') }}" class="btn btn-sm btn-outline-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                            View All Groups
                                        </a>
                                        <a href="{{ route('learning-group.create') }}" class="btn btn-sm btn-outline-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            Create Learning Group
                                        </a>
                                    </div>
                                </div>
                                <p class="text-muted fs-13 mb-3">Set access status at the group level. Foundation (Free) users can access all learnings in free groups. Tier 1 and Tier 2 require a subscription. Toggle the switches below to easily lock or unlock entire groups.</p>

                                @if($caseStudyGroups->isEmpty() && $fnhArchitectureGroups->isEmpty())
                                    <div class="alert alert-warning py-2 mb-0">
                                        <small>No groups found for Case Studies or FNH Architecture. <a href="{{ route('learning-group.create') }}" class="fw-semibold">Create groups first</a>.</small>
                                    </div>
                                @else
                                    {{-- Case Study groups --}}
                                    @if(!$caseStudyGroups->isEmpty())
                                        <h6 class="fw-semibold mb-2 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                            Case Studies
                                        </h6>
                                        <div class="row g-3 mb-4">
                                            @foreach($caseStudyGroups as $group)
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="card card-border h-100 shadow-none border {{ $group->is_premium ? 'border-danger' : 'border-success' }} mb-0">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <h6 class="fw-semibold mb-0">{{ $group->label }}</h6>
                                                                <span class="text-muted fs-11">{{ $group->learnings()->count() }} learning{{ $group->learnings()->count() !== 1 ? 's' : '' }}</span>
                                                            </div>
                                                            <div class="mt-2 pt-2 border-top">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="small fw-semibold {{ $group->is_premium ? 'text-danger' : 'text-success' }}">
                                                                        @if($group->is_premium) <i class="fa fa-lock"></i> @else <i class="fa fa-unlock"></i> @endif Foundation (Free)
                                                                    </span>
                                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="group_premium[{{ $group->id }}]"
                                                                            value="1" id="gprem_{{ $group->id }}"
                                                                            {{ $group->is_premium ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="small fw-semibold {{ $group->tier2_premium ? 'text-danger' : 'text-success' }}">
                                                                        @if($group->tier2_premium) <i class="fa fa-lock"></i> @else <i class="fa fa-unlock"></i> @endif Tier 1
                                                                    </span>
                                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="group_tier2_premium[{{ $group->id }}]"
                                                                            value="1" id="gt2prem_{{ $group->id }}"
                                                                            {{ $group->tier2_premium ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <span class="small fw-semibold {{ $group->tier3_premium ? 'text-danger' : 'text-success' }}">
                                                                        @if($group->tier3_premium) <i class="fa fa-lock"></i> @else <i class="fa fa-unlock"></i> @endif Tier 2
                                                                    </span>
                                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="group_tier3_premium[{{ $group->id }}]"
                                                                            value="1" id="gt3prem_{{ $group->id }}"
                                                                            {{ $group->tier3_premium ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- FNH Architecture groups --}}
                                    @if(!$fnhArchitectureGroups->isEmpty())
                                        <h6 class="fw-semibold mb-2 text-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            FNH Architecture
                                        </h6>
                                        <div class="row g-3">
                                            @foreach($fnhArchitectureGroups as $group)
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="card card-border h-100 shadow-none border {{ $group->is_premium ? 'border-danger' : 'border-success' }} mb-0">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <h6 class="fw-semibold mb-0">{{ $group->label }}</h6>
                                                                <span class="text-muted fs-11">{{ $group->learnings()->count() }} learning{{ $group->learnings()->count() !== 1 ? 's' : '' }}</span>
                                                            </div>
                                                            <div class="mt-2 pt-2 border-top">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="small fw-semibold {{ $group->is_premium ? 'text-danger' : 'text-success' }}">
                                                                        @if($group->is_premium) <i class="fa fa-lock"></i> @else <i class="fa fa-unlock"></i> @endif Foundation (Free)
                                                                    </span>
                                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="group_premium_fnh[{{ $group->id }}]"
                                                                            value="1" id="fnhprem_{{ $group->id }}"
                                                                            {{ $group->is_premium ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="small fw-semibold {{ $group->tier2_premium ? 'text-danger' : 'text-success' }}">
                                                                        @if($group->tier2_premium) <i class="fa fa-lock"></i> @else <i class="fa fa-unlock"></i> @endif Tier 1
                                                                    </span>
                                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="group_tier2_premium_fnh[{{ $group->id }}]"
                                                                            value="1" id="fnht2prem_{{ $group->id }}"
                                                                            {{ $group->tier2_premium ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <span class="small fw-semibold {{ $group->tier3_premium ? 'text-danger' : 'text-success' }}">
                                                                        @if($group->tier3_premium) <i class="fa fa-lock"></i> @else <i class="fa fa-unlock"></i> @endif Tier 2
                                                                    </span>
                                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="group_tier3_premium_fnh[{{ $group->id }}]"
                                                                            value="1" id="fnht3prem_{{ $group->id }}"
                                                                            {{ $group->tier3_premium ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif


                            </div>

                            {{-- TIER 1 Ear Training Access Control --}}
                            <div class="col-12 mb-3">
                                <div class="mb-2 mt-3">
                                    <h5 class="fw-semibold mb-1">Foundation (Free) Ear Training</h5>
                                    <p class="text-muted fs-13 mb-0">Toggle each difficulty to control Foundation (Free) user access (tier_1)</p>
                                </div>

                                @php
                                    $beginnerLevelsCount = $earTrainingStages->firstWhere(fn($s) => strtolower($s->title) === 'beginner')?->levels_count ?? 0;
                                    $intermediateLevelsCount = $earTrainingStages->firstWhere(fn($s) => strtolower($s->title) === 'intermediate')?->levels_count ?? 0;
                                    $advancedLevelsCount = $earTrainingStages->firstWhere(fn($s) => strtolower($s->title) === 'advanced')?->levels_count ?? 0;
                                @endphp

                                <div class="row g-3 mb-4">
                                    {{-- Beginner --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ $settings['tier1_ear_training_beginner_levels'] > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-success-transparent me-1">Beginner</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $beginnerLevelsCount }} level{{ $beginnerLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if($settings['tier1_ear_training_beginner_levels'] > 0)
                                                             <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier1_ear_training_beginner_levels"
                                                            value="1" id="et_beginner"
                                                            {{ $settings['tier1_ear_training_beginner_levels'] > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Intermediate --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ $settings['tier1_ear_training_intermediate_levels'] > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-warning-transparent me-1">Intermediate</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $intermediateLevelsCount }} level{{ $intermediateLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if($settings['tier1_ear_training_intermediate_levels'] > 0)
                                                             <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier1_ear_training_intermediate_levels"
                                                            value="1" id="et_intermediate"
                                                            {{ $settings['tier1_ear_training_intermediate_levels'] > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Advanced --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ $settings['tier1_ear_training_advanced_levels'] > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-danger-transparent me-1">Advanced</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $advancedLevelsCount }} level{{ $advancedLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if($settings['tier1_ear_training_advanced_levels'] > 0)
                                                             <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier1_ear_training_advanced_levels"
                                                            value="1" id="et_advanced"
                                                            {{ $settings['tier1_ear_training_advanced_levels'] > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TIER 2 Ear Training Access Control --}}
                                <div class="mb-2 mt-4">
                                    <h5 class="fw-semibold mb-1">Tier 1 Ear Training</h5>
                                    <p class="text-muted fs-13 mb-0">Toggle each difficulty to control Tier 1 (tier_2) user access</p>
                                </div>
                                <div class="row g-3">
                                    {{-- Beginner --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ $settings['tier2_ear_training_beginner_levels'] > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-success-transparent me-1">Beginner</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $beginnerLevelsCount }} level{{ $beginnerLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if($settings['tier2_ear_training_beginner_levels'] > 0)
                                                            <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier2_ear_training_beginner_levels"
                                                            value="1" id="t2_et_beginner"
                                                            {{ $settings['tier2_ear_training_beginner_levels'] > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Intermediate --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ $settings['tier2_ear_training_intermediate_levels'] > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-warning-transparent me-1">Intermediate</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $intermediateLevelsCount }} level{{ $intermediateLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if($settings['tier2_ear_training_intermediate_levels'] > 0)
                                                            <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier2_ear_training_intermediate_levels"
                                                            value="1" id="t2_et_intermediate"
                                                            {{ $settings['tier2_ear_training_intermediate_levels'] > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Advanced --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ $settings['tier2_ear_training_advanced_levels'] > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-danger-transparent me-1">Advanced</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $advancedLevelsCount }} level{{ $advancedLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if($settings['tier2_ear_training_advanced_levels'] > 0)
                                                            <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier2_ear_training_advanced_levels"
                                                            value="1" id="t2_et_advanced"
                                                            {{ $settings['tier2_ear_training_advanced_levels'] > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {{-- TIER 3 Ear Training Access Control --}}
                                <div class="mb-2 mt-4">
                                    <h5 class="fw-semibold mb-1">Tier 2 Ear Training</h5>
                                    <p class="text-muted fs-13 mb-0">Toggle each difficulty to control Tier 2 (tier_3) user access</p>
                                </div>
                                <div class="row g-3">
                                    {{-- Beginner --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ ($settings['tier3_ear_training_beginner_levels'] ?? 1) > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-success-transparent me-1">Beginner</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $beginnerLevelsCount }} level{{ $beginnerLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if(($settings['tier3_ear_training_beginner_levels'] ?? 1) > 0)
                                                            <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier3_ear_training_beginner_levels"
                                                            value="1" id="t3_et_beginner"
                                                            {{ ($settings['tier3_ear_training_beginner_levels'] ?? 1) > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Intermediate --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ ($settings['tier3_ear_training_intermediate_levels'] ?? 1) > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-warning-transparent me-1">Intermediate</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $intermediateLevelsCount }} level{{ $intermediateLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if(($settings['tier3_ear_training_intermediate_levels'] ?? 1) > 0)
                                                            <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier3_ear_training_intermediate_levels"
                                                            value="1" id="t3_et_intermediate"
                                                            {{ ($settings['tier3_ear_training_intermediate_levels'] ?? 1) > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Advanced --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card card-border h-100 shadow-none border {{ ($settings['tier3_ear_training_advanced_levels'] ?? 1) > 0 ? 'border-success' : 'border-danger' }} mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-0">
                                                        <span class="badge bg-danger-transparent me-1">Advanced</span>
                                                    </h6>
                                                    <span class="text-muted fs-11">{{ $advancedLevelsCount }} level{{ $advancedLevelsCount !== 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                    <span class="small fw-semibold">
                                                        @if(($settings['tier3_ear_training_advanced_levels'] ?? 1) > 0)
                                                            <span class="text-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                        @else
                                                            <span class="text-danger"><i class="fa fa-lock"></i> Locked</span>
                                                        @endif
                                                    </span>
                                                    <div class="form-check form-switch mb-0" style="min-height:unset;padding-left:2.5em;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="tier3_ear_training_advanced_levels"
                                                            value="1" id="t3_et_advanced"
                                                            {{ ($settings['tier3_ear_training_advanced_levels'] ?? 1) > 0 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">Hide Entire Categories</h5>
                        <p class="text-muted fs-13 mb-3">Select categories to completely hide from users in the mobile app. They won't even know these categories exist!</p>

                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Foundation (Free & Guest)</h6>
                            </div>
                            @foreach($categories as $catKey => $catLabel)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hidden_categories[]"
                                            value="{{ $catKey }}" id="hidden_{{ $catKey }}"
                                            {{ in_array($catKey, $settings['hidden_categories']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hidden_{{ $catKey }}">{{ $catLabel }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Tier 1</h6>
                            </div>
                            @foreach($categories as $catKey => $catLabel)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tier2_hidden_categories[]"
                                            value="{{ $catKey }}" id="t2_hidden_{{ $catKey }}"
                                            {{ in_array($catKey, $settings['tier2_hidden_categories'] ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="t2_hidden_{{ $catKey }}">{{ $catLabel }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Tier 2</h6>
                            </div>
                            @foreach($categories as $catKey => $catLabel)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tier3_hidden_categories[]"
                                            value="{{ $catKey }}" id="t3_hidden_{{ $catKey }}"
                                            {{ in_array($catKey, $settings['tier3_hidden_categories'] ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="t3_hidden_{{ $catKey }}">{{ $catLabel }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">How It Works</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <h6 class="fw-semibold mb-1">Category Limits</h6>
                        <p class="mb-0 fs-13">Set the number of items from each category that Foundation (Free) users can access. Items are unlocked in sort order. Set to 0 to fully lock a category.</p>
                    </div>
                    <div class="alert alert-warning mb-3">
                        <h6 class="fw-semibold mb-1">Hidden Categories</h6>
                        <p class="mb-0 fs-13">Hidden categories are completely invisible to the selected tiers - the category will completely disappear from their mobile app dashboard.</p>
                    </div>
                    <div class="alert alert-success mb-0">
                        <h6 class="fw-semibold mb-1">Per-Item Overrides</h6>
                        <p class="mb-0 fs-13">Use the table below to override access for individual items. This takes priority over category limits.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PER-ITEM LEARNING ACCESS CONTROL --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom" style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h3 class="card-title">Per-Item Learning Access Control</h3>
                        <p class="text-muted fs-13 mb-0">Override guest access for individual learning items</p>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark me-2">
                            <span class="text-success">●</span> Follow Category Default
                        </span>
                        <span class="badge bg-light text-dark me-2">
                            <span class="text-primary">●</span> Always Unlocked
                        </span>
                        <span class="badge bg-light text-dark">
                            <span class="text-danger">●</span> Always Locked
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom" id="learningsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Premium</th>
                                    <th style="min-width:200px;">Guest Access Override</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($learnings as $index => $learning)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $learning->title }}</td>
                                        <td>
                                            <span class="badge bg-{{ match($learning->category) {
                                                'lesson' => 'primary',
                                                'core_theory' => 'info',
                                                'case_study' => 'purple',
                                                'fnh_architecture' => 'warning',
                                                'advanced_insight' => 'danger',
                                                default => 'secondary'
                                            } }}-transparent">
                                                {{ $categories[$learning->category] ?? $learning->category }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $learning->status === 'active' ? 'success' : ($learning->status === 'coming_soon' ? 'warning' : 'secondary') }}-transparent">
                                                {{ ucfirst(str_replace('_', ' ', $learning->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($learning->is_premium)
                                                <span class="badge bg-warning-transparent">Premium</span>
                                            @else
                                                <span class="badge bg-light text-dark">Foundation (Free)</span>
                                            @endif
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm guest-access-toggle"
                                                data-type="learning" data-id="{{ $learning->id }}"
                                                style="min-width:170px;">
                                                <option value="null" {{ $learning->is_guest_accessible === null ? 'selected' : '' }}>
                                                    🟢 Follow Category Default
                                                </option>
                                                <option value="true" {{ $learning->is_guest_accessible === true ? 'selected' : '' }}>
                                                    🔵 Always Unlocked
                                                </option>
                                                <option value="false" {{ $learning->is_guest_accessible === false ? 'selected' : '' }}>
                                                    🔴 Always Locked
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PER-ITEM EAR TRAINING ACCESS CONTROL --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Per-Item Ear Training Access Control</h3>
                    <p class="text-muted fs-13 mb-0">Override guest access for individual ear training levels</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom" id="earTrainingTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Level</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th style="min-width:200px;">Guest Access Override</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($earTrainingLevels as $index => $level)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php $stageTitle = strtolower($level->stage->title ?? ''); @endphp
                                            <span class="badge bg-{{ match($stageTitle) {
                                                'beginner' => 'success',
                                                'intermediate' => 'warning',
                                                'advanced' => 'danger',
                                                default => 'secondary'
                                            } }}-transparent">
                                                {{ ucfirst($level->stage->title ?? 'Unknown') }} - Lvl {{ $level->level_number }}
                                            </span>
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($level->description, 60) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $level->is_active ? 'success' : 'warning' }}-transparent">
                                                {{ $level->is_active ? 'Published' : 'Draft' }}
                                            </span>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm guest-access-toggle"
                                                data-type="ear_training" data-id="{{ $level->id }}"
                                                style="min-width:170px;">
                                                <option value="Use Level Rule" {{ $level->guest_access_override === 'Use Level Rule' ? 'selected' : '' }}>
                                                    🟢 Follow Category Default
                                                </option>
                                                <option value="Unlocked" {{ $level->guest_access_override === 'Unlocked' ? 'selected' : '' }}>
                                                    🔵 Always Unlocked
                                                </option>
                                                <option value="Locked" {{ $level->guest_access_override === 'Locked' ? 'selected' : '' }}>
                                                    🔴 Always Locked
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        if ($('#learningsTable').length && !$.fn.DataTable.isDataTable('#learningsTable')) {
            $('#learningsTable').DataTable({
                order: [],
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>'
                },
            });
        }

        if ($('#earTrainingTable').length && !$.fn.DataTable.isDataTable('#earTrainingTable')) {
            $('#earTrainingTable').DataTable({
                order: [],
                pageLength: 25,
                responsive: true,
            });
        }

        // AJAX toggle for per-item guest access
        $(document).on('change', '.guest-access-toggle', function() {
            let select = $(this);
            let type = select.data('type');
            let id = select.data('id');
            let value = select.val();

            $.ajax({
                url: "{{ route('guest-access.toggle-item') }}",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { type: type, id: id, value: value },
                success: function(resp) {
                    if (resp.success) {
                        toastr.success(resp.message);
                    } else {
                        toastr.error(resp.message || 'Update failed');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to update access control');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush
