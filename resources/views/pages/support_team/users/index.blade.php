@extends('layouts.master')
@section('page_title', 'Staff & Users')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-users4 mr-2 text-primary"></i> Manage Staff &amp; Users</h6>
            <span class="stat-chip"><i class="icon-users2"></i>{{ $users->count() }} accounts</span>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#new-user" class="nav-link active" data-toggle="tab"><i class="icon-plus2 mr-1"></i> Create New User</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Browse by Role</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($user_types as $ut)
                            <a href="#ut-{{ Qs::hash($ut->id) }}" class="dropdown-item" data-toggle="tab">
                                {{ $ut->name }}s
                                <span class="badge badge-light border ml-2">{{ $users->where('user_type', $ut->title)->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="new-user">
                    <form method="post" enctype="multipart/form-data" class="wizard-form steps-validation ajax-store" action="{{ route('users.store') }}" data-fouc>
                        @csrf
                    <h6><i class="icon-user-tie mr-1"></i> Personal Data</h6>
                        <fieldset>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="user_type" class="req">User Role</label>
                                        <select required data-placeholder="Select role" class="form-control select" name="user_type" id="user_type">
                                @foreach($user_types as $ut)
                                            <option value="{{ Qs::hash($ut->id) }}">{{ $ut->name }}</option>
                                @endforeach
                                        </select>
                                        <span class="form-text text-muted">Determines what this account can access.</span>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="name" class="req">Full Name</label>
                                        <input value="{{ old('name') }}" required type="text" name="name" placeholder="e.g. Nakato Sarah" class="form-control" autocomplete="name">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="address" class="req">Address</label>
                                        <input value="{{ old('address') }}" class="form-control" placeholder="Physical / postal address" name="address" type="text" required autocomplete="street-address">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input value="{{ old('email') }}" type="email" name="email" id="email" class="form-control" placeholder="name@school.com" autocomplete="email">
                                        <span class="form-text text-muted">Used for login and password resets.</span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input value="{{ old('username') }}" type="text" name="username" id="username" class="form-control" placeholder="e.g. snakato" autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input value="{{ old('phone') }}" type="tel" name="phone" id="phone" class="form-control" placeholder="+256 7XX XXX XXX" autocomplete="tel">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="phone2">Alternate Phone</label>
                                        <input value="{{ old('phone2') }}" type="tel" name="phone2" id="phone2" class="form-control" placeholder="Optional" autocomplete="tel">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gender" class="req">Gender</label>
                                        <select class="select form-control" id="gender" name="gender" required data-fouc data-placeholder="Choose..">
                                            <option value=""></option>
                                            <option {{ (old('gender') == 'Male') ? 'selected' : '' }} value="Male">Male</option>
                                            <option {{ (old('gender') == 'Female') ? 'selected' : '' }} value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input id="password" type="password" name="password" class="form-control" placeholder="Leave blank to auto-generate" autocomplete="new-password">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="emp_date">Date of Employment</label>
                                        <input autocomplete="off" name="emp_date" value="{{ old('emp_date') }}" type="text" class="form-control date-pick" placeholder="Select date...">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="nal_id" class="req">Nationality</label>
                                        <select data-placeholder="Choose..." required name="nal_id" id="nal_id" class="select-search form-control">
                                            <option value=""></option>
                                            @foreach($nationals as $nal)
                                                <option {{ (old('nal_id') == $nal->id ? 'selected' : '') }} value="{{ $nal->id }}">{{ $nal->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{--State--}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state_id" class="req">State / Region</label>
                                        <select onchange="getLGA(this.value)" required data-placeholder="Choose.." class="select-search form-control" name="state_id" id="state_id">
                                            <option value=""></option>
                                            @foreach($states as $st)
                                                <option {{ (old('state_id') == $st->id ? 'selected' : '') }} value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{--LGA--}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="lga_id" class="req">District / LGA</label>
                                        <select required data-placeholder="Select state first" class="select-search form-control" name="lga_id" id="lga_id">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                                {{--BLOOD GROUP--}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bg_id">Blood Group</label>
                                        <select class="select form-control" id="bg_id" name="bg_id" data-fouc data-placeholder="Choose..">
                                            <option value=""></option>
                                            @foreach($blood_groups as $bg)
                                                <option {{ (old('bg_id') == $bg->id ? 'selected' : '') }} value="{{ $bg->id }}">{{ $bg->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                {{--PASSPORT--}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="d-block">Passport Photo</label>
                                        <input value="{{ old('photo') }}" accept="image/*" type="file" name="photo" class="form-input-styled" data-fouc>
                                        <span class="form-text text-muted">JPEG or PNG, max 2 MB.</span>
                                    </div>
                                </div>
                            </div>

                        </fieldset>



                    </form>
                </div>

                @foreach($user_types as $ut)
                    @php($typeUsers = $users->where('user_type', $ut->title))
                    <div class="tab-pane fade" id="ut-{{Qs::hash($ut->id)}}">
                        @if($typeUsers->isEmpty())
                            <div class="empty-state my-4">
                                <i class="icon-user-plus"></i>
                                <div class="empty-title">No {{ strtolower($ut->name) }} accounts yet</div>
                                <span class="text-muted">Use the “Create New User” tab to add the first one.</span>
                            </div>
                        @else
                        <div class="table-responsive">
                        <table class="table datatable-button-html5-columns table-hover">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($typeUsers as $u)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="avatar-sm mr-2" src="{{ $u->photo }}" alt="{{ $u->name }}">
                                            <span class="cell-identity">
                                                <span class="name">{{ $u->name }}</span>
                                                <span class="meta">{{ $u->email ?: 'No email' }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ $u->username }}</td>
                                    <td>{{ $u->phone ?: '—' }}</td>
                                    <td>{{ $u->email ?: '—' }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown" aria-label="Actions for {{ $u->name }}">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-right">
                                                    {{--View Profile--}}
                                                    <a href="{{ route('users.show', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                                    {{--Edit--}}
                                                    <a href="{{ route('users.edit', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                @if(Qs::userIsSuperAdmin())

                                                        <a href="{{ route('users.reset_pass', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-lock"></i> Reset password</a>
                                                        {{--Delete--}}
                                                        <a id="{{ Qs::hash($u->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item text-danger"><i class="icon-trash"></i> Delete</a>
                                                        <form method="post" id="item-delete-{{ Qs::hash($u->id) }}" action="{{ route('users.destroy', Qs::hash($u->id)) }}" class="hidden">@csrf @method('delete')</form>
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
                @endforeach

            </div>
        </div>
    </div>

    {{--User List Ends--}}

@endsection
