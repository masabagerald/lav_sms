@extends('layouts.master')

@section('page_title', 'Student Information - '.$my_class->name)

@section('content')

<style>
#importOverlay{
    position:fixed;
    inset:0;
    z-index:9999;
}

.import-overlay-backdrop{
    position:absolute;
    inset:0;
    background:rgba(0,0,0,.45);
}

.import-overlay-card{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:#fff;
    padding:30px;
    border-radius:8px;
    width:360px;
    text-align:center;
    box-shadow:0 10px 40px rgba(0,0,0,.3);
}

.table-responsive{
    overflow-x:auto;
}
</style>

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h6 class="card-title mb-0">Students List</h6>

        <div>
            @if(Qs::userIsTeamSA())
                <button class="btn btn-primary btn-sm"
                        data-toggle="modal"
                        data-target="#bulkUploadModal">
                    <i class="icon-upload mr-1"></i> Bulk Upload
                </button>
            @endif

            {!! Qs::getPanelOptions() !!}
        </div>

    </div>

    <div class="card-body">

        {{-- Tabs --}}
        <ul class="nav nav-tabs nav-tabs-highlight mb-3">

            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#allStudents">
                    All {{ $my_class->name }}
                </a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                    Sections
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($sections as $s)
                        <a class="dropdown-item"
                           data-toggle="tab"
                           href="#section{{ $s->id }}">
                            {{ $my_class->name }} {{ $s->name }}
                        </a>
                    @endforeach
                </div>
            </li>

        </ul>

        <div class="tab-content">

            {{-- ALL STUDENTS --}}
            <div class="tab-pane fade show active" id="allStudents">

                @include('pages.support_team.students.partials.students-table',[
                    'students'=>$students,
                    'my_class'=>$my_class
                ])

            </div>


            {{-- SECTION STUDENTS --}}
            @foreach($sections as $se)

                <div class="tab-pane fade" id="section{{$se->id}}">

                    @include('pages.support_team.students.partials.students-table',[
                        'students'=>$students->where('section_id',$se->id),
                        'my_class'=>$my_class
                    ])

                </div>

            @endforeach

        </div>

    </div>

</div>



{{-- BULK UPLOAD MODAL --}}
<div class="modal fade" id="bulkUploadModal">

    <div class="modal-dialog modal-sm">

        <form method="POST"
              action="{{ route('students.import') }}"
              enctype="multipart/form-data"
              class="modal-content">

            @csrf

            <input type="hidden"
                   name="my_class_id"
                   value="{{ $my_class->id }}">

            <div class="modal-header">
                <h5 class="modal-title">
                    Bulk Upload – {{ $my_class->name }}
                </h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <input type="file"
                       name="file"
                       class="form-control"
                       accept=".xls,.xlsx"
                       required>

                <small class="text-muted">Excel only</small>

            </div>

            <div class="modal-footer">

                <button class="btn btn-primary" id="uploadBtn">
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



{{-- IMPORT / DELETE OVERLAY --}}
<div id="importOverlay" style="display:none">

    <div class="import-overlay-backdrop"></div>

    <div class="import-overlay-card">

        <div class="spinner-border text-primary mb-3"></div>

        <h5 id="overlayTitle">Processing...</h5>

        <p id="overlayMessage" class="text-muted">
            Please wait...
        </p>

        <div class="progress mb-2" id="importProgressWrapper">

            <div id="importProgressBar"
                 class="progress-bar progress-bar-striped progress-bar-animated"
                 style="width:0%">
                 0%
            </div>

        </div>

        <small id="importProgressText">
            Preparing import…
        </small>

    </div>

</div>



<script>

const progressUrl = "{{ route('students.import.progress',$my_class->id) }}";

let pollInterval=null;



/* IMPORT */

$('form[action="{{ route('students.import') }}"]').submit(function(e){

    e.preventDefault();

    let formData=new FormData(this);

    lockUI();

    $('#bulkUploadModal').modal('hide');

    $.ajax({

        url:"{{ route('students.import') }}",

        method:"POST",

        data:formData,

        processData:false,

        contentType:false,

        success:startPolling,

        error:unlockUI

    });

});



function lockUI(){

    $('#overlayTitle').text('Importing students');

    $('#overlayMessage').text('Please wait while students are uploaded.');

    $('#importProgressWrapper').show();

    $('#importOverlay').fadeIn(150);

}



function unlockUI(){

    clearInterval(pollInterval);

    $('#importOverlay').fadeOut(150);

}



function startPolling(){

    pollInterval=setInterval(()=>{

        $.get(progressUrl,function(data){

            if(!data.total) return;

            let percent=Math.round((data.processed/data.total)*100);

            $('#importProgressBar')

                .css('width',percent+'%')

                .text(percent+'%');

            $('#importProgressText')

                .text(`${data.processed} of ${data.total} processed`);

            if(data.status==='done'||data.processed>=data.total){

                setTimeout(()=>{

                    unlockUI();

                    location.reload();

                },1200);

            }

        });

    },1200);

}



/* DELETE */

function confirmDelete(id){

    if(!confirm('Delete this student permanently?')) return;

    $('#overlayTitle').text('Deleting student');

    $('#overlayMessage').text('Please wait while the student is removed.');

    $('#importProgressWrapper').hide();

    $('#importOverlay').fadeIn(150);

    document.getElementById('item-delete-'+id).submit();

}



</script>

@endsection