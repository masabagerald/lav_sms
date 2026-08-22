@extends('layouts.master')
@section('page_title', 'Debtors — Outstanding Fees')

@include('pages.support_team.reports._styles')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap mb-3 rep-header">
    <div>
        <h5 class="neg-text"><i class="icon-alarm mr-2 text-danger-400"></i>Debtors — Outstanding Fees</h5>
        <div class="sub">Session <b>{{ $f_session }}</b>@if($f_term) · Term {{ $f_term }}@endif — largest balances first.</div>
    </div>
    <div class="rep-no-print">
        <a href="{{ route('reports.debtors', array_filter(array_merge(request()->query(), ['export' => 'csv']))) }}" class="btn btn-success btn-sm"><i class="icon-file-excel mr-1"></i> CSV</a>
    </div>
</div>

<div class="card rep-no-print mb-3">
    <form method="GET" class="card-body py-3 row align-items-end rep-filter">
        <div class="col-md-3 col-6">
            <label>Session</label>
            <select name="year" class="form-control select">
                @foreach($years as $y)<option value="{{ $y }}" {{ $f_session === $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2 col-6">
            <label>Term</label>
            <select name="term" class="form-control select">
                <option value="">All</option>
                @for($t=1;$t<=3;$t++)<option value="{{ $t }}" {{ $f_term == $t ? 'selected' : '' }}>Term {{ $t }}</option>@endfor
            </select>
        </div>
        <div class="col-md-4 col-8">
            <label>Class</label>
            <select name="my_class_id" class="form-control select" data-placeholder="All classes">
                <option value="">All Classes</option>
                @foreach($classes as $c)<option value="{{ $c->id }}" {{ $f_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3 col-4"><button class="btn btn-primary btn-block">Apply</button></div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm rep-table mb-0">
            <thead><tr><th>#</th><th>Student</th><th>Adm No</th><th>Class</th><th class="num">Paid So Far</th><th class="num">Outstanding Balance</th><th></th></tr></thead>
            <tbody>
                @forelse($debtors as $i => $d)
                    <tr>
                        <td class="text-muted">{{ ($debtors->currentPage()-1)*$debtors->perPage() + $i + 1 }}</td>
                        <td class="font-weight-semibold">{{ $d->student }}</td>
                        <td class="text-muted">{{ $d->adm_no }}</td>
                        <td>{{ $d->class_name }}</td>
                        <td class="num money">{{ number_format($d->paid) }}</td>
                        <td class="num money neg font-weight-bold">UGX {{ number_format($d->balance) }}</td>
                        <td class="rep-no-print"><a href="{{ route('payments.invoice', [$d->student_id, $f_session]) }}"><i class="icon-file-text2 text-muted"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="icon-checkmark-circle icon-2x d-block mb-2 opacity-40"></i>
                        Great news — no outstanding student balances for this selection.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 rep-no-print">{{ $debtors->appends(request()->query())->links() }}</div>
@endsection
