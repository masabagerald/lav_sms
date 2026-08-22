@extends('layouts.master')
@section('page_title', 'My Children')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-users4 mr-2 text-primary"></i> My Children
                <span class="stat-chip ml-2"><i class="icon-users2"></i>{{ $students->count() }} enrolled</span>
            </h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            @if($students->isEmpty())
                <div class="empty-state my-4">
                    <i class="icon-users4"></i>
                    <div class="empty-title">No children linked to your account yet</div>
                    <span class="text-muted">Contact the school administration to link your children.</span>
                </div>
            @else
            <div class="table-responsive">
            <table class="table table-hover datatable-button-html5-columns">
                <thead>
                <tr>
                    <th>S/N</th>
                    <th>Student</th>
                    <th>ADM No</th>
                    <th>Class</th>
                    <th>Email</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($students as $s)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img class="avatar-sm mr-2"
                                     src="{{ $s->user->photo ?? Qs::getDefaultUserImage() }}"
                                     onerror="this.src='{{ Qs::getDefaultUserImage() }}'"
                                     alt="{{ $s->user->name }}">
                                <span class="cell-identity">
                                    <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="name">{{ $s->user->name }}</a>
                                    <span class="meta">{{ $s->my_class->name.' '.$s->section->name }}</span>
                                </span>
                            </div>
                        </td>
                        <td><span class="font-weight-semibold">{{ $s->adm_no }}</span></td>
                        <td>{{ $s->my_class->name.' '.$s->section->name }}</td>
                        <td>{{ $s->user->email ?: '—' }}</td>
                        <td class="text-center">
                                <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="btn btn-light btn-sm border mr-1"><i class="icon-eye"></i> Profile</a>
                                <a target="_blank" href="{{ route('marks.year_selector', Qs::hash($s->user->id)) }}" class="btn btn-primary btn-sm"><i class="icon-book mr-1"></i> Marksheet</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif

        </div>
    </div>

    {{--Student List Ends--}}

@endsection
