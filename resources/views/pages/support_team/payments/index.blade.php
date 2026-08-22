@extends('layouts.master')
@section('page_title', 'Fee Setup & Payments'.($selected ? ' — '.$year : ''))
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-cash2 mr-2 text-success-400"></i> Fee Setup Overview</h5>
            <div>
                <a href="{{ route('reports.index') }}" class="btn btn-light btn-sm mr-1"><i class="icon-statistics mr-1"></i> Reports</a>
                @if(Qs::userIsTeamSA() || Qs::userIsTeamAccount())
                    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm"><i class="icon-plus2 mr-1"></i> New Fee Setup</a>
                @endif
            </div>
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('payments.select_year') }}">
                @csrf
                <div class="smis-toolbar">
                    <div>
                        <label for="year">Academic Session</label>
                        <select data-placeholder="Select session" required id="year" name="year" class="form-control select">
                            @foreach($years as $yr)
                                <option {{ ($selected && $year == $yr->year) ? 'selected' : '' }} value="{{ $yr->year }}">{{ $yr->year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Load Session <i class="icon-circle-right2 ml-1"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@if($selected)

    {{-- Session summary --}}
    <div class="row mt-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card card-body kpi-card bg-blue-400 text-white">
                <h3 class="mb-0">{{ $payments->count() }}</h3>
                <span class="kpi-sub d-block">Fee Types Configured</span>
                <i class="kpi-icon icon-file-text2"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card card-body kpi-card bg-indigo-400 text-white">
                <h3 class="mb-0">{{ $my_classes->count() }}</h3>
                <span class="kpi-sub d-block">Classes Billed</span>
                <i class="kpi-icon icon-office"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card card-body kpi-card bg-success-400 text-white">
                <h3 class="mb-0">UGX {{ number_format($payments->sum('amount')) }}</h3>
                <span class="kpi-sub d-block">Expected per Student (All Fees)</span>
                <i class="kpi-icon icon-calculator"></i>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Fee Structures — {{ $year }} Session</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-payments" class="nav-link active" data-toggle="tab">All Classes <span class="badge badge-light border ml-1">{{ $payments->count() }}</span></a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">By Class</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($my_classes as $mc)
                            <a href="#pc-{{ $mc->id }}" class="dropdown-item" data-toggle="tab">
                                {{ $mc->name }}
                                <span class="badge badge-light border ml-1">{{ $payments->where('my_class_id', $mc->id)->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                    <div class="tab-pane fade show active" id="all-payments">
                        @include('pages.support_team.payments.partials.fee-table', ['fees' => $payments])
                    </div>

                @foreach($my_classes as $mc)
                    <div class="tab-pane fade" id="pc-{{ $mc->id }}">
                        @include('pages.support_team.payments.partials.fee-table', ['fees' => $payments->where('my_class_id', $mc->id)])
                    </div>
                    @endforeach
            </div>
        </div>
    </div>
    @endif

    {{--Payments List Ends--}}

@endsection
