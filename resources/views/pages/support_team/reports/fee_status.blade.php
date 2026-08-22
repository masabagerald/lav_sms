@extends('layouts.master')
@section('page_title', 'Student Fee Status')

@include('pages.support_team.reports._styles')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap mb-3 rep-header">
    <div>
        <h5><i class="icon-users4 mr-2 text-blue-400"></i>Student Fee Status</h5>
        <div class="sub">Session <b>{{ $f_session }}</b>@if($f_term) · Term {{ $f_term }}@endif — Expected − Paid = Balance per student.</div>
    </div>
    <div class="rep-no-print">
        <a href="{{ route('reports.fee_status', array_filter(array_merge(request()->query(), ['export' => 'csv']))) }}" class="btn btn-success btn-sm mr-1"><i class="icon-file-excel mr-1"></i> CSV</a>
        <button onclick="window.print()" class="btn btn-light btn-sm"><i class="icon-printer2 mr-1"></i> Print</button>
    </div>
</div>

<div class="card rep-no-print mb-3">
    <form method="GET" class="card-body py-3">
        <div class="row align-items-end rep-filter">
            <div class="col-lg-2 col-md-3 col-sm-6 mb-2 mb-lg-0">
                <label>Session</label>
                <select name="year" class="form-control select">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $f_session === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-2 mb-lg-0">
                <label>Term</label>
                <select name="term" class="form-control select">
                    <option value="">All</option>
                    @for($t = 1; $t <= 3; $t++)
                        <option value="{{ $t }}" {{ $f_term == $t ? 'selected' : '' }}>Term {{ $t }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-2 mb-lg-0">
                <label>Class</label>
                <select name="my_class_id" class="form-control select" data-placeholder="All classes">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $f_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-2 mb-lg-0">
                <label>Status</label>
                <select name="status" class="form-control select">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>No Payment</option>
                </select>
            </div>
            <div class="col-lg-2 col-sm-6 mb-2 mb-lg-0">
                <label>Search</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Name or adm no...">
            </div>
            <div class="col-lg-1 col-sm-6">
                <button class="btn btn-primary btn-block">Go</button>
            </div>
        </div>
    </form>
</div>

<div class="rep-print-title"><b>{{ Qs::getSetting('system_name') }}</b> — Student Fee Status ({{ $f_session }})</div>

{{-- Totals strip --}}
<div class="row mb-3">
    <div class="col-6 col-md-3"><div class="kpi-card bg-blue-400">
        <div class="kpi-label">Students in scope</div><div class="kpi-value">{{ number_format($totals['students']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card bg-indigo-400">
        <div class="kpi-label">Expected</div><div class="kpi-value">UGX {{ number_format($totals['expected']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card bg-success-400">
        <div class="kpi-label">Collected</div><div class="kpi-value">UGX {{ number_format($totals['paid']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card bg-danger-400">
        <div class="kpi-label">Outstanding</div><div class="kpi-value">UGX {{ number_format($totals['balance']) }}</div></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm rep-table mb-0">
            <thead><tr>
                <th>#</th><th>Student</th><th>Adm No</th><th>Class / Stream</th>
                <th class="num">Expected</th><th class="num">Paid</th><th class="num">Balance</th>
                <th>Status</th><th>Guardian Contact</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($rows as $i => $r)
                    <tr>
                        <td class="text-muted">{{ ($paginator->currentPage() - 1) * $paginator->perPage() + $i + 1 }}</td>
                        <td class="font-weight-semibold">{{ $r['student'] }}</td>
                        <td class="text-muted">{{ $r['adm_no'] }}</td>
                        <td>{{ trim($r['class'] . ' ' . $r['section']) }}</td>
                        <td class="num money">{{ number_format($r['expected']) }}</td>
                        <td class="num money pos">{{ number_format($r['paid']) }}</td>
                        <td class="num money {{ $r['balance'] > 0 ? 'neg font-weight-semibold' : '' }}">{{ number_format($r['balance']) }}</td>
                        <td>
                            <span class="status-pill st-{{ $r['state'] }}">
                                {{ ['paid'=>'Paid','partial'=>'Partial','unpaid'=>'No Payment'][$r['state']] }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $r['contact'] ?: '—' }}</td>
                        <td class="rep-no-print"><a href="{{ route('payments.invoice', [$r['user_id'], $f_session]) }}" title="Fee history"><i class="icon-file-text2 text-muted"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-5">
                        <i class="icon-users4 icon-2x d-block mb-2 opacity-40"></i>
                        No students match the selected filters.<br>
                        <span class="font-size-sm">Try another session, class or clear the search box.</span>
                    </td></tr>
                @endforelse
            </tbody>
            @if($rows->count())
                <tfoot><tr>
                    <td colspan="4">TOTALS (whole selection)</td>
                    <td class="num">{{ number_format($totals['expected']) }}</td>
                    <td class="num">{{ number_format($totals['paid']) }}</td>
                    <td class="num">{{ number_format($totals['balance']) }}</td>
                    <td colspan="3"></td>
                </tr></tfoot>
            @endif
        </table>
    </div>
</div>

<div class="mt-3 rep-no-print">{{ $paginator->appends(request()->query())->links() }}</div>
@endsection
