@extends('layouts.master')
@section('page_title', 'Record Student Payments')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-calculator mr-2 text-primary"></i> Record Payments</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('payments.select_class') }}">
                @csrf
                <div class="smis-toolbar">
                    <div>
                        <label for="my_class_id">Class</label>
                        <select required id="my_class_id" name="my_class_id" class="form-control select" data-placeholder="Select class to bill...">
                            @foreach($my_classes as $c)
                                <option {{ ($selected && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Load Students <i class="icon-circle-right2 ml-1"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selected)
        <div class="card mt-3">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Students &amp; Fee Balances
                    <span class="stat-chip ml-2"><i class="icon-users2"></i>{{ $students->count() }} students</span>
                </h6>
                {!! Qs::getPanelOptions() !!}
            </div>

            <div class="card-body">

                @if($students->isEmpty())
                    <div class="empty-state my-4">
                        <i class="icon-users4"></i>
                        <div class="empty-title">No students found in this class</div>
                        <span class="text-muted">Admit students first, then return here to record their payments.</span>
                    </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>ADM No</th>
                        <th class="text-right">School Fees (UGX)</th>
                        <th class="text-center">Payments</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img class="avatar-sm mr-2"
                                         src="{{ optional($s->user)->photo ?? Qs::getDefaultUserImage() }}"
                                         onerror="this.src='{{ Qs::getDefaultUserImage() }}'"
                                         alt="{{ optional($s->user)->name }}">
                                    <span class="cell-identity">
                                        <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="name">{{ $s->user->name ?? '' }}</a>
                                        <span class="meta">{{ optional($s->section)->name ? $s->my_class->name.' — '.$s->section->name : '' }}</span>
                                    </span>
                                </div>
                            </td>
                            <td><span class="font-weight-semibold">{{ $s->adm_no }}</span></td>
                            <td class="text-right money-cell">{{ $s->fees ? number_format((float) $s->fees) : '—' }}</td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm border" data-toggle="dropdown">
                                        Manage Payments <i class="icon-arrow-down5 ml-1"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <h6 class="dropdown-header">Open Invoice</h6>
                                        <a href="{{ route('payments.invoice', [Qs::hash($s->user_id)]) }}" class="dropdown-item"><i class="icon-file-text2 mr-2"></i>All Sessions</a>
                                        @foreach(Pay::getYears($s->user_id) as $py)
                                        @if($py)
                                            <a href="{{ route('payments.invoice', [Qs::hash($s->user_id), $py]) }}" class="dropdown-item"><i class="icon-calendar5 mr-2"></i>{{ $py }}</a>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
                @endif
            </div>
        </div>
    @endif
@endsection
