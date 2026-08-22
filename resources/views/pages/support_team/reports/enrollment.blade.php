@extends('layouts.master')
@section('page_title', 'Enrollment & Growth')

@include('pages.support_team.reports._styles')

@section('content')
<div class="rep-header d-flex justify-content-between flex-wrap mb-3">
    <div>
        <h5><i class="icon-chart mr-2 text-purple-400"></i>Enrollment &amp; Growth</h5>
        <div class="sub">Session <b>{{ $f_session }}</b> — who is enrolled, and how the school has grown.</div>
    </div>
    <button onclick="window.print()" class="btn btn-light btn-sm rep-no-print"><i class="icon-printer2 mr-1"></i> Print</button>
</div>

<div class="card rep-no-print mb-3">
    <form method="GET" class="card-body py-3 row align-items-end rep-filter">
        <div class="col-md-5 col-7">
            <label>Session</label>
            <select name="year" class="form-control select">
                @foreach($years as $y)<option value="{{ $y }}" {{ $f_session === $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3 col-5"><button class="btn btn-primary btn-block">Apply</button></div>
    </form>
</div>

{{-- KPIs --}}
<div class="row mb-1">
    <div class="col-6 col-xl-3"><div class="kpi-card bg-blue-400 mb-3">
        <div class="kpi-label">Total Enrolled</div><div class="kpi-value">{{ number_format($e['total']) }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="kpi-card bg-success-400 mb-3">
        <div class="kpi-label">New This Session</div><div class="kpi-value">{{ number_format($e['new_admissions']) }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="kpi-card bg-warning-400 mb-3">
        <div class="kpi-label">Graduated Flagged</div><div class="kpi-value">{{ number_format($e['graduated']) }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="kpi-card bg-indigo-400 mb-3">
        <div class="kpi-label">Classes In Use</div><div class="kpi-value">{{ $e['by_class']->where('total','>',0)->count() }}</div></div></div>
</div>

<div class="row">
    <div class="col-lg-5 mb-3">
        <div class="card h-100">
            <div class="card-header py-2 font-weight-semibold">Growth by Year of Admission</div>
            <div class="card-body"><div id="growth-chart" style="height:260px;"></div></div>
        </div>
    </div>
    <div class="col-lg-7 mb-3">
        <div class="card h-100">
            <div class="card-header py-2 font-weight-semibold">Current Enrollment by Class</div>
            <div class="table-responsive"><table class="table table-sm rep-table mb-0">
                <thead><tr><th>Class</th><th class="num">Students</th><th style="width:45%">Share</th></tr></thead>
                <tbody>
                    @forelse($e['by_class'] as $c)
                        <tr>
                            <td>{{ $c['name'] }}</td>
                            <td class="num">{{ number_format($c['total']) }}</td>
                            <td>
                                @if($e['total'] > 0)
                                    <div class="progress" style="height:6px;margin-bottom:0;">
                                        <div class="progress-bar bg-blue-400" style="width: {{ round($c['total'] / $e['total'] * 100) }}%"></div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No enrolment data for session {{ $f_session }}.</td></tr>
                    @endforelse
                </tbody>
                @if($e['by_class']->count())
                    <tfoot><tr><td>TOTAL</td><td class="num">{{ number_format($e['total']) }}</td><td></td></tr></tfoot>
                @endif
            </table></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
<script>
(function () {
    var el = document.getElementById('growth-chart');
    if (!el) return;
    var growth = @json(collect($e['growth'])->map(fn ($g) => ['yr' => (string) $g->yr, 'total' => (int) $g->total]));

    echarts.init(el).setOption({
        tooltip: { trigger: 'axis' },
        grid: { left: 10, right: 15, top: 30, bottom: 25, containLabel: true },
        xAxis: { type: 'category', data: growth.map(g => g.yr) },
        yAxis: { type: 'value', splitLine: { lineStyle: { color: '#eee' } } },
        series: [{
            type: 'line', smooth: true, symbolSize: 8,
            lineStyle: { color: '#8e24aa', width: 3 },
            itemStyle: { color: '#8e24aa' },
            areaStyle: { color: 'rgba(142,36,170,.08)' },
            label: { show: true, position: 'top', fontSize: 11, color: '#666' },
            data: growth.map(g => g.total)
        }]
    });
})();
</script>
@endsection
