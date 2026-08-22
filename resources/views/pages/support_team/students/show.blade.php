@extends('layouts.master')

@section('page_title', 'Student Profile - '.$sr->user->name)

@section('content')

{{-- ============ PROFILE HERO ============ --}}
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center">

            <img src="{{ $sr->user->photo ?? Qs::getDefaultUserImage() }}"
                 onerror="this.src='{{ Qs::getDefaultUserImage() }}'"
                 class="rounded-circle mr-3 my-2"
                 alt="{{ $sr->user->name }}"
                 style="width:96px; height:96px; object-fit:cover; border:3px solid #fff; box-shadow:0 0 0 1px rgba(30,60,110,.12), 0 4px 14px rgba(16,42,80,.10);">

            <div class="mr-auto my-2">
                <h4 class="font-weight-semibold mb-1">{{ $sr->user->name }}</h4>
                <div class="d-flex flex-wrap mt-2">
                    <span class="stat-chip mr-1 mb-1"><i class="icon-user-check"></i>ADM {{ $sr->adm_no ?: '—' }}</span>
                    <span class="stat-chip mr-1 mb-1"><i class="icon-office"></i>{{ $sr->my_class->name ?? '' }} {{ $sr->section->name ?? '' }}</span>
                    @if($sr->session)
                        <span class="stat-chip mr-1 mb-1"><i class="icon-calendar5"></i>{{ $sr->session }}</span>
                    @endif
                    @if($sr->user->gender)
                        <span class="chip-gender {{ strtolower($sr->user->gender) == 'male' ? 'chip-male' : 'chip-female' }} mb-1">{{ $sr->user->gender }}</span>
                    @endif
                    <span class="badge badge-{{ $sr->grad ? 'success' : 'light border text-muted' }} ml-1 mb-1 align-self-center">
                        {{ $sr->grad ? 'Graduated' : 'Active' }}
                    </span>
                </div>
            </div>

            <div class="text-right my-2">
                <span class="text-muted d-block" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.06em;">Fees (Declared)</span>
                <strong class="money-cell" style="font-size:1.15rem;">{{ $sr->fees ? 'UGX '.number_format((float) $sr->fees) : '—' }}</strong>
                @if(Qs::userIsTeamAccount())
                    <div class="mt-1">
                        <a href="{{ route('payments.invoice', Qs::hash($sr->user_id)) }}" class="btn btn-light btn-sm border"><i class="icon-cash2 mr-1"></i>Payment History</a>
                    </div>
                @endif
            </div>

        </div>

        @if(Qs::userIsTeamSA())
        <div class="mt-3 pt-3" style="border-top:1px solid rgba(30,60,110,.08);">
            <a href="{{ route('students.edit', Qs::hash($sr->id)) }}" class="btn btn-primary btn-sm mr-1"><i class="icon-pencil mr-1"></i>Edit Record</a>
            <a href="{{ route('st.reset_pass', Qs::hash($sr->user_id)) }}" class="btn btn-light btn-sm border"><i class="icon-lock mr-1"></i>Reset Password</a>
        </div>
        @endif
    </div>
</div>

