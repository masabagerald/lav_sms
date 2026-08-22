@extends('layouts.master')
@section('page_title', 'Daily Collections & Reconciliation')

@include('pages.support_team.reports._styles')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap mb-3 rep-header">
    <div>
        <h5><i class="icon-calculator mr-2 text-indigo-400"></i>Daily Collections &amp; Reconciliation</h5>
        <div class="sub">Every receipt transaction for session <b>{{ $f_session }}</b>, grouped by day.</div>
    </div>
    <div class="rep-no-print">
        <a href="{{ route('reports.daily', array_filter(array_merge(request()->query(), ['export' => 'csv']))) }}" class="btn btn-success btn-sm mr-1"><i class="icon-file-excel mr-1"></i> CSV</a>
        <button onclick="window.print()" class="btn btn-light btn-sm"><i class="icon-printer2 mr-1"></i> Print</button>
    </div>
</div>

<div class="card rep-no-print mb-3">
    <form method="GET" class="card-body py-3 row align-items-end rep-filter">
        <input type="hidden" name="year" value="{{ $f_session }}">
        <div class="col-md-3 col-6"><label>From</label><input type="date" name="from" value="{{ $f_from }}" class="form-control"></div>
        <div class="col-md-3 col-6"><label>To</label><input type="date" name="to" value="{{ $f_to }}" class="form-control"></div>
        <div class="col-md-3 col-6"><button class="btn btn-primary btn-block">Apply</button></div>
        <div class="col-md-3 col-6 text-right"><span class="text-muted" style="font-size:.8rem;">{{ number_format($count) }} transactions · UGX {{ number_format($grand) }} total</span></div>
    </form>
</div>

<div class="rep-print-title"><b>{{ Qs::getSetting('system_name') }}</b> — Payment Reconciliation ({{ $f_session }})</div>

@forelse($days as $day => $txns)
    <div class="card mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="font-weight-semibold"><i class="icon-calendar52 mr-2 opacity-50"></i>{{ \Carbon\Carbon::parse($day)->format('l, d F Y') }}</span>
            <span class="money pos font-weight-bold">UGX {{ number_format($txns->sum('amt_paid')) }}
                <span class="text-muted font-weight-normal" style="font-size:.75rem;">({{ $txns->count() }} receipts)</span></span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm rep-table mb-0">
                <thead><tr><th>#</th><th>Student</th><th>Fee Type</th><th>Receipt Ref</th><th class="num">Amount Received</th></tr></thead>
                <tbody>
                    @foreach($txns as $r)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td>{{ $r->student }}</td>
                            <td>{{ $r->fee }}</td>
                            <td class="text-muted">{{ $r->ref_no }}</td>
                            <td class="num money pos">{{ number_format($r->amt_paid) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="icon-cash2 icon-2x d-block mb-2 opacity-40"></i>
        No payment records were found{{ $f_from || $f_to ? ' for the selected date range' : '' }}.
        <br><span class="font-size-sm">Adjust the date range or pick a different session.</span>
    </div></div>
@endforelse

@if($days->count())
    <div class="alert alert-light border d-flex justify-content-between align-items-center">
        <b>GRAND TOTAL ({{ number_format($count) }} transactions)</b>
        <b class="money pos" style="font-size:1.05rem;">UGX {{ number_format($grand) }}</b>
    </div>
@endif
@endsection
