@extends('layouts.master')
@section('page_title', 'Admit Student')

@push('styles')
<style>
    /* ---- Micro labels ---- */
    .ctl-label {
        display: block; font-size: .72rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
        color: #6c757d; margin-bottom: .35rem;
    }
    .ctl-label .req { color: #e53935; }

    /* ---- Step intro banner ---- */
    .reg-step-head {
        display: flex; align-items: center; gap: .85rem;
        background: #f6f9fe; border-left: 3px solid #1f4e8c;
        border-radius: .4rem; padding: .8rem 1rem; margin-bottom: 1.4rem;
    }
    .reg-step-head .rh-icon {
        flex: 0 0 auto; width: 42px; height: 42px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: #e3edfb; color: #1f4e8c; font-size: 1.05rem;
    }
    .reg-step-head h5 { margin: 0; font-weight: 600; font-size: 1rem; }
    .reg-step-head small { color: #8895a7; }

    /* ---- Input group icons ---- */
    .reg-form .input-group-text { color: #8895a7; background: #f8f9fa; min-width: 42px; justify-content: center; }

    /* ---- Gender pills ---- */
    .gender-toggle { display: flex; gap: .55rem; }
    .g-pill {
        position: relative; flex: 1; display: flex; align-items: center; justify-content: center;
        gap: .45rem; padding: .58rem .5rem; border: 1px solid #d5dce4; border-radius: .45rem;
        font-weight: 600; color: #666; cursor: pointer; transition: all .15s ease; user-select: none;
        margin: 0;
    }
    .g-pill input { position: absolute; opacity: 0; pointer-events: none; }
    .g-pill i { font-size: 1rem; }
    .g-pill:hover { border-color: #9fb6d4; }
    .g-pill.active {
        border-color: #1f4e8c; background: #eef4fc; color: #16305c;
        box-shadow: 0 0 0 2px rgba(31,78,140,.12);
    }

    /* ---- Photo upload box ---- */
    .upload-box {
        position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .15rem; min-height: 116px; height: calc(100% - 26px); border: 2px dashed #ccd5df;
        border-radius: .5rem; background: #fafbfd; cursor: pointer; text-align: center;
        transition: all .15s ease; overflow: hidden; margin: 0;
    }
    .upload-box:hover { border-color: #1f4e8c; background: #f0f6ff; }
    .upload-box .u-icon { font-size: 1.55rem; color: #9aa7b8; }
    .upload-box.has-photo { border-style: solid; border-color: #43a047; background: #f4faf4; }
    .upload-box img { width: 70px; height: 70px; object-fit: cover; border-radius: .45rem; box-shadow: 0 1px 4px rgba(0,0,0,.18); }
    #photo-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

    /* ---- Fees prefix ---- */
    .fees-prepend { font-weight: 700; color: #2e7d32; letter-spacing: .03em; }
</style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header bg-white header-elements-inline">
            <div class="pt-2 pb-2">
                <h6 class="card-title mb-0"><i class="icon-user-plus mr-2 text-primary"></i>Admit New Student</h6>
                <span class="d-block text-muted mt-1" style="font-size:.8rem;">
                    Complete the three steps below. Fields marked <span class="text-danger">*</span> are required.
                </span>
            </div>
            {!! Qs::getPanelOptions() !!}
        </div>

        <form id="ajax-reg" method="post" enctype="multipart/form-data"
              class="wizard-form steps-validation reg-form"
              action="{{ route('students.store') }}" data-fouc>
            @csrf

            {{-- =========================
                 STEP 1 · PERSONAL DATA
             ========================== --}}
            <h6>Personal Data</h6>
            <fieldset>

                <div class="reg-step-head">
                    <span class="rh-icon"><i class="icon-user"></i></span>
                    <div>
                        <h5>Personal Information</h5>
                        <small>Basic identity and origin of the student</small>
                    </div>
                </div>

                {{-- Row: Name + Address --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label">Full Name <span class="req">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-user"></i></span>
                                </div>
                                <input value="{{ old('name') }}" required type="text" name="name"
                                       placeholder="Surname & given names" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label">Address <span class="req">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-home"></i></span>
                                </div>
                                <input value="{{ old('address') }}" required type="text" name="address"
                                       placeholder="Village / street, town" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Gender + Religion + DOB --}}
                <div class="row mt-1">
                    <div class="col-md-4">
                        <div class="form-group form-group-sm-none">
                            <label class="ctl-label">Gender <span class="req">*</span></label>
                            <div class="gender-toggle">
                                <label class="g-pill {{ old('gender') == 'Male' ? 'active' : '' }}">
                                    <input type="radio" name="gender" value="Male"
                                           {{ old('gender') == 'Male' ? 'checked' : '' }} required>
                                    <i class="icon-man"></i><span>Male</span>
                                </label>
                                <label class="g-pill {{ old('gender') == 'Female' ? 'active' : '' }}">
                                    <input type="radio" name="gender" value="Female"
                                           {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                    <i class="icon-woman"></i><span>Female</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="ctl-label">Religion</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-book"></i></span>
                                </div>
                                <input name="religion" type="text" class="form-control"
                                       placeholder="e.g. Christian, Muslim" value="{{ old('religion') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="ctl-label">Date of Birth</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-calendar52"></i></span>
                                </div>
                                <input name="dob" type="text" class="form-control date-pick"
                                       placeholder="Select date" value="{{ old('dob') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Nationality + District + Subcounty --}}
                <div class="row mt-1">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="ctl-label" for="nal_id">Nationality <span class="req">*</span></label>
                            <select name="nal_id" id="nal_id" class="select-search form-control" required data-fouc>
                                <option value="">Choose nationality</option>
                                @foreach($nationals as $nal)
                                    <option value="{{ $nal->id }}" {{ old('nal_id') == $nal->id ? 'selected' : '' }}>
                                        {{ $nal->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="ctl-label" for="state_id">District <span class="req">*</span></label>
                            <select name="state_id" id="state_id" class="select-search form-control"
                                    required onchange="getLGA(this.value)" data-fouc>
                                <option value="">Select district</option>
                                @foreach($states as $st)
                                    <option value="{{ $st->id }}" {{ old('state_id') == $st->id ? 'selected' : '' }}>
                                        {{ $st->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="ctl-label">Subcounty</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-map4"></i></span>
                                </div>
                                <input name="sub_county" type="text" class="form-control"
                                       placeholder="Subcounty / division" value="{{ old('sub_county') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: PLE + UCE results + Photo upload --}}
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="ctl-label">PLE Results</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-file-text2"></i></span>
                                </div>
                                <input value="{{ old('upe_results') }}" type="text" name="upe_results"
                                       placeholder="Aggregate / division" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="ctl-label">UCE Results</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-file-text2"></i></span>
                                </div>
                                <input value="{{ old('uce_results') }}" type="text" name="uce_results"
                                       placeholder="Aggregate / division" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="ctl-label">Passport Photo</label>
                        <label class="upload-box" id="photo-box">
                            <i class="icon-image4 u-icon"></i>
                            <span class="font-weight-semibold">Click to upload photo</span>
                            <span class="text-muted" style="font-size:.72rem;">JPEG or PNG · max 2MB</span>
                            <img id="photo-preview" src="" alt="" class="d-none mt-1">
                            <input type="file" name="photo" id="photo-input" accept="image/png,image/jpeg">
                        </label>
                    </div>
                </div>

            </fieldset>


            {{-- =========================
                 STEP 2 · GUARDIAN DETAILS
             ========================== --}}
            <h6>Guardian / Emergency Contact</h6>
            <fieldset>

                <div class="reg-step-head">
                    <span class="rh-icon"><i class="icon-users2"></i></span>
                    <div>
                        <h5>Guardian / Emergency Contact</h5>
                        <small>The person we reach about this student</small>
                    </div>
                </div>

                {{-- Row: Guardian Name + Email --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label">Parent / Guardian Name</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-user-tie"></i></span>
                                </div>
                                <input type="text" name="guardian_name" class="form-control"
                                       value="{{ old('guardian_name') }}" placeholder="Guardian's full name">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label">Guardian Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-envelop"></i></span>
                                </div>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email') }}" placeholder="example@email.com">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Phone + Alternate Phone --}}
                <div class="row mt-1">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label">Guardian Phone Number <span class="req">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-phone"></i></span>
                                </div>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone') }}" placeholder="07XX XXX XXX" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label">Alternate / Emergency Phone</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-phone"></i></span>
                                </div>
                                <input type="text" name="alt_phone" class="form-control"
                                       value="{{ old('alt_phone') }}" placeholder="Optional second number">
                            </div>
                        </div>
                    </div>
                </div>

            </fieldset>


            {{-- =========================
                 STEP 3 · STUDENT DATA
             ========================== --}}
            <h6>Student Data</h6>
            <fieldset>

                <div class="reg-step-head">
                    <span class="rh-icon"><i class="icon-office"></i></span>
                    <div>
                        <h5>Academic &amp; Boarding Details</h5>
                        <small>Class placement, residence and fees</small>
                    </div>
                </div>

                {{-- Row: Class + Section --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label" for="my_class_id">Class <span class="req">*</span></label>
                            <select required name="my_class_id" id="my_class_id"
                                    class="select-search form-control"
                                    onchange="getClassSections(this.value)" data-fouc>
                                <option value="">Select class</option>
                                @foreach($my_classes as $c)
                                    <option value="{{ $c->id }}" {{ old('my_class_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label" for="section_id">Section (Stream) <span class="req">*</span></label>
                            <select required name="section_id" id="section_id"
                                    class="select-search form-control" data-fouc>
                                <option value="{{ old('section_id') }}">
                                    {{ old('section_id') ? 'Selected — pick class again to change' : 'Select class first' }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Row: Year admitted + Dormitory + Room + House --}}
                <div class="row mt-1">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="ctl-label" for="year_admitted">Year Admitted <span class="req">*</span></label>
                            <select name="year_admitted" id="year_admitted"
                                    class="select-search form-control" required data-fouc>
                                <option value=""></option>
                                @for($y = date('Y') - 10; $y <= date('Y'); $y++)
                                    <option value="{{ $y }}" {{ old('year_admitted') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="ctl-label" for="dorm_id">Dormitory</label>
                            <select name="dorm_id" id="dorm_id" class="select-search form-control" data-fouc>
                                <option value=""></option>
                                @foreach($dorms as $d)
                                    <option value="{{ $d->id }}" {{ old('dorm_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="ctl-label">Room No.</label>
                            <input type="text" name="dorm_room_no" class="form-control"
                                   value="{{ old('dorm_room_no') }}" placeholder="e.g. R12">
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="ctl-label">Sport House</label>
                            <input type="text" name="house" class="form-control"
                                   value="{{ old('house') }}" placeholder="Sport house">
                        </div>
                    </div>
                </div>

                {{-- Row: Fees --}}
                <div class="row mt-1">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ctl-label">Fees per Term <span class="req">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text fees-prepend">UGX</span>
                                </div>
                                <input type="number" min="0" step="500" name="fees" class="form-control"
                                       value="{{ old('fees') }}" placeholder="e.g. 300000" required>
                            </div>
                            <span class="form-text text-muted">Amount expected from the guardian every term.</span>
                        </div>
                    </div>
                </div>

                {{-- General comment --}}
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label class="ctl-label">General Comment</label>
                            <textarea name="general_comments" class="form-control" rows="3"
                                      placeholder="Any additional notes about this student...">{{ old('general_comments') }}</textarea>
                        </div>
                    </div>
                </div>

            </fieldset>

        </form>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    // ---- Gender pill active state ----
    function syncPills() {
        $('.g-pill').each(function () {
            $(this).toggleClass('active', $(this).find('input').prop('checked'));
        });
    }
    $('.g-pill input').on('change', syncPills);
    syncPills();

    // ---- Passport photo live preview ----
    var photoInput = document.getElementById('photo-input');
    if (photoInput) {
        photoInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.getElementById('photo-preview');
                img.src = e.target.result;
                img.classList.remove('d-none');
                $('#photo-box').addClass('has-photo')
                    .find('.u-icon').first().hide();
            };
            reader.readAsDataURL(file);
        });
    }
})();
</script>
@endsection
