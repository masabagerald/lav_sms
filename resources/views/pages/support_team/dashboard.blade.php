@extends('layouts.master')
@section('page_title', 'Dashboard')

@push('styles')
<style>
    /* ---- Filter bar ---- */
    .dash-filters { display: flex; flex-wrap: wrap; gap: .75rem 1.25rem; align-items: flex-end; }
    .dash-filters > div { min-width: 190px; flex: 1 1 190px; }
    .dash-filters label {
        font-size: .72rem; text-transform: uppercase; letter-spacing: .05em;
        color: #777; margin-bottom: .25rem; display: block; font-weight: 600;
    }
    @media (max-width: 576px) { .dash-filters > div { min-width: 100%; } }

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

    /* ---- Calendar ---- */
    .cal-legend { display: flex; gap: .4rem .9rem; flex-wrap: wrap; align-items: center; }
    .cal-legend .chip {
        display: inline-flex; align-items: center; font-size: .72rem; font-weight: 600;
        color: #555; white-space: nowrap;
    }
    .cal-legend .dot {
        width: .65rem; height: .65rem; border-radius: 50%;
        display: inline-block; margin-right: .35rem;
    }
    #school-calendar { padding-top: .25rem; }
    #school-calendar .fc-toolbar h2 { font-size: 1.15rem; font-weight: 600; }
    #school-calendar .fc-button {
        background: #f5f5f5; border: 1px solid #ddd; color: #555;
        box-shadow: none; text-transform: capitalize; border-radius: .35rem;
        padding: .3rem .7rem; font-size: .8rem;
    }
    #school-calendar .fc-button:hover,
    #school-calendar .fc-state-active { background: #1e88e5 !important; border-color: #1e88e5 !important; color: #fff !important; }
    #school-calendar th.fc-day-header {
        padding: .45rem 0; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em;
        color: #777; background: #fafafa; border-bottom: 1px solid #eee;
    }
    #school-calendar .fc-day-number { font-size: .78rem; color: #666; padding: 4px 6px; }
    #school-calendar .fc-today { background: rgba(30, 136, 229, .07) !important; }
    #school-calendar .fc-today .fc-day-number { color: #1565c0; font-weight: 700; }
    #school-calendar .fc-event {
        border: none; border-radius: .3rem; font-size: .72rem; font-weight: 600;
        padding: .12rem .4rem; cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,.18);
    }
    #school-calendar .fc-more-cell a { font-size: .7rem; }
</style>
@endpush

@section('content')

