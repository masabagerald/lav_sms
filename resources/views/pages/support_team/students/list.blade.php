@extends('layouts.master')

@section('page_title', 'Student Information - '.$my_class->name)

@section('content')

{{-- =========================
    STYLES
========================== --}}
<style>
#importOverlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
}
.import-overlay-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.45);
}
.import-overlay-card {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 25px 30px;
    border-radius: 8px;
    width: 360px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}
</style>

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Students List</h6>

        <div class="header-elements">
            @if(Qs::userIsTeamSA())
                <button type="button"
                        class="btn btn-sm btn-primary mr-2 lock-during-import"
                        data-toggle="modal"
                        data-target="#bulkUploadModal">
                    <i class="icon-upload mr-1"></i> Bulk Upload
                </button>
            @endif

            {!! Qs::getPanelOptions() !!}
        </div>
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
                                <img class="rounded-circle"
                                     style="height:40px;width:40px;object-fit:cover;"
                                     src="{{ optional($s->user)->photo ?? asset('images/default-user.png') }}">
                            </td>

                            <td>{{ $s->user->name ?? '' }}</td>
                            <td>{{ $s->adm_no }}</td>
                            <td>{{ $my_class->name.' '.$s->section->name }}</td>
                            <td>{{ $s->user->gender ?? '-' }}</td>
                            <td>{{ $s->age ?? '-' }}</td>
                            <td>{{ $s->house ?? '-' }}</td>
                            <td>{{ $s->fees ?? '-' }}</td>
                            <td>{{ $s->year_admitted ?? '-' }}</td>

                            <td class="text-center">
                                <div class="list-icons">
                                    <a href="#" class="list-icons-item lock-during-import"
                                       data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- =========================
    BULK UPLOAD MODAL
========================== --}}
<div class="modal fade" id="bulkUploadModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">

            <form method="POST"
                  action="{{ route('students.import') }}"
                  enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="my_class_id" value="{{ $my_class->id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Bulk Upload – {{ $my_class->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".xls,.xlsx"
                           required>
                    <small class="text-muted">Excel files only</small>
                </div>

                <div class="modal-footer">
                    <button type="submit"
                            class="btn btn-primary"
                            id="uploadBtn">
                        Upload Students
                    </button>
                    <button type="button"
                            class="btn btn-light"
                            data-dismiss="modal">
                        Cancel
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- =========================
    FULLSCREEN IMPORT OVERLAY
========================== --}}
<div id="importOverlay" style="display:none;">
    <div class="import-overlay-backdrop"></div>

    <div class="import-overlay-card">
        <h5>Importing Students</h5>
        <p class="text-muted">
            Please wait. Do not refresh or leave this page.
        </p>

        <div class="progress mb-2">
            <div id="importProgressBar"
                 class="progress-bar progress-bar-striped progress-bar-animated"
                 style="width:0%">0%</div>
        </div>

        <small id="importProgressText" class="text-muted">
            Preparing import…
        </small>
    </div>
</div>

{{-- =========================
    JAVASCRIPT
========================== --}}
<script>
const progressUrl = "{{ route('students.import.progress', $my_class->id) }}";
let pollInterval = null;

$('form[action="{{ route('students.import') }}"]').on('submit', function (e) {
    e.preventDefault();

    let formData = new FormData(this);
    lockUI();
    $('#bulkUploadModal').modal('hide');

    $.ajax({
        url: "{{ route('students.import') }}",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: startPolling,
        error: unlockUI
    });
});

function lockUI() {
    $('#importOverlay').fadeIn(150);
    $('.lock-during-import').prop('disabled', true);
}

function unlockUI() {
    clearInterval(pollInterval);
    $('#importOverlay').fadeOut(150);
    $('.lock-during-import').prop('disabled', false);
}

function startPolling() {
    pollInterval = setInterval(() => {
        $.get(progressUrl, function (data) {
            if (!data.total) return;

            let percent = Math.round((data.processed / data.total) * 100);

            $('#importProgressBar')
                .css('width', percent + '%')
                .text(percent + '%');

            $('#importProgressText')
                .text(`${data.processed} of ${data.total} students processed`);

            if (data.status === 'done' || data.processed >= data.total) {
                $('#importProgressBar')
                    .removeClass('progress-bar-animated')
                    .addClass('bg-success')
                    .text('Completed');

                setTimeout(() => {
                    unlockUI();
                    location.reload();
                }, 1200);
            }
        });
    }, 1200);
}

// Resume progress if page refreshed mid-import
$(document).ready(function () {
    $.get(progressUrl, function (data) {
        if (data.status === 'running' || data.status === 'queued') {
            lockUI();
            startPolling();
        }
    });
});
</script>

@endsection




