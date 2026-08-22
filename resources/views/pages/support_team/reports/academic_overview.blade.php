@extends('layouts.master')
@section('page_title', 'Exam Performance Overview')

@include('pages.support_team.reports._styles')

@section('content')
<div class="rep-header d-flex justify-content-between flex-wrap mb-3">
    <div>
        <h5><i class="icon-file-text2 mr-2 text-brown-400"></i>Exam Performance Overview</h5>
        <div class="sub">Subject averages, grade distribution and top students for a selected exam.</div>
    </div>
    <button onclick="window.print()" class="btn btn-light btn-sm rep-no-print"><i class="icon-printer2 mr-1"></i> Print</button>
</div>

<div class="card rep-no-print mb-3">
    <form method="GET" class="card-body py-3 row align-items-end rep-filter">
        <div class="col-lg-3 col-md-4 col-6">
            <label>Session</label>
            <select name="year" class="form-control select">
                @foreach($years as $y)<option value="{{ $y }}" {{ $f_session === $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select>
        </div>
        <div class="col-lg-3 col-md-4 col-6">
            <label>Exam</label>
            <select name="exam_id" class="form-control select">
                @forelse($exams as $ex)
                    <option value="{{ $ex->id }}" {{ $f_exam == $ex->id ? 'selected' : '' }}>{{ $ex->name }} (Term {{ $ex->term }})</option>
                @empty
                    <option value="">No exams this session</option>
                @endforelse
            </select>
        </div>
        <div class="col-lg-3 col-md-4 col-6">
            <label>Class</label>
            <select name="my_class_id" class="form-control select">
                @foreach($classes as $c)<option value="{{ $c->id }}" {{ $f_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-lg-3 col-6"><button class="btn btn-primary btn-block">Generate</button></div>
    </form>
</div>

@if(!$data)
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="icon-file-text2 icon-2x d-block mb-2 opacity-40"></i>
        No exams have been defined for session {{ $f_session }} yet.
    </div></div>
@else
    {{-- KPI strip --}}
    <div class="row mb-1">
        <div class="col-4"><div class="kpi-card bg-blue-400 mb-3">
            <div class="kpi-label">Students Sat</div><div class="kpi-value">{{ number_format($data['students']) }}</div></div></div>
        <div class="col-4"><div class="kpi-card bg-success-400 mb-3">
            <div class="kpi-label">Class Average</div><div class="kpi-value">{{ $data['class_ave'] }}%</div></div></div>
        <div class="col-4"><div class="kpi-card bg-warning-400 mb-3">
            <div class="kpi-label">Subjects Taken</div><div class="kpi-value">{{ $data['subjects']->count() }}</div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-header py-2 font-weight-semibold">Performance by Subject</div>
                <div class="table-responsive"><table class="table table-sm rep-table mb-0">
                    <thead><tr><th>Subject</th><th class="num">Entries</th><th class="num">Average</th><th class="num">Highest</th><th class="num">Pass Rate</th></tr></thead>
                    <tbody>
                        @forelse($data['subjects'] as $s)
                            <tr>
                                <td>{{ $s->subject }}</td>
                                <td class="num">{{ number_format($s->entries) }}</td>
                                <td class="num font-weight-semibold">{{ $s->average }}</td>
                                <td class="num">{{ $s->highest }}</td>
                                <td class="num">{{ $s->pass_rate }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No marks entered for this exam &amp; class yet.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="card h-100"><div class="card-header py-2 font-weight-semibold">Grade Distribution</div>
                <div class="card-body"><div id="grade-chart" style="height:250px;"></div></div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-2 font-weight-semibold"><i class="icon-trophy2 mr-2 text-warning-400"></i>Top 10 Students</div>
        <div class="table-responsive"><table class="table table-sm rep-table mb-0">
            <thead><tr><th>#</th><th>Student</th><th class="num">Total Marks</th><th class="num">Average</th><th class="num">Position</th></tr></thead>
            <tbody>
                @forelse($data['top'] as $t)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td class="font-weight-semibold">{{ $t->student }}</td>
                        <td class="num">{{ number_format((float) $t->total, 1) }}</td>
                        <td class="num">{{ $t->ave }}%</td>
                        <td class="num">{{ $t->pos ? '#' . $t->pos : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Exam records have not been compiled for this exam &amp; class.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
@endif
@endsection

@section('scripts')
<script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
<script>
(function () {
    var el = document.getElementById('grade-chart');
    if (!el) return;
    var grades = @json($data ? $data['grades'] : []);
    var keys = Object.keys(grades);

    if (!keys.length) {
        el.innerHTML = '<div class="text-center text-muted py-5" style="font-size:.85rem;">No graded marks yet.</div>';
        return;
    }

    echarts.init(el).setOption({
        tooltip: {},
        grid: { left: 5, right: 5, top: 25, bottom: 20, containLabel: true },
        xAxis: { type: 'category', data: keys },
        yAxis: { type: 'value' },
        series: [{ type: 'bar', barMaxWidth: 34,
            itemStyle: { borderRadius: [4,4,0,0], color: '#43a047' }, data: keys.map(k => Number(grades[k])) }]
    });
})();
</script>
@endsection