@if(Qs::userIsTeamSA())
<!-- Filters -->
<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title"><i class="icon-filter4 mr-2 text-primary"></i>Dashboard Filters</h5>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body py-3">
        @php $current_session = Qs::getCurrentSession(); @endphp
        <form method="GET" action="{{ route('dashboard') }}" id="dashboard-filters">
            <div class="dash-filters">
                <div>
                    <label for="f-year">Academic Session</label>
                    <select name="year" id="f-year" class="form-control select" data-placeholder="Select session">
                        @foreach($years ?? [] as $yr)
                            <option value="{{ $yr }}" {{ ($selected_year ?? '') === $yr ? 'selected' : '' }}>
                                {{ $yr }}{{ $yr === $current_session ? ' (current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f-class">Class</label>
                    <select name="class_id" id="f-class" class="form-control select" data-placeholder="All classes">
                        <option value="">All Classes</option>
                        @foreach(($my_classes ?? collect()) as $mc)
                            <option value="{{ $mc->id }}" {{ ($selected_class ?? 0) == $mc->id ? 'selected' : '' }}>{{ $mc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f-term">Term</label>
                    <select name="term" id="f-term" class="form-control select" data-placeholder="All terms">
                        <option value="">All Terms</option>
                        <option value="1" {{ ($selected_term ?? 0) == 1 ? 'selected' : '' }}>First Term</option>
                        <option value="2" {{ ($selected_term ?? 0) == 2 ? 'selected' : '' }}>Second Term</option>
                        <option value="3" {{ ($selected_term ?? 0) == 3 ? 'selected' : '' }}>Third Term</option>
                    </select>
                </div>
                <div class="d-flex">
                    <button type="submit" class="btn btn-primary mr-1">Apply <i class="icon-circle-right2 ml-1"></i></button>
                    <a href="{{ route('dashboard') }}" class="btn btn-light" title="Reset to current session">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- /filters -->

<!-- KPI cards -->
<div class="row mt-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body kpi-card bg-blue-400 text-white">
            <h3 class="mb-0">{{ $total_students ?? 0 }}</h3>
            <span class="kpi-sub d-block">Total Students</span>
            <span class="kpi-badge mt-2"><i class="icon-plus2 mr-1"></i>{{ $new_students ?? 0 }} admitted {{ $selected_year ?? '' }}</span>
            <i class="kpi-icon icon-users4"></i>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body kpi-card bg-success-400 text-white">
            <h3 class="mb-0">UGX {{ number_format($fees_collected ?? 0) }}</h3>
            <span class="kpi-sub d-block">Fees Collected ({{ $selected_year ?? '' }})</span>
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

@if(Qs::userIsTeamSA())
<!-- Quick actions -->
<div class="d-flex flex-wrap mt-3">
    @if(Qm::enabled('students'))
        <a href="{{ route('students.create') }}" class="btn btn-light btn-sm mr-2 mb-2">
            <i class="icon-plus2 text-primary mr-1"></i> Admit Student
        </a>
    @endif
    @if(Qm::enabled('finance'))
        <a href="{{ route('payments.manage') }}" class="btn btn-light btn-sm mr-2 mb-2">
            <i class="icon-cash2 text-success-400 mr-1"></i> Record Payment
        </a>
    @endif
    @if(Qm::enabled('examinations'))
        <a href="{{ route('marks.index') }}" class="btn btn-light btn-sm mr-2 mb-2">
            <i class="icon-pencil text-indigo-400 mr-1"></i> Marks Entry
        </a>
    @endif
    @if(Qm::enabled('reports'))
        <a href="{{ route('reports.index') }}" class="btn btn-light btn-sm mb-2">
            <i class="icon-statistics text-warning-400 mr-1"></i> Payment Reports
        </a>
    @endif
</div>
@endif
<!-- /quick actions -->

<!-- Charts -->
<div class="row mt-3">
    <div class="col-lg-7">
        <div class="card chart-card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title"><i class="icon-cash2 mr-2 text-success-400"></i>Fee Collection — {{ $trend_period ?? '' }}</h5>
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
                <h5 class="card-title"><i class="icon-users4 mr-2 text-blue-400"></i>Students by Class{{ ($selected_class ?? 0) ? ' — ' . (optional(($my_classes ?? collect())->firstWhere('id', $selected_class))->name ?? '') : '' }}</h5>
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
                                    No payments recorded for session {{ $selected_year ?? '' }} yet.
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

{{--School Activity Calendar Begins--}}
<div class="card mt-3">
    <div class="card-header header-elements-inline">
        <h5 class="card-title"><i class="icon-calendar52 mr-2 text-primary"></i>School Activity Calendar — {{ $selected_year ?? '' }}</h5>
        <div class="header-elements">
            <div class="cal-legend mr-3">
                <span class="chip"><span class="dot" style="background:#43a047"></span>Fee collections</span>
                <span class="chip"><span class="dot" style="background:#1e88e5"></span>New admissions</span>
            </div>
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>

    <div class="card-body">
        <div id="school-calendar"></div>
    </div>
</div>
{{--School Activity Calendar Ends--}}

@endsection

@section('scripts')
<script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
<script>
(function () {
    // ---- Filters: apply instantly on selection ----
    $('#dashboard-filters select').on('change', function () {
        this.form.submit();
    });

    // ================= Charts =================
    if (typeof echarts === 'undefined' || !document.getElementById('fee-trend-chart')) return;

    var trendLabels = @json($trend_labels ?? []);
    var trendData   = @json($trend_data ?? []);
    var selClassId  = {{ (int) ($selected_class ?? 0) }};
    var classes     = @json(($students_by_class ?? collect())->map(function ($c) {
        return ['id' => $c->id, 'name' => $c->name, 'total' => $c->student_record_count];
    }));
    var genders     = @json($by_gender ?? collect());

    // Fee collection trend
    echarts.init(document.getElementById('fee-trend-chart')).setOption({
        grid: { left: 10, right: 20, top: 35, bottom: 25, containLabel: true },
        tooltip: { trigger: 'axis', valueFormatter: function (v) { return 'UGX ' + Number(v).toLocaleString(); } },
        xAxis: { type: 'category', data: trendLabels, axisLine: { lineStyle: { color: '#ccc' } }, axisLabel: { rotate: 45 } },
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

    // Students by class (selected class highlighted)
    var classNames = classes.map(function (c) { return c.name; });
    echarts.init(document.getElementById('class-chart')).setOption({
        grid: { left: 10, right: 30, top: 15, bottom: 10, containLabel: true },
        tooltip: { trigger: 'axis' },
        xAxis: { type: 'value', splitLine: { lineStyle: { color: '#eee' } } },
        yAxis: { type: 'category', data: classNames, axisLine: { lineStyle: { color: '#ccc' } } },
        series: [{
            name: 'Students',
            type: 'bar',
            barMaxWidth: 22,
            itemStyle: { borderRadius: [0, 6, 6, 0] },
            label: { show: true, position: 'right', color: '#666', fontSize: 11 },
            data: classes.map(function (c) {
                return {
                    value: c.total,
                    itemStyle: { color: (selClassId && c.id === selClassId) ? '#fb8c00' : '#1e88e5' }
                };
            })
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

<script>
(function () {
    // ================= School Activity Calendar =================
    if (!$().fullCalendar || !document.getElementById('school-calendar')) return;

    var calEvents = @json($calendar_events ?? []);
    var selectedYear = @json($selected_year ?? '');
    var currentSession = @json(Qs::getCurrentSession());

    // For past sessions, open on the most recent activity instead of today
    var defaultDate = undefined;
    if (calEvents.length && selectedYear && selectedYear !== currentSession) {
        var latest = calEvents.reduce(function (a, b) { return (a.start > b.start ? a : b); });
        if (latest.start.substring(0, 7) !== moment().format('YYYY-MM')) {
            defaultDate = latest.start;
        }
    }

    $('#school-calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay'
        },
        defaultDate: defaultDate,
        events: calEvents,
        eventLimit: true,
        height: 'auto',
        isRTL: $('html').attr('dir') === 'rtl',
        eventRender: function (event, element) {
            element.attr('title', event.description ? event.title + '\n' + event.description : event.title);
        }
    });
})();
</script>
@endsection
