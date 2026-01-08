@extends('layouts.master')

@section('page_title', 'Student Information - '.$my_class->name)

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Students List</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">

        {{-- ================= TABS ================= --}}
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item">
                <a href="#all-students" class="nav-link active" data-toggle="tab">
                    All {{ $my_class->name }} Students
                </a>
            </li>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    Sections
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($sections as $s)
                        <a href="#s{{ $s->id }}" class="dropdown-item" data-toggle="tab">
                            {{ $my_class->name.' '.$s->name }}
                        </a>
                    @endforeach
                </div>
            </li>
        </ul>

        {{-- ================= TAB CONTENT ================= --}}
        <div class="tab-content">

            {{-- ================= ALL STUDENTS ================= --}}
            <div class="tab-pane fade show active" id="all-students">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>ADM No</th>
                        <th>Section</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>House</th>
                        <th>Fees</th>
                        <th>Year Admitted</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <img
                                    class="rounded-circle"
                                    style="height:40px;width:40px;object-fit:cover;"
                                    src="{{ optional($s->user)->photo ?? asset('images/default-user.png') }}"
                                    alt="photo"
                                >
                            </td>

                            <td>{{ $s->user->name ?? '' }}</td>
                            <td>{{ $s->adm_no }}</td>
                            <td>{{ $my_class->name.' '.$s->section->name }}</td>
                            <td>{{ $s->user->gender ?? '-' }}</td>
                            <td>{{ $s->age ?? '-' }}</td>
                            <td>{{ $s->house ?? '-' }}</td>

                            <td>
                                {{ $s->fees ?? '-' }}
                            </td>

                            <td>{{ $s->year_admitted ?? '-' }}</td>

                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item">
                                                <i class="icon-eye"></i> View Profile
                                            </a>

                                            @if(Qs::userIsTeamSA())
                                                <a href="{{ route('students.edit', Qs::hash($s->id)) }}" class="dropdown-item">
                                                    <i class="icon-pencil"></i> Edit
                                                </a>

                                                <a href="{{ route('st.reset_pass', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                                    <i class="icon-lock"></i> Reset Password
                                                </a>
                                            @endif

                                            <a target="_blank"
                                               href="{{ route('marks.year_selector', Qs::hash($s->user->id)) }}"
                                               class="dropdown-item">
                                                <i class="icon-check"></i> Marksheet
                                            </a>

                                            @if(Qs::userIsSuperAdmin())
                                                <a id="{{ Qs::hash($s->user->id) }}"
                                                   onclick="confirmDelete(this.id)"
                                                   href="#"
                                                   class="dropdown-item text-danger">
                                                    <i class="icon-trash"></i> Delete
                                                </a>

                                                <form method="post"
                                                      id="item-delete-{{ Qs::hash($s->user->id) }}"
                                                      action="{{ route('students.destroy', Qs::hash($s->user->id)) }}"
                                                      class="d-none">
                                                    @csrf
                                                    @method('delete')
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ================= SECTION TABS ================= --}}
            @foreach($sections as $se)
                <div class="tab-pane fade" id="s{{ $se->id }}">
                    <table class="table datatable-button-html5-columns">
                        <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>ADM No</th>
                            <th>Gender</th>
                            <th>Fees</th>
                            <th>Action</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($students->where('section_id', $se->id) as $sr)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <img
                                        class="rounded-circle"
                                        style="height:40px;width:40px;object-fit:cover;"
                                        src="{{ optional($sr->user)->photo ?? asset('images/default-user.png') }}"
                                    >
                                </td>

                                <td>{{ $sr->user->name ?? '' }}</td>
                                <td>{{ $sr->adm_no }}</td>
                                <td>{{ $sr->user->gender ?? '-' }}</td>

                                <td>
                                    {!! $sr->fees
                                        ? '<span class="badge badge-success">Paid</span>'
                                        : '<span class="badge badge-danger">Not Paid</span>' !!}
                                </td>

                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('students.show', Qs::hash($sr->id)) }}" class="dropdown-item">
                                                    <i class="icon-eye"></i> View Info
                                                </a>

                                                @if(Qs::userIsTeamSA())
                                                    <a href="{{ route('students.edit', Qs::hash($sr->id)) }}" class="dropdown-item">
                                                        <i class="icon-pencil"></i> Edit
                                                    </a>

                                                    <a href="{{ route('st.reset_pass', Qs::hash($sr->user->id)) }}" class="dropdown-item">
                                                        <i class="icon-lock"></i> Reset Password
                                                    </a>
                                                @endif

                                                <a target="_blank"
                                                   href="{{ route('marks.year_selector', Qs::hash($sr->user->id)) }}"
                                                   class="dropdown-item">
                                                    <i class="icon-check"></i> Marksheet
                                                </a>

                                                @if(Qs::userIsSuperAdmin())
                                                    <a id="{{ Qs::hash($sr->user->id) }}"
                                                       onclick="confirmDelete(this.id)"
                                                       href="#"
                                                       class="dropdown-item text-danger">
                                                        <i class="icon-trash"></i> Delete
                                                    </a>

                                                    <form method="post"
                                                          id="item-delete-{{ Qs::hash($sr->user->id) }}"
                                                          action="{{ route('students.destroy', Qs::hash($sr->user->id)) }}"
                                                          class="d-none">
                                                        @csrf
                                                        @method('delete')
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

        </div>
    </div>
</div>

@endsection
