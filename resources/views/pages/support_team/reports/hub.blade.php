@extends('layouts.master')
@section('page_title', 'Reports')

@include('pages.support_team.reports._styles')

@section('content')
<div class="rep-header mb-3">
    <h5><i class="icon-statistics mr-2 text-primary"></i>Reports &amp; Analytics</h5>
    <div class="sub">Answer real operational questions — collections, balances, students and performance.</div>
</div>

<div class="row">
    {{-- ================= FINANCE ================= --}}
    <div class="col-12 mb-2"><p class="set-section-title" style="font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#8895a7;margin-bottom:.4rem;">Finance &amp; Fees</p></div>
    @php $finance = [
        ['reports.payments', 'icon-cash2', 'Finance Dashboard', 'Collections, outstanding, rates and trends for the whole school.', 'bg-success-400'],
        ['reports.fee_status', 'icon-users4', 'Student Fee Status', 'Expected vs paid vs balance per student. Who has paid, who hasn\u2019t.', 'bg-blue-400'],
        ['reports.debtors', 'icon-alarm', 'Debtors / Outstanding', 'Students with the largest fee balances, largest first.', 'bg-danger-400'],
        ['reports.daily', 'icon-calculator', 'Daily Collections', 'Reconcile every receipt transaction by date and total.', 'bg-indigo-400'],
    ]; @endphp
    @foreach($finance as $r)
        <div class="col-sm-6 col-xl-3 mb-3">
            <a href="{{ route($r[0]) }}" class="card cat-card d-block text-decoration-none text-reset">
                <div class="card-body d-flex align-items-start">
                    <span class="cat-icon {{ $r[4] }} text-white mr-3"><i class="{{ $r[1] }}"></i></span>
                    <div>
                        <div class="font-weight-semibold">{{ $r[2] }}</div>
                        <div class="text-muted mt-1" style="font-size:.8rem;">{{ $r[3] }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach

    {{-- ================= STUDENTS ================= --}}
    <div class="col-12 mb-2 mt-2"><p style="font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#8895a7;margin-bottom:.4rem;">Student Management</p></div>
    @php $studentR = [
        ['reports.students.register', 'icon-list', 'Student Register', 'Official register with guardian contacts. Print, PDF or CSV.', 'bg-teal-400'],
        ['reports.demographics', 'icon-users2', 'Demographics', 'Gender, age groups, boarding and class distribution.', 'bg-warning-400'],
        ['reports.enrollment', 'icon-chart', 'Enrollment &amp; Growth', 'Total enrolment, new admissions and year-on-year growth.', 'bg-purple-400'],
    ]; @endphp
    @foreach($studentR as $r)
        <div class="col-sm-6 col-xl-4 mb-3">
            <a href="{{ route($r[0]) }}" class="card cat-card d-block text-decoration-none text-reset">
                <div class="card-body d-flex align-items-start">
                    <span class="cat-icon {{ $r[4] }} text-white mr-3"><i class="{{ $r[1] }}"></i></span>
                    <div>
                        <div class="font-weight-semibold">{!! $r[2] !!}</div>
                        <div class="text-muted mt-1" style="font-size:.8rem;">{{ $r[3] }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach

    {{-- ================= ACADEMIC ================= --}}
    <div class="col-12 mb-2 mt-2"><p style="font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#8895a7;margin-bottom:.4rem;">Academic</p></div>
    <div class="col-sm-6 col-xl-4 mb-3">
        <a href="{{ route('reports.academic') }}" class="card cat-card d-block text-decoration-none text-reset">
            <div class="card-body d-flex align-items-start">
                <span class="cat-icon bg-brown-400 text-white mr-3"><i class="icon-file-text2"></i></span>
                <div>
                    <div class="font-weight-semibold">Exam Performance Overview</div>
                    <div class="text-muted mt-1" style="font-size:.8rem;">Subject averages, grade distribution and top students per exam.</div>
                </div>
            </div>
        </a>
    </div>
</div>

@if(!Qm::enabled('finance') || !Qm::enabled('students'))
    <div class="alert alert-light border mt-2" style="font-size:.82rem;">
        <i class="icon-info22 mr-1"></i> Some reports may be unavailable because their module is disabled in Settings.
    </div>
@endif
@endsection
