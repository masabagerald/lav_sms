@extends('layouts.master')
@section('page_title', 'Student Register')

@include('pages.support_team.reports._styles')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap mb-3 rep-header">
    <div>
        <h5><i class="icon-list mr-2 text-teal-400"></i>Student Register</h5>
        <div class="sub">Session <b>{{ $f_session }}</b>@if($fc = $classes->firstWhere('id', $f_class)) · {{ $fc->name }}@endif — {{ number_format($rows->count()) }} students.</div>
    </div>
    <div class="rep-no-print">
        <a href="{{ route('reports.students.register', array_filter(array_merge(request()->query(), ['format' => 'pdf']))) }}" class="btn btn-danger btn-sm mr-1"><i class="icon-file-pdf mr-1"></i> PDF</a>
        <a href="{{ route('reports.students.register', array_filter(array_merge(request()->query(), ['export' => 'csv']))) }}" class="btn btn-success btn-sm mr-1"><i class="icon-file-excel mr-1"></i> CSV</a>
        <button onclick="window.print()" class="btn btn-light btn-sm"><i class="icon-printer2 mr-1"></i> Print</button>
    </div>
</div>

<div class="card rep-no-print mb-3">
    <form method="GET" class="card-body py-3 row align-items-end rep-filter">
        <div class="col-md-4 col-6">
            <label>Session</label>
            <select name="year" class="form-control select">
                @foreach($years as $y)<option value="{{ $y }}" {{ $f_session === $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4 col-6">
            <label>Class</label>
            <select name="my_class_id" class="form-control select" data-placeholder="All classes">
                <option value="">All Classes</option>
                @foreach($classes as $c)<option value="{{ $c->id }}" {{ $f_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><button class="btn btn-primary btn-block">Generate</button></div>
    </form>
</div>

<div class="rep-print-title"><b>{{ Qs::getSetting('system_name') }}</b> — Student Register ({{ $f_session }})</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm rep-table mb-0">
            <thead><tr>
                <th>#</th><th>Adm No</th><th>Student Name</th><th>Gender</th><th>Date of Birth</th>
                <th>Class / Stream</th><th>Guardian</th><th>Contact</th>
            </tr></thead>
            <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td>{{ $r->adm_no }}</td>
                        <td class="font-weight-semibold">{{ $r->student }}</td>
                        <td>{{ ucfirst((string) $r->gender) ?: '—' }}</td>
                        <td class="text-muted">{{ $r->dob ?: '—' }}</td>
                        <td>{{ trim(($r->class_name ?? '') . ' ' . ($r->section_name ?? '')) }}</td>
                        <td>{{ $r->guardian_name ?: '—' }}</td>
                        <td class="text-muted">{{ $r->contact ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">
                        <i class="icon-users4 icon-2x d-block mb-2 opacity-40"></i>
                        No students enrolled for this selection.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
