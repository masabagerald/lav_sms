@extends('layouts.master')

@section('page_title', 'Student Profile - '.$sr->user->name)

@section('content')
<div class="row">

    {{-- ================= LEFT PROFILE ================= --}}
    <div class="col-md-3 text-center">
        <div class="card">
            <div class="card-body">
                <img
                    src="{{ $sr->user->photo }}"
                    class="rounded-circle"
                    alt="photo"
                    style="width:90%; height:90%; object-fit:cover;"
                >
                <h4 class="mt-3">{{ $sr->user->name }}</h4>
                <p class="text-muted">{{ $sr->class_section }}</p>
            </div>
        </div>
    </div>

    {{-- ================= RIGHT CONTENT ================= --}}
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">

                {{-- ================= TABS ================= --}}
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#basic-info">
                            Basic Info
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#additional-info">
                            Additional Info
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#academic-info">
                            Academic / Status
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3">

                    {{-- ================= BASIC INFO ================= --}}
                    <div class="tab-pane fade show active" id="basic-info">
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <td class="font-weight-bold">Name</td>
                                <td>{{ $sr->user->name }}</td>
                            </tr>

                            <tr>
                                <td class="font-weight-bold">Admission No</td>
                                <td>{{ $sr->adm_no }}</td>
                            </tr>

                            <tr>
                                <td class="font-weight-bold">Class</td>
                                <td>{{ $sr->class_section }}</td>
                            </tr>

                            <tr>
                                <td class="font-weight-bold">Session</td>
                                <td>{{ $sr->session }}</td>
                            </tr>

                            <tr>
                                <td class="font-weight-bold">Year Admitted</td>
                                <td>{{ $sr->year_admitted }}</td>
                            </tr>

                            @if($sr->my_parent_id)
                            <tr>
                                <td class="font-weight-bold">Parent</td>
                                <td>{{ $sr->my_parent->name }}</td>
                            </tr>
                            @endif

                            @if($sr->dorm_id)
                            <tr>
                                <td class="font-weight-bold">Dormitory</td>
                                <td>{{ $sr->dorm->name }} {{ $sr->dorm_room_no }}</td>
                            </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- ================= ADDITIONAL INFO ================= --}}
                    <div class="tab-pane fade" id="additional-info">
                        <table class="table table-bordered">
                            <tbody>
                            @if($sr->age)
                            <tr>
                                <td class="font-weight-bold">Age</td>
                                <td>{{ $sr->age }}</td>
                            </tr>
                            @endif

                            @if($sr->religion)
                            <tr>
                                <td class="font-weight-bold">Religion</td>
                                <td>{{ $sr->religion }}</td>
                            </tr>
                            @endif

                            @if($sr->guardian_name)
                            <tr>
                                <td class="font-weight-bold">Guardian Name</td>
                                <td>{{ $sr->guardian_name }}</td>
                            </tr>
                            @endif

                            @if($sr->sub_county)
                            <tr>
                                <td class="font-weight-bold">Sub County</td>
                                <td>{{ $sr->sub_county }}</td>
                            </tr>
                            @endif

                            @if($sr->house)
                            <tr>
                                <td class="font-weight-bold">House</td>
                                <td>{{ $sr->house }}</td>
                            </tr>
                            @endif

                            @if($sr->general_comments)
                            <tr>
                                <td class="font-weight-bold">General Comments</td>
                                <td>{{ $sr->general_comments }}</td>
                            </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- ================= ACADEMIC / STATUS ================= --}}
                    <div class="tab-pane fade" id="academic-info">
                        <table class="table table-bordered">
                            <tbody>
                            @if($sr->upe_results)
                            <tr>
                                <td class="font-weight-bold">UPE Results</td>
                                <td>{{ $sr->upe_results }}</td>
                            </tr>
                            @endif

                            @if($sr->uce_results)
                            <tr>
                                <td class="font-weight-bold">UCE Results</td>
                                <td>{{ $sr->uce_results }}</td>
                            </tr>
                            @endif

                            <tr>
                                <td class="font-weight-bold">Fees Status</td>
                                <td>
                                    {{ $sr->fees }}                                    
                                        
                                </td>
                            </tr>

                            <tr>
                                <td class="font-weight-bold">Graduated</td>
                                <td>{{ $sr->grad ? 'Yes' : 'No' }}</td>
                            </tr>

                            @if($sr->grad_date)
                            <tr>
                                <td class="font-weight-bold">Graduation Date</td>
                                <td>{{ $sr->grad_date }}</td>
                            </tr>
                            @endif

                            <tr>
                                <td class="font-weight-bold">Record Created</td>
                                <td>{{ $sr->created_at->format('d M Y') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
