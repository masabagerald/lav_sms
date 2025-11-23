@extends('layouts.master')
@section('page_title', 'Admit Student')
@section('content')
    <div class="card">
        <div class="card-header bg-white header-elements-inline">
            <h6 class="card-title">Please fill The form Below To Admit A New Student</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <form id="ajax-reg" method="post" enctype="multipart/form-data"
              class="wizard-form steps-validation"
              action="{{ route('students.store') }}" data-fouc>
            @csrf

            {{-- =========================
                PERSONAL DATA
            ========================== --}}
            <h6>Personal Data</h6>
            <fieldset>

                {{-- Row: Name + Address --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name: <span class="text-danger">*</span></label>
                            <input value="{{ old('name') }}" required type="text"
                                   name="name" placeholder="Full Name" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Address: <span class="text-danger">*</span></label>
                            <input value="{{ old('address') }}" required type="text"
                                   name="address" placeholder="Address" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Row: Gender + Religion + DOB --}}
                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="gender">Gender: <span class="text-danger">*</span></label>
                            <select class="select form-control" id="gender" name="gender" required data-fouc>
                                <option value=""></option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bg_id">Religion:</label>
                             <input name="religion" type="text" class="form-control"
                                   placeholder="type religion." value="{{ old('religion') }}">
                           
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date of Birth:</label>
                            <input name="dob" type="text" class="form-control date-pick"
                                   placeholder="Select Date..." value="{{ old('dob') }}">
                        </div>
                    </div>
                </div>

                {{-- Row: Nationality + District + Subcounty --}}
                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                            <select name="nal_id" id="nal_id" class="select-search form-control" required>
                                <option value=""></option>
                                @foreach($nationals as $nal)
                                    <option value="{{ $nal->id }}" {{ old('nal_id') == $nal->id ? 'selected' : '' }}>
                                        {{ $nal->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="state_id">District: <span class="text-danger">*</span></label>
                        <select name="state_id" id="state_id"
                                class="select-search form-control" required
                                onchange="getLGA(this.value)">
                            <option value=""></option>
                            @foreach($states as $st)
                                <option value="{{ $st->id }}" {{ old('state_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="lga_id">Subcounty: <span class="text-danger">*</span></label>
                        <select name="lga_id" id="lga_id"
                                class="select-search form-control" required>
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                {{-- Row: Photo --}}
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>PLE Results:</label>
                         <input value="{{ old('upe_results') }}" required type="text"
                                   name="upe_results" placeholder="UPE Results" class="form-control">
                       
                    </div>
                    <div class="col-md-4">
                        <label>UCE Results:</label>
                         <input value="{{ old('uce_results') }}" required type="text"
                                   name="uce_results" placeholder="UCE Results" class="form-control">
                        
                    </div>
                    <div class="col-md-4">
                        <label>Upload Passport Photo:</label>
                        <input type="file" name="photo" accept="image/*"
                               class="form-input-styled" data-fouc>
                        <span class="form-text text-muted">Accepted: jpeg, png. Max 2MB</span>
                    </div>
                </div>

            </fieldset>


            {{-- =========================
                GUARDIAN DETAILS
            ========================== --}}
            <h6>Guardian / Emergency Contact</h6>
            <fieldset>

                {{-- Row: Guardian Name + Email --}}
                <div class="row">
                    <div class="col-md-6">
                        <label>Parents / Guardian Name:</label>
                        <input type="text" name="guardian_name" class="form-control"
                               value="{{ old('guardian_name') }}" placeholder="Guardian full name">
                    </div>

                    <div class="col-md-6">
                        <label>Guardian Email:</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}" placeholder="Email Address">
                    </div>
                </div>

                {{-- Row: Phone + Alternate Phone --}}
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label>Guardian Phone Number: <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label>Alternate / Emergency Phone:</label>
                        <input type="text" name="alt_phone" class="form-control"
                               value="{{ old('alt_phone') }}">
                    </div>
                </div>

            </fieldset>


            {{-- =========================
                STUDENT DATA
            ========================== --}}
            <h6>Student Data</h6>
            <fieldset>

                {{-- Row: Class + Section --}}
                <div class="row">
                    <div class="col-md-6">
                        <label for="my_class_id">Class: <span class="text-danger">*</span></label>
                        <select required name="my_class_id" id="my_class_id"
                                class="select-search form-control"
                                onchange="getClassSections(this.value)">
                            <option value=""></option>
                            @foreach($my_classes as $c)
                                <option value="{{ $c->id }}" {{ old('my_class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="section_id">Section: <span class="text-danger">*</span></label>
                        <select required name="section_id" id="section_id"
                                class="select-search form-control">
                            <option value="{{ old('section_id') }}">
                                {{ old('section_id') ? 'Selected' : '' }}
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Row: Year Admitted + Admission Number --}}
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label for="year_admitted">Year Admitted: <span class="text-danger">*</span></label>
                        <select name="year_admitted" id="year_admitted"
                                class="select-search form-control" required>
                            <option value=""></option>
                            @for($y = date('Y') - 10; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ old('year_admitted') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Admission Number:</label>
                        <input type="text" name="adm_no" class="form-control"
                               value="{{ old('adm_no') }}" placeholder="Admission Number">
                    </div>
                </div>

                {{-- Row: Dormitory + Room Number + Sport House --}}
                <div class="row mt-2">
                    <div class="col-md-4">
                        <label for="dorm_id">Dormitory:</label>
                        <select name="dorm_id" id="dorm_id" class="select-search form-control">
                            <option value=""></option>
                            @foreach($dorms as $d)
                                <option value="{{ $d->id }}" {{ old('dorm_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Dormitory Room No:</label>
                        <input type="text" name="dorm_room_no" class="form-control"
                               value="{{ old('dorm_room_no') }}" placeholder="Room number">
                    </div>

                    <div class="col-md-4">
                        <label>Sport House:</label>
                        <input type="text" name="house" class="form-control"
                               value="{{ old('house') }}" placeholder="Sport House">
                    </div>
                </div>

                {{-- Row: Fees --}}
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label>Fees (per term):</label>
                        <input type="text" name="fees" class="form-control"
                               value="{{ old('fees') }}" placeholder="Fees per term">
                    </div>
                </div>

                {{-- NEW FIELD: General Comment --}}
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label>General Comment:</label>
                        <textarea name="general_comments" class="form-control"
                                  rows="3" placeholder="Any additional notes...">{{ old('general_comments') }}</textarea>
                    </div>
                </div>

            </fieldset>

        </form>
    </div>
@endsection
