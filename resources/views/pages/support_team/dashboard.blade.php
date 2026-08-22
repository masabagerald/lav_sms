@extends('layouts.master')
@section('page_title', 'Dashboard')

@push('styles')
<style>
    .kpi-card { position: relative; overflow: hidden; border-radius: .6rem; }
    .kpi-card .kpi-icon {
        position: absolute; right: -12px; bottom: -18px;
        font-size: 5.2rem; opacity: .16;
    }
    .kpi-card h3 { font-size: 1.75rem; font-weight: 600; margin-bottom: .15rem; }
    .kpi-sub { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }
    .kpi-badge {
        display: inline-block; padding: .1rem .5rem; border-radius: 1rem;
        background: rgba(255,255,255,.25); font-size: .7rem; font-weight: 600;
    }
    .chart-card { min-height: 320px; }
    .payment-status { padding: .18rem .6rem; border-radius: 1rem; font-size: .72rem; font-weight: 600; }
    .status-paid     { background: #e8f5e9; color: #2e7d32; }
    .status-partial  { background: #fff3e0; color: #ef6c00; }
    .money { font-weight: 600; white-space: nowrap; }
</style>
@endpush

@section('content')

@if(Qs::userIsTeamSA())
<!-- KPI cards -->
<div class="row">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body kpi-card bg-blue-400 text-white">
            <h3 class="mb-0">{{ $total_students ?? 0 }}</h3>
            <span class="kpi-sub d-block">Total Students</span>
            <span class="kpi-badge mt-2"><i class="icon-plus2 mr-1"></i>{{ $new_students ?? 0 }} new this year</span>
            <i class="kpi-icon icon-users4"></i>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body kpi-card bg-success-400 text-white">
            <h3 class="mb-0">UGX {{ number_format($fees_collected ?? 0) }}</h3>
            <span class="kpi-sub d-block">Fees Collected ({{ $session ?? '' }})</span>
            <span class="kpi-badge mt-2">{{ $payments_count ?? 0 }} cleared payments</span>
            <i class="kpi-icon icon-cash2"></i>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body kpi-card bg-danger-400 text-white">
            <h3 class="mb-0">UGX {{ number_format($fees_outstanding ?? 0) }}</h3>
            <span class="kpi-sub d-block">Outstanding Balances</span>
            <span class="kpi-badge mt-2"><i class="icon-alarm mr-1"></i>follow-up needed</span>
            <i class="kpi-icon icon-statistics"></i>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        @php
            $teachers = isset($users) ? $users->where('user_type', 'teacher')->count() : 0;
            $parents  = isset($users) ? $users->where('user_type', 'parent')->count() : 0;
        @endphp
        <div class="card card-body kpi-card bg-indigo-400 text-white">
            <h3 class="mb-0">{{ $teachers }}</h3>
            <span class="kpi-sub d-block">Teaching Staff</span>
            <span class="kpi-badge mt-2"><i class="icon-users2 mr-1"></i>{{ $parents }} parents registered</span>
            <i class="kpi-icon icon-user-tie"></i>
        </div>
    </div>
</div>
<!-- /KPI cards -->

<!-- Charts -->
<div class="row mt-3">
    <div class="col-lg-7">
        <div class="card chart-card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title"><i class="icon-cash2 mr-2 text-success-400"></i>Fee Collection — Last 6 Months</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div id="fee-trend-chart" style="height: 270px;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card chart-card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title"><i class="icon-users4 mr-2 text-blue-400"></i>Students by Class</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div id="class-chart" style="height: 270px;"></div>
            </div>
        </div>
    </div>
</div>
<!-- /charts -->

<!-- Recent payments + gender split -->
<div class="row mt-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title"><i class="icon-file-text2 mr-2 text-indigo-400"></i>Recent Payments</h5>
                <div class="header-elements">
                    <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light">View all <i class="icon-circle-right2 ml-1"></i></a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Fee Type</th>
                            <th class="text-right">Amount Paid</th>
                            <th class="text-center">Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_payments ?? [] as $pr)
                            <tr>
                                <td>
                                    <span class="font-weight-semibold">{{ $pr->student->name ?? '—' }}</span>
                                    <span class="d-block text-muted font-size-xs">{{ $pr->ref_no }}</span>
                                </td>
                                <td>{{ $pr->payment->title ?? '—' }}</td>
                                <td class="text-right money">UGX {{ number_format($pr->amt_paid) }}</td>
                                <td class="text-center">
                                    @if($pr->paid)
                                        <span class="payment-status status-paid">Fully Paid</span>
                                    @else
                                        <span class="payment-status status-partial" title="Balance: UGX {{ number_format($pr->balance) }}">Balance {{ number_format($pr->balance) }}</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ optional($pr->payment_date)->format('d M Y') ?? ($pr->created_at->format('d M Y')) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="icon-cash2 icon-2x d-block mb-2 opacity-40"></i>
                                    No payments recorded for session {{ $session }} yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card chart-card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title"><i class="icon-users2 mr-2 text-warning-400"></i>Gender Distribution</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div id="gender-chart" style="height: 270px;"></div>
            </div>
        </div>
    </div>
</div>
@endif
<!-- /recent payments -->

{{--Events Calendar Begins--}}
<div class="card mt-3">
    <div class="card-header header-elements-inline">
        <h5 class="card-title">School Events Calendar</h5>
     {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="fullcalendar-basic"></div>
    </div>
</div>
{{--Events Calendar Ends--}}

@endsection

@section('scripts')
<script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
<script>
(function () {
    if (typeof echarts === 'undefined' || !document.getElementById('fee-trend-chart')) return;

    var trendLabels = @json($trend_labels ?? []);
    var trendData   = @json($trend_data ?? []);
    var classes     = @json(($students_by_class ?? collect())->map(function ($c) {
        return ['name' => $c->name, 'total' => $c->student_record_count];
    }));
    var genders     = @json($by_gender ?? collect());

    // Fee collection trend
    echarts.init(document.getElementById('fee-trend-chart')).setOption({
        grid: { left: 10, right: 20, top: 35, bottom: 25, containLabel: true },
        tooltip: { trigger: 'axis', valueFormatter: function (v) { return 'UGX ' + Number(v).toLocaleString(); } },
        xAxis: { type: 'category', data: trendLabels, axisLine: { lineStyle: { color: '#ccc' } } },
        yAxis: { type: 'value', splitLine: { lineStyle: { color: '#eee' } } },
        series: [{
            name: 'Collected',
            type: 'bar',
            barMaxWidth: 42,
            itemStyle: { borderRadius: [6, 6, 0, 0], color: '#43a047' },
            label: { show: true, position: 'top', formatter: function (p) { return p.value > 0 ? (p.value / 1000) + 'k' : ''; }, color: '#666', fontSize: 11 },
            data: trendData
        }]
    });

    // Students by class
    var classNames = classes.map(function (c) { return c.name; });
    var classTotals = classes.map(function (c) { return c.total; });
    echarts.init(document.getElementById('class-chart')).setOption({
        grid: { left: 10, right: 30, top: 15, bottom: 10, containLabel: true },
        tooltip: { trigger: 'axis' },
        xAxis: { type: 'value', splitLine: { lineStyle: { color: '#eee' } } },
        yAxis: { type: 'category', data: classNames, axisLine: { lineStyle: { color: '#ccc' } } },
        series: [{
            type: 'bar',
            barMaxWidth: 22,
            itemStyle: { borderRadius: [0, 6, 6, 0], color: '#1e88e5' },
            label: { show: true, position: 'right', color: '#666', fontSize: 11 },
            data: classTotals
        }]
    });

    // Gender distribution
    var genderMap = { male: ['Male', '#1e88e5'], female: ['Female', '#e91e63'] };
    var genderData = Object.keys(genders).map(function (g) {
        var meta = genderMap[String(g).toLowerCase()] || [String(g).charAt(0).toUpperCase() + String(g).slice(1), '#90a4ae'];
        return { name: meta[0], value: genders[g], itemStyle: { color: meta[1] } };
    });
    echarts.init(document.getElementById('gender-chart')).setOption({
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
        legend: { bottom: 0, icon: 'circle' },
        series: [{
            type: 'pie',
            radius: ['45%', '68%'],
            center: ['50%', '45%'],
            label: { show: false },
            data: genderData
        }]
    });
})();
</script>
@endsection
