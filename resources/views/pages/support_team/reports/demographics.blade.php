@extends('layouts.master')
@section('page_title', 'Student Demographics')

@include('pages.support_team.reports._styles')

@section('content')
<div class="rep-header d-flex justify-content-between flex-wrap mb-3">
    <div>
        <h5><i class="icon-users2 mr-2 text-warning-400"></i>Student Demographics</h5>
        <div class="sub">Session <b>{{ $f_session }}</b>@if($fc = $classes->firstWhere('id', $f_class)) · {{ $fc->name }}@endif</div>
    </div>
    <button onclick="window.print()" class="btn btn-light btn-sm rep-no-print"><i class="icon-printer2 mr-1"></i> Print</button>
</div>

<div class="card rep-no-print mb-3">
    <form method="GET" class="card-body py-3 row align-items-end rep-filter">
        <div class="col-md-5 col-6">
            <label>Session</label>
            <select name="year" class="form-control select">
                @foreach($years as $y)<option value="{{ $y }}" {{ $f_session === $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-5 col-6">
            <label>Class</label>
            <select name="my_class_id" class="form-control select" data-placeholder="All classes">
                <option value="">All Classes</option>
                @foreach($classes as $c)<option value="{{ $c->id }}" {{ $f_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary btn-block">Apply</button></div>
    </form>
</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card h-100"><div class="card-header py-2 font-weight-semibold">By Gender</div>
            <div class="card-body"><div id="gender-chart" style="height:230px;"></div></div></div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card h-100"><div class="card-header py-2 font-weight-semibold">By Age Group</div>
            <div class="card-body"><div id="age-chart" style="height:230px;"></div></div></div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card h-100"><div class="card-header py-2 font-weight-semibold">Boarding vs Day</div>
            <div class="card-body"><div id="boarding-chart" style="height:230px;"></div></div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-header py-2 font-weight-semibold">Students by Class</div>
            <div class="table-responsive"><table class="table table-sm rep-table mb-0">
                <thead><tr><th>Class</th><th class="num">Students</th></tr></thead>
                <tbody>
                    @forelse($d['byClass'] as $name => $n)
                        <tr><td>{{ $name }}</td><td class="num">{{ number_format($n) }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted py-4">No enrolment data for this session.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header py-2 font-weight-semibold">Summary</div>
            <div class="card-body">
                <p style="font-size:.9rem;"><b>{{ number_format($d['total']) }}</b> students in scope for session {{ $f_session }}.</p>
                @php
                    $male = (int) ($d['gender']['male'] ?? 0);
                    $female = (int) ($d['gender']['female'] ?? 0);
                @endphp
                <ul class="list-unstyled mb-0" style="font-size:.88rem;">
                    <li class="mb-1"><span class="status-pill st-neutral">Male</span> {{ number_format($male) }}
                        ({{ $d['total'] ? round($male / $d['total'] * 100, 1) : 0 }}%)</li>
                    <li class="mb-1"><span class="status-pill st-neutral">Female</span> {{ number_format($female) }}
                        ({{ $d['total'] ? round($female / $d['total'] * 100, 1) : 0 }}%)</li>
                    <li class="mb-1"><span class="status-pill st-paid">Boarders</span>
                        {{ number_format($d['boarding']->except(['Day'])->sum()) }}</li>
                    <li><span class="status-pill st-partial">Day scholars</span>
                        {{ number_format($d['boarding']['Day'] ?? 0) }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
<script>
(function () {
    if (!document.getElementById('gender-chart')) return;
    var gender = @json($d['gender']);
    var age = @json($d['age']);
    var boarding = @json($d['boarding']);

    function pie(id, data, colors) {
        var el = document.getElementById(id);
        if (!el) return;
        echarts.init(el).setOption({
            tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
            legend: { bottom: 0, icon: 'circle', textStyle: { fontSize: 10 } },
            series: [{ type: 'pie', radius: ['42%','66%'], center: ['50%','44%'],
                label: { show: false }, color: colors,
                data: Object.keys(data).map(function (k) { return { name: k, value: Number(data[k]) }; }) }]
        });
    }

    pie('gender-chart', gender, ['#1e88e5', '#e91e63', '#90a4ae']);
    pie('boarding-chart', boarding, ['#43a047', '#fb8c00', '#8e24aa', '#3949ab']);

    var el = document.getElementById('age-chart');
    echarts.init(el).setOption({
        tooltip: {},
        grid: { left: 5, right: 5, top: 25, bottom: 20, containLabel: true },
        xAxis: { type: 'category', data: Object.keys(age), axisLabel: { rotate: 30, fontSize: 10 } },
        yAxis: { type: 'value' },
        series: [{ type: 'bar', barMaxWidth: 26, itemStyle: { borderRadius: [4,4,0,0], color: '#1f4e8c' },
                   data: Object.values(age).map(Number) }]
    });
})();
</script>
@endsection