{{-- ============ DETAILS TABS ============ --}}
<div class="row">
    <div class="col-md-12 mt-3">
        <div class="card">
            <div class="card-body">

                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#basic-info">Basic Info</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#additional-info">Guardian &amp; Personal</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#academic-info">Academic / Status</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">

                    {{-- ================= BASIC INFO ================= --}}
                    <div class="tab-pane fade show active" id="basic-info">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted" style="width:40%;">Full Name</td>
                                        <td class="font-weight-semibold">{{ $sr->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Admission No</td>
                                        <td class="font-weight-semibold">{{ $sr->adm_no ?: '—' }}</td>
                                    </tr>
                                    @if($sr->old_reg_no)
                                    <tr>
                                        <td class="text-muted">Previous Reg. No</td>
                                        <td>{{ $sr->old_reg_no }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="text-muted">Class — Section</td>
                                        <td>{{ $sr->class_section }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Session</td>
                                        <td>{{ $sr->session ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Year Admitted</td>
                                        <td>{{ $sr->year_admitted ?: '—' }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                    @if($sr->my_parent_id)
                                    <tr>
                                        <td class="text-muted" style="width:40%;">Parent / Guardian Account</td>
                                        <td>{{ $sr->my_parent->name ?? '—' }}</td>
                                    </tr>
                                    @endif

                                    @if($sr->user->email)
                                    <tr>
                                        <td class="text-muted">Email</td>
                                        <td>{{ $sr->user->email }}</td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td class="text-muted">Phone</td>
                                        <td>{{ $sr->guardian_phone ?? $sr->user->phone ?? '—' }}</td>
                                    </tr>

                                    @if($sr->user->dob)
                                    <tr>
                                        <td class="text-muted">Date of Birth</td>
                                        <td>{{ $sr->user->dob }}</td>
                                    </tr>
                                    @endif

                                    @if($sr->user->address)
                                    <tr>
                                        <td class="text-muted">Address</td>
                                        <td>{{ $sr->user->address }}</td>
                                    </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ================= ADDITIONAL INFO ================= --}}
                    <div class="tab-pane fade" id="additional-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="form-section-title"><i class="icon-users2"></i> Guardian Details</h6>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted" style="width:40%;">Guardian Name</td>
                                        <td>{{ $sr->guardian_name ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Guardian Phone</td>
                                        <td>{{ $sr->guardian_phone ?? $sr->user->phone ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Sub County</td>
                                        <td>{{ $sr->sub_county ?: '—' }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="form-section-title"><i class="icon-user"></i> Personal</h6>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted" style="width:40%;">Age</td>
                                        <td>{{ $sr->age ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Religion</td>
                                        <td>{{ $sr->religion ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">House</td>
                                        <td>{{ $sr->house ?: '—' }}</td>
                                    </tr>
                                    @if($sr->dorm_id)
                                    <tr>
                                        <td class="text-muted">Dormitory</td>
                                        <td>{{ optional($sr->dorm)->name }} {{ $sr->dorm_room_no }}</td>
                                    </tr>
                                    @endif
                                    @if($sr->general_comments)
                                    <tr>
                                        <td class="text-muted">General Comments</td>
                                        <td>{{ $sr->general_comments }}</td>
                                    </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ================= ACADEMIC / STATUS ================= --}}
                    <div class="tab-pane fade" id="academic-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="form-section-title"><i class="icon-book"></i> Prior National Exams</h6>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted" style="width:40%;">UPE Results</td>
                                        <td>{{ $sr->upe_results ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">UCE Results</td>
                                        <td>{{ $sr->uce_results ?: '—' }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="form-section-title"><i class="icon-cash2"></i> Status &amp; Fees</h6>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted" style="width:40%;">Enrollment Status</td>
                                        <td><span class="badge badge-{{ $sr->grad ? 'success' : 'primary' }}">{{ $sr->grad ? 'Graduated' : 'Active' }}</span></td>
                                    </tr>
                                    @if($sr->grad_date)
                                    <tr>
                                        <td class="text-muted">Graduation Date</td>
                                        <td>{{ $sr->grad_date }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="text-muted">Fees (Declared)</td>
                                        <td class="money-cell">{{ $sr->fees ? 'UGX '.number_format((float) $sr->fees) : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Record Created</td>
                                        <td>{{ $sr->created_at->format('d M Y') }}</td>
                                    </tr>
                                    </tbody>
                                </table>

                                @if(Qs::userIsTeamAccount())
                                    <a href="{{ route('payments.invoice', Qs::hash($sr->user_id)) }}" class="btn btn-outline-primary btn-sm"><i class="icon-file-text2 mr-1"></i>Open Full Invoice</a>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
