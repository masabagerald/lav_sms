@extends('layouts.master')
@section('page_title', 'Finance Dashboard')

@include('pages.support_team.reports._styles')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap mb-3 rep-header">
    <div>
        <h5><i class="icon-cash2 mr-2 text-success-400"></i>Finance Dashboard</h5>
        <div class="sub">Session <b>{{ $f_session }}</b>@if($f_term) · Term {{ $f_term }}@endif @if($fc = $classes->firstWhere('id', $f_class)) · {{ $fc->name }}@endif</div>
    </div>
    <div class="rep-no-print">
        <button onclick="window.print()" class="btn btn-light btn-sm"><i class="icon-printer2 mr-1"></i> Print</button>
    </div>
</div>

{{-- ================= Filters ================= --}}
<div class="card rep-no-print mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end rep-filter">
            <div class="col-md-4 col-sm-6">
                <label>Academic Session</label>
                <select name="year" class="form-control select" data-placeholder="Session">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $f_session === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-3">
                <label>Term</label>
                <select name="term" class="form-control select">
                    <option value="">All</option>
                    @for($t = 1; $t <= 3; $t++)
                        <option value="{{ $t }}" {{ $f_term == $t ? 'selected' : '' }}>Term {{ $t }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 col-sm-6">
                <label>Class</label>
                <select name="my_class_id" class="form-control select" data-placeholder="All classes">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $f_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-3 text-sm-right">
                <button class="btn btn-primary btn-block">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="rep-print-title"><b>{{ Qs::getSetting('system_name') }}</b> — Finance Dashboard ({{ $f_session }})</div>

{{-- ================= KPI cards ================= --}}
<div class="row">
    <div class="col-6 col-xl-3 mb-3"><div class="kpi-card bg-blue-400">
        <div class="kpi-label">Expected Fees</div><div class="kpi-value">UGX {{ number_format($s['expected']) }}</div>
        <div class="kpi-foot">{{ number_format($s['enrolled']) }} enrolled students</div><i class="kpi-icon icon-file-text2"></i></div></div>
    <div class="col-6 col-xl-3 mb-3"><div class="kpi-card bg-success-400">
        <div class="kpi-label">Collected</div><div class="kpi-value">UGX {{ number_format($s['collected']) }}</div>
        <div class="kpi-foot">Collection rate {{ $s['rate'] }}%</div><i class="kpi-icon icon-cash2"></i></div></div>
    <div class="col-6 col-xl-3 mb-3"><div class="kpi-card bg-danger-400">
        <div class="kpi-label">Outstanding</div><div class="kpi-value">UGX {{ number_format($s['outstanding']) }}</div>
        <div class="kpi-foot">Expected − Collected</div><i class="kpi-icon icon-alarm"></i></div></div>
    <div class="col-6 col-xl-3 mb-3"><div class="kpi-card bg-indigo-400">
        <div class="kpi-label">Collection Rate</div><div class="kpi-value">{{ $s['rate'] }}%</div>
        <div class="kpi-foot">of expected fees this scope</div><i class="kpi-icon icon-chart"></i></div></div>
</div>

<div class="row mb-1">
    <div class="col-md-4 col-4 text-center border-right py-2">
        <span class="status-pill st-paid">Fully paid</span>
        <div class="font-weight-bold mt-1">{{ number_format($s['fully_paid']) }}</div>
    </div>
    <div class="col-md-4 col-4 text-center border-right py-2">
        <span class="status-pill st-partial">Partially paid</span>
        <div class="font-weight-bold mt-1">{{ number_format($s['partially_paid']) }}</div>
    </div>
    <div class="col-md-4 col-4 text-center py-2">
        <span class="status-pill st-unpaid">No payment yet</span>
        <div class="font-weight-bold mt-1">{{ number_format($s['no_payment']) }}</div>
    </div>
</div>

{{-- ================= Trend + Class breakdown ================= --}}
<div class="row mt-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header header-elements-inline py-2"><span class="font-weight-semibold">Collection Trend</span></div>
            <div class="card-body"><div id="trend-chart" style="height:260px;"></div></div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header header-elements-inline py-2">
                <span class="font-weight-semibold">Collection by Class</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover rep-table mb-0">
                    <thead><tr>
                        <th>Class</th><th class="num">Students</th><th class="num">Per Student</th>
                        <th class="num">Expected</th><th class="num">Collected</th><th class="num">Outstanding</th><th class="num">Rate</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse($byClass->where('students','>',0) as $c)
                            <tr>
                                <td>{{ $c['name'] }}</td>
                                <td class="num">{{ number_format($c['students']) }}</td>
                                <td class="num">{{ $c['per_student'] ? number_format($c['per_student']) : '—' }}</td>
                                <td class="num money">{{ number_format($c['expected']) }}</td>
                                <td class="num money pos">{{ number_format($c['collected']) }}</td>
                                <td class="num money neg">{{ number_format($c['outstanding']) }}</td>
                                <td class="num">{{ $c['rate'] }}%</td>
                                <td class="rep-no-print"><a href="{{ route('reports.fee_status', ['year' => $f_session, 'my_class_id' => $c['id'], 'term' => $f_term]) }}" class="text-muted" title="View students"><i class="icon-arrow-right7"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No fee structures or students found for this session. Adjust the filters above.</td></tr>
                        @endforelse
                    </tbody>
                    @if($byClass->count())
                        <tfoot><tr>
                            <td>TOTAL</td><td class="num">{{ number_format($byClass->last()['students']) }}</td><td></td>
                            <td class="num">{{ number_format($byClass->last()['expected']) }}</td>
                            <td class="num">{{ number_format($byClass->last()['collected']) }}</td>
                            <td class="num">{{ number_format($byClass->last()['outstanding']) }}</td>
                            <td class="num">{{ $byClass->last()['rate'] }}%</td><td></td>
                        </tr></tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ================= Recent transactions ================= --}}
