@extends('layouts.master')

@section('page_title', 'Edit Student')

@section('content')

<div class="card">
    <div class="card-header bg-white header-elements-inline">
        <h6 class="card-title">
            Edit Student Record – {{ $sr->user->name }}
        </h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <form method="POST" enctype="multipart/form-data"
          class="wizard-form steps-validation ajax-update"
          action="{{ route('students.update', Qs::hash($sr->id)) }}"
          data-fouc>
        @csrf
        @method('PUT')

        {{-- =========================
            PERSONAL DATA
        ========================== --}}
        <h6>Personal Data</h6>
        <fieldset>

            {{-- Name + Address --}}
            <div class="row">
                <div class="col-md-6">
                    <label>Full Name: <span class="text-danger">*</span></label>
                    <input type="text" name="name" required
                           value="{{ $sr->user->name }}"
                           class="form-control">
                </div>

                <div class="col-md-6">
                    <label>Address: <span class="text-danger">*</span></label>
                    <input type="text" name="address"
                           value="{{ $sr->user->address }}"
                           class="form-control">
                </div>
            </div>

            {{-- Gender + Religion + DOB --}}
            <div class="row mt-2">
                <div class="col-md-4">
                    <label>Gender: <span class="text-danger">*</span></label>
                    <select name="gender" required class="select form-control">
                        <option value=""></option>
                        <option value="Male" {{ $sr->user->gender == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $sr->user->gender == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Religion:</label>
                    <input type="text" name="religion"
                           value="{{ $sr->user->religion }}"
                           class="form-control">
                </div>

                <div class="col-md-4">
                    <label>Date of Birth:</label>
                    <input type="text" name="dob"
                           value="{{ $sr->user->dob }}"
                           class="form-control date-pick">
                </div>
            </div>

            {{-- Nationality + District + Subcounty --}}
            <div class="row mt-2">
                <div class="col-md-4">
                    <label>Nationality: <span class="text-danger">*</span></label>
                    <select name="nal_id" class="select-search form-control">
                        <option value=""></option>
                        @foreach($nationals as $nal)
                            <option value="{{ $nal->id }}"
                                {{ $sr->user->nal_id == $nal->id ? 'selected' : '' }}>
                                {{ $nal->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>District: <span class="text-danger">*</span></label>
                    <select name="state_id"  class="select-search form-control">
                        <option value=""></option>
                        @foreach($states as $st)
                            <option value="{{ $st->id }}"
                                {{ $sr->user->state_id == $st->id ? 'selected' : '' }}>
                                {{ $st->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Subcounty: <span class="text-danger">*</span></label>
                    <input type="text" name="sub_county"
                           value="{{ $sr->user->sub_county }}"
                           class="form-control">
                </div>
            </div>

            {{-- Results + Photo --}}
            <div class="row mt-3">
                <div class="col-md-4">
                    <label>PLE Results:</label>
                    <input type="text" name="upe_results"
                           value="{{ $sr->upe_results }}"
                           class="form-control">
                </div>

                <div class="col-md-4">
                    <label>UCE Results:</label>
                    <input type="text" name="uce_results"
                           value="{{ $sr->uce_results }}"
                           class="form-control">
                </div>

                <div class="col-md-4">
                    <label>Upload Passport Photo:</label>
                    <input type="file" name="photo"
                           class="form-input-styled" data-fouc>
                </div>
            </div>

        </fieldset>

        {{-- =========================
            GUARDIAN DETAILS
        ========================== --}}
        <h6>Guardian / Emergency Contact</h6>
        <fieldset>

            <div class="row">
                <div class="col-md-6">
                    <label>Guardian Name:</label>
                    <input type="text" name="guardian_name"
                           value="{{ $sr->guardian_name }}"
                           class="form-control">
                </div>

                <div class="col-md-6">
                    <label>Guardian Email:</label>
                    <input type="email" name="email"
                           value="{{ $sr->user->email }}"
                           class="form-control">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label>Guardian Phone:</label>
                    <input type="text" name="phone"
                           value="{{ $sr->user->phone }}"
                           class="form-control">
                </div>

                <div class="col-md-6">
                    <label>Alternate Phone:</label>
                    <input type="text" name="alt_phone"
                           value="{{ $sr->user->phone2 }}"
                           class="form-control">
                </div>
            </div>

        </fieldset>

        {{-- =========================
            STUDENT DATA
        ========================== --}}
        <h6>Student Data</h6>
        <fieldset>

            <div class="row">
                <div class="col-md-6">
                    <label>Class: <span class="text-danger">*</span></label>
                    <select name="my_class_id" required
                            class="select-search form-control"
                            onchange="getClassSections(this.value)">
                        <option value=""></option>
                        @foreach($my_classes as $c)
                            <option value="{{ $c->id }}"
                                {{ $sr->my_class_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Section: <span class="text-danger">*</span></label>
                    <select name="section_id" required class="select-search form-control">
                        <option value="{{ $sr->section_id }}">{{ $sr->section->name }}</option>
                    </select>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label>Year Admitted:</label>
                    <select name="year_admitted" class="select-search form-control">
                        <option value=""></option>
                        @for($y = date('Y') - 10; $y <= date('Y'); $y++)
                            <option value="{{ $y }}"
                                {{ $sr->year_admitted == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Admission Number:</label>
                    <input type="text" name="adm_no"
                           value="{{ $sr->adm_no }}"
                           class="form-control">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4">
                    <label>Dormitory:</label>
                    <select name="dorm_id" class="select-search form-control">
                        <option value=""></option>
                        @foreach($dorms as $d)
                            <option value="{{ $d->id }}"
                                {{ $sr->dorm_id == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Dorm Room No:</label>
                    <input type="text" name="dorm_room_no"
                           value="{{ $sr->dorm_room_no }}"
                           class="form-control">
                </div>

                <div class="col-md-4">
                    <label>Sport House:</label>
                    <input type="text" name="house"
                           value="{{ $sr->house }}"
                           class="form-control">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label>Fees (per term):</label>
                    <input type="text" name="fees"
                           value="{{ $sr->fees }}"
                           class="form-control">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <label>General Comment:</label>
                    <textarea name="general_comments"
                              rows="3"
                              class="form-control">{{ $sr->general_comments }}</textarea>
                </div>
            </div>

        </fieldset>

    </form>
</div>

@endsection
