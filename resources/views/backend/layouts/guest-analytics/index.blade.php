@extends('backend.app')

@section('title', 'User Analytics')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">User & Guest Analytics</h1>
            <p class="text-muted">Comprehensive user behavior, engagement, and conversion insights</p>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">User Analytics</li>
            </ol>
        </div>
    </div>
    {{-- PAGE-HEADER --}}

    {{-- OVERVIEW STAT CARDS --}}
    <div class="row">
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(36,87,230,0.1)">
                            <i class="fa fa-eye" style="color:#2457E6;font-size:14px"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold text-primary">{{ $overview['guest_sessions_today'] }}</h4>
                            <p class="text-muted fs-12 mb-0">Guest Sessions Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(23,162,184,0.1)">
                            <i class="fa fa-calendar" style="color:#17a2b8;font-size:14px"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold text-info">{{ $overview['guest_sessions_week'] }}</h4>
                            <p class="text-muted fs-12 mb-0">Guest Sessions This Week</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(40,167,69,0.1)">
                            <i class="fa fa-chart-bar" style="color:#28a745;font-size:14px"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold text-success">{{ $overview['guest_sessions_month'] }}</h4>
                            <p class="text-muted fs-12 mb-0">Guest Sessions This Month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(255,165,2,0.1)">
                            <i class="fa fa-users" style="color:#ffa502;font-size:14px"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold text-warning">{{ $overview['unique_visitors_today'] }}</h4>
                            <p class="text-muted fs-12 mb-0">Unique Visitors Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(108,92,231,0.1)">
                            <i class="fa fa-file" style="color:#6c5ce7;font-size:14px"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold" style="color:#6c5ce7">{{ $overview['avg_pages_per_session'] }}</h4>
                            <p class="text-muted fs-12 mb-0">Avg Pages/Session</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(220,53,69,0.1)">
                            <i class="fa fa-lock" style="color:#dc3545;font-size:14px"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold text-danger">{{ $overview['lock_hit_rate'] }}%</h4>
                            <p class="text-muted fs-12 mb-0">Lock Hit Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 1: Daily Sessions + User Tier Distribution --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom" style="display:flex;justify-content:space-between;align-items:center;">
                    <h3 class="card-title">Daily Sessions (30 Days)</h3>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary chart-range-btn" data-days="7">7D</button>
                        <button type="button" class="btn btn-primary chart-range-btn active" data-days="30">30D</button>
                        <button type="button" class="btn btn-outline-primary chart-range-btn" data-days="60">60D</button>
                        <button type="button" class="btn btn-outline-primary chart-range-btn" data-days="90">90D</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="dailySessionsChart" style="height:320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">User Tier Distribution</h3>
                </div>
                <div class="card-body">
                    <div id="tierDistributionChart" style="height:320px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 2: Conversion Funnel + Registration Trend --}}
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Conversion Funnel (30 Days)</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <h5 class="fw-semibold text-primary">{{ $funnel['guest_sessions'] }}</h5>
                            <small class="text-muted">Guest Sessions</small>
                        </div>
                        <div class="col-4">
                            <h5 class="fw-semibold text-success">{{ $funnel['new_registrations'] }}</h5>
                            <small class="text-muted">New Registrations</small>
                        </div>
                        <div class="col-4">
                            <h5 class="fw-semibold text-warning">{{ $funnel['conversion_rate'] }}%</h5>
                            <small class="text-muted">Conversion Rate</small>
                        </div>
                    </div>
                    <div id="conversionFunnelChart" style="height:250px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Registration Trend (30 Days)</h3>
                </div>
                <div class="card-body">
                    <div id="registrationTrendChart" style="height:300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIVITY BY TIER --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Activity by User Tier (30 Days)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Tier</th>
                                    <th>Total Requests</th>
                                    <th>Unique Sessions</th>
                                    <th>Avg Requests/Session</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityByTier as $tier)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ match($tier['tier']) {
                                                'guest' => 'secondary',
                                                'tier_1' => 'success',
                                                'tier_2' => 'primary',
                                                default => 'light'
                                            } }}">
                                                {{ ucfirst(str_replace('_', ' ', $tier['tier'])) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($tier['total_requests']) }}</td>
                                        <td>{{ number_format($tier['unique_sessions']) }}</td>
                                        <td>{{ $tier['unique_sessions'] > 0 ? round($tier['total_requests'] / $tier['unique_sessions'], 1) : 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fa fa-info-circle me-1"></i>No activity data yet. Data will appear as users access the API.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENT ENGAGEMENT & LOCKED CONTENT TABLES --}}
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Most Popular Content (30 Days)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom" id="engagementTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Content</th>
                                    <th>Type</th>
                                    <th>Total Views</th>
                                    <th>Unique Viewers</th>
                                    <th>Guest Views</th>
                                    <th>Auth Views</th>
                                    <th>Lock Hits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contentEngagement as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['title'] ?? "#{$item['content_id']}" }}</td>
                                        <td>
                                            <span class="badge bg-{{ match($item['content_type']) {
                                                'learning' => 'primary',
                                                'ear_training' => 'info',
                                                'quiz' => 'warning',
                                                default => 'secondary'
                                            } }}-transparent">
                                                {{ ucfirst(str_replace('_', ' ', $item['content_type'])) }}
                                            </span>
                                        </td>
                                        <td><strong>{{ number_format($item['total_views']) }}</strong></td>
                                        <td>{{ number_format($item['unique_viewers']) }}</td>
                                        <td>{{ number_format($item['guest_views']) }}</td>
                                        <td>{{ number_format($item['auth_views']) }}</td>
                                        <td>
                                            @if($item['locked_hits'] > 0)
                                                <span class="text-danger fw-semibold">{{ $item['locked_hits'] }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa fa-info-circle me-1"></i>No engagement data yet. Data will appear once users start viewing content via the API.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Top Locked Content (Bounced)</h3>
                    <p class="text-muted fs-13 mb-0 ms-2">Content users tried but couldn't access</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom" id="lockedTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Content</th>
                                    <th>Type</th>
                                    <th>Attempts</th>
                                    <th>Unique Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topLocked as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['title'] ?? "#{$item['content_id']}" }}</td>
                                        <td>
                                            <span class="badge bg-{{ match($item['content_type']) {
                                                'learning' => 'primary',
                                                'ear_training' => 'info',
                                                'quiz' => 'warning',
                                                default => 'secondary'
                                            } }}-transparent">
                                                {{ ucfirst(str_replace('_', ' ', $item['content_type'])) }}
                                            </span>
                                        </td>
                                        <td><span class="text-danger fw-semibold">{{ number_format($item['locked_attempts']) }}</span></td>
                                        <td>{{ number_format($item['unique_users']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fa fa-info-circle me-1"></i>No locked content hits yet.
                                        </td>
                                    </tr>
                                @endforelse
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
    // Wait for DOM + all scripts to be fully loaded
    $(document).ready(function() {
        // Safety check: ensure ApexCharts is available
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts is not loaded');
            return;
        }

        // ---------- Daily Sessions Chart ----------
        var dailySessionsData = @json($dailySessions);

        var hasSessionData = dailySessionsData.guest_sessions.some(function(v) { return v > 0; }) ||
                             dailySessionsData.auth_sessions.some(function(v) { return v > 0; });

        var dailySessionsOptions = {
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: true },
                zoom: { enabled: true },
                fontFamily: 'inherit'
            },
            series: [
                { name: 'Guest Sessions', data: dailySessionsData.guest_sessions },
                { name: 'Auth Sessions', data: dailySessionsData.auth_sessions },
            ],
            xaxis: {
                categories: dailySessionsData.dates,
                labels: {
                    rotate: -45,
                    formatter: function(val) {
                        if (!val) return '';
                        var d = new Date(val + 'T00:00:00');
                        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }
                },
                tickAmount: 10
            },
            yaxis: {
                title: { text: 'Sessions' },
                min: 0,
                forceNiceScale: true,
                labels: { formatter: function(val) { return Math.floor(val); } }
            },
            colors: ['#6c5ce7', '#00b894'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
            stroke: { curve: 'smooth', width: 2 },
            tooltip: {
                x: {
                    formatter: function(val, opts) {
                        var date = dailySessionsData.dates[opts.dataPointIndex];
                        if (!date) return '';
                        var d = new Date(date + 'T00:00:00');
                        return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { position: 'top' },
            noData: {
                text: 'No session data yet',
                align: 'center',
                verticalAlign: 'middle',
                style: { color: '#6c757d', fontSize: '14px' }
            }
        };

        var dailySessionsChart = new ApexCharts(document.querySelector("#dailySessionsChart"), dailySessionsOptions);
        dailySessionsChart.render();

        // ---------- User Tier Distribution Chart ----------
        var tierData = @json($tierDistribution);
        var totalTierUsers = (tierData.tier_1 || 0) + (tierData.tier_2 || 0) + (tierData.tier_3 || 0);

        var tierOptions = {
            chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
            series: totalTierUsers > 0 ? [tierData.tier_1 || 0, tierData.tier_2 || 0, tierData.tier_3 || 0] : [1],
            labels: totalTierUsers > 0 ? ['Foundation (Free)', 'Tier 1', 'Tier 2'] : ['No Users Yet'],
            colors: totalTierUsers > 0 ? ['#ffa502', '#2ed573', '#3742fa'] : ['#e9ecef'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Users',
                                fontSize: '14px',
                                fontWeight: 600,
                                formatter: function() { return totalTierUsers; }
                            }
                        }
                    }
                }
            },
            legend: { position: 'bottom' },
            responsive: [{ breakpoint: 480, options: { chart: { height: 280 }, legend: { position: 'bottom' } } }]
        };

        var tierChart = new ApexCharts(document.querySelector("#tierDistributionChart"), tierOptions);
        tierChart.render();

        // ---------- Conversion Funnel Chart ----------
        var funnelData = @json($funnel);

        var funnelOptions = {
            chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{
                name: 'Users',
                data: [funnelData.guest_sessions, funnelData.total_registered, funnelData.subscribed_users]
            }],
            xaxis: { categories: ['Guest Sessions', 'Registered', 'Subscribed'] },
            colors: ['#6c5ce7', '#00b894', '#fdcb6e'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    distributed: true,
                    horizontal: false,
                    columnWidth: '60%',
                }
            },
            fill: {
                type: 'gradient',
                gradient: { shade: 'dark', type: 'vertical', shadeIntensity: 0.3, gradientToColors: ['#a29bfe', '#00b894', '#fdcb6e'], inverseColors: false, opacityFrom: 0.9, opacityTo: 0.7 }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) { return val.toLocaleString(); },
                style: { fontSize: '13px', fontWeight: 600 }
            },
            legend: { show: false },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: { formatter: function(val) { return Math.floor(val); } }
            },
            noData: {
                text: 'No data yet',
                align: 'center',
                verticalAlign: 'middle',
                style: { color: '#6c757d', fontSize: '14px' }
            }
        };

        var funnelChart = new ApexCharts(document.querySelector("#conversionFunnelChart"), funnelOptions);
        funnelChart.render();

        // ---------- Registration Trend Chart ----------
        var regData = @json($registrationTrend);

        var regOptions = {
            chart: { type: 'bar', height: 300, toolbar: { show: true }, fontFamily: 'inherit' },
            series: [{ name: 'New Registrations', data: regData.counts }],
            xaxis: {
                categories: regData.dates,
                labels: {
                    rotate: -45,
                    formatter: function(val) {
                        if (!val) return '';
                        var d = new Date(val + 'T00:00:00');
                        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }
                },
                tickAmount: 10
            },
            yaxis: {
                title: { text: 'Registrations' },
                min: 0,
                forceNiceScale: true,
                labels: { formatter: function(val) { return Math.floor(val); } }
            },
            colors: ['#00b894'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
            dataLabels: { enabled: false },
            noData: {
                text: 'No registrations yet',
                align: 'center',
                verticalAlign: 'middle',
                style: { color: '#6c757d', fontSize: '14px' }
            }
        };

        var regChart = new ApexCharts(document.querySelector("#registrationTrendChart"), regOptions);
        regChart.render();

        // ---------- Date Range Buttons ----------
        $('.chart-range-btn').on('click', function() {
            var days = $(this).data('days');
            var btn = $(this);

            $('.chart-range-btn').removeClass('btn-primary active').addClass('btn-outline-primary');
            btn.removeClass('btn-outline-primary').addClass('btn-primary active');

            $.ajax({
                url: "{{ route('guest-analytics.chart-data') }}",
                data: { days: days },
                success: function(resp) {
                    // Update daily sessions chart
                    dailySessionsChart.updateOptions({
                        xaxis: { categories: resp.daily_sessions.dates }
                    });
                    dailySessionsChart.updateSeries([
                        { name: 'Guest Sessions', data: resp.daily_sessions.guest_sessions },
                        { name: 'Auth Sessions', data: resp.daily_sessions.auth_sessions },
                    ]);

                    // Update registration trend
                    regChart.updateOptions({
                        xaxis: { categories: resp.registration_trend.dates }
                    });
                    regChart.updateSeries([{ name: 'New Registrations', data: resp.registration_trend.counts }]);

                    // Update tier distribution
                    var newTotal = (resp.tier_distribution.tier_1 || 0) + (resp.tier_distribution.tier_2 || 0) + (resp.tier_distribution.tier_3 || 0);
                    if (newTotal > 0) {
                        tierChart.updateOptions({ labels: ['Foundation (Free)', 'Tier 1', 'Tier 2'], colors: ['#ffa502', '#2ed573', '#3742fa'] });
                        tierChart.updateSeries([resp.tier_distribution.tier_1 || 0, resp.tier_distribution.tier_2 || 0, resp.tier_distribution.tier_3 || 0]);
                    }
                },
                error: function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load chart data');
                    }
                }
            });
        });

        // ---------- Initialize DataTables (only if there's real data) ----------
        var engagementHasData = $('#engagementTable tbody tr').length > 0 && !$('#engagementTable tbody tr td[colspan]').length;
        if (engagementHasData) {
            $('#engagementTable').DataTable({
                order: [[3, 'desc']],
                pageLength: 10,
                responsive: true,
                language: { emptyTable: 'No engagement data available' }
            });
        }

        var lockedHasData = $('#lockedTable tbody tr').length > 0 && !$('#lockedTable tbody tr td[colspan]').length;
        if (lockedHasData) {
            $('#lockedTable').DataTable({
                order: [[3, 'desc']],
                pageLength: 10,
                responsive: true,
                language: { emptyTable: 'No locked content data available' }
            });
        }
    });
</script>
@endpush
