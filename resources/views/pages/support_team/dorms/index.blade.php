@extends('layouts.master')
@section('page_title', 'Manage Dorms')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-home2 mr-2 text-primary"></i> Manage Dormitories
                <span class="stat-chip ml-2"><i class="icon-home2"></i>{{ $dorms->count() }} dorms</span>
            </h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-dorms" class="nav-link active" data-toggle="tab">Manage Dorms</a></li>
                <li class="nav-item"><a href="#new-dorm" class="nav-link" data-toggle="tab"><i class="icon-plus2"></i> Create New Dorm</a></li>
            </ul>

            <div class="tab-content">
                    <div class="tab-pane fade show active" id="all-dorms">
                        @if($dorms->isEmpty())
                            <div class="empty-state my-4">
                                <i class="icon-home2"></i>
                                <div class="empty-title">No dormitories registered</div>
                                <span class="text-muted">Create a dorm so boarding students can be allocated rooms.</span>
                            </div>
                        @else
                        <div class="table-responsive">
                        <table class="table table-hover datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dorms as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="font-weight-semibold">{{ $d->name }}</span></td>
                                    <td class="text-muted">{{ $d->description ?: '—'}}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown" aria-label="Actions for {{ $d->name }}">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if(Qs::userIsTeamSA())
                                                    {{--Edit--}}
                                                    <a href="{{ route('dorms.edit', $d->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                   @endif
                                                        @if(Qs::userIsSuperAdmin())
                                                    {{--Delete--}}
                                                    <a id="{{ $d->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item text-danger"><i class="icon-trash"></i> Delete</a>
                                                    <form method="post" id="item-delete-{{ $d->id }}" action="{{ route('dorms.destroy', $d->id) }}" class="hidden">@csrf @method('delete')</form>
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
                        @endif
                    </div>

                <div class="tab-pane fade" id="new-dorm">

                    <div class="row">
                        <div class="col-md-6">
                            <form class="ajax-store" method="post" action="{{ route('dorms.store') }}">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="Name of Dormitory">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Description</label>
                                    <div class="col-lg-9">
                                        <input name="description" value="{{ old('description') }}"  type="text" class="form-control" placeholder="Description of Dormitory">
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button id="ajax-btn" type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--Dorm List Ends--}}

@endsection