<div class="card mt-3">
    <div class="card-header header-elements-inline py-2">
        <span class="font-weight-semibold">Recent Receipts</span>
        <a href="{{ route('reports.daily', ['year' => $f_session]) }}" class="btn btn-sm btn-light">Reconciliation <i class="icon-arrow-right7 ml-1"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover rep-table mb-0">
            <thead><tr><th>Date</th><th>Student</th><th>Fee Type</th><th>Receipt Ref</th><th class="num">Amount</th></tr></thead>
            <tbody>
                @forelse($recent as $r)
                    <tr>
                        <td class="text-muted">{{ $r->pay_date }}</td>
                        <td>{{ $r->student }}</td>
                        <td>{{ $r->fee }}</td>
                        <td class="text-muted">{{ $r->ref_no }}</td>
                        <td class="num money pos">UGX {{ number_format($r->amt_paid) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No receipts recorded for session {{ $f_session }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
<script>
(function () {
    var labels = @json($trend[0] ?? []);
    var data = @json($trend[1] ?? []);
    if (!document.getElementById('trend-chart')) return;

    echarts.init(document.getElementById('trend-chart')).setOption({
        grid: { left: 10, right: 15, top: 30, bottom: 25, containLabel: true },
        tooltip: { trigger: 'axis', valueFormatter: function (v) { return 'UGX ' + Number(v).toLocaleString(); } },
        xAxis: { type: 'category', data: labels, axisLabel: { rotate: 45 } },
        yAxis: { type: 'value', splitLine: { lineStyle: { color: '#eee' } } },
        series: [{
            name: 'Collected', type: 'bar', barMaxWidth: 26,
            itemStyle: { borderRadius: [5, 5, 0, 0], color: '#43a047' },
            label: { show: true, position: 'top', fontSize: 10, color: '#666',
                     formatter: function (p) { return p.value > 0 ? Math.round(p.value / 1000) + 'k' : ''; } },
            data: data
        }]
    });
})();
</script>
@endsection
