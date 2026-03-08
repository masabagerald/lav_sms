<div class="table-responsive">

<table class="table table-striped table-bordered datatable-button-html5-columns">

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
<th>Year</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@foreach($students as $s)

<tr>

<td>{{ $loop->iteration }}</td>

<td>
<img class="rounded-circle"
     style="width:40px;height:40px;object-fit:cover"
     src="{{ optional($s->user)->photo ?? asset('images/default-user.png') }}">
</td>

<td>{{ optional($s->user)->name }}</td>

<td>{{ $s->adm_no }}</td>

<td>{{ optional($s->section)->name }}</td>

<td>{{ optional($s->user)->gender }}</td>

<td>{{ $s->age }}</td>

<td>{{ $s->house }}</td>

<td>{{ $s->fees }}</td>

<td>{{ $s->year_admitted }}</td>

<td class="text-center">

<div class="dropdown">

<a class="list-icons-item" data-toggle="dropdown">
<i class="icon-menu9"></i>
</a>

<div class="dropdown-menu dropdown-menu-right">

<a href="{{ route('students.show',Qs::hash($s->id)) }}" class="dropdown-item">
<i class="icon-eye"></i> View
</a>

@if(Qs::userIsTeamSA())

<a href="{{ route('students.edit',Qs::hash($s->id)) }}" class="dropdown-item">
<i class="icon-pencil"></i> Edit
</a>

<a href="{{ route('st.reset_pass',Qs::hash(optional($s->user)->id)) }}" class="dropdown-item">
<i class="icon-lock"></i> Reset Password
</a>

@endif

@if(Qs::userIsSuperAdmin())

<a onclick="confirmDelete('{{ Qs::hash(optional($s->user)->id) }}')"
   class="dropdown-item">
<i class="icon-trash"></i> Delete
</a>

<form method="POST"
      id="item-delete-{{ Qs::hash(optional($s->user)->id) }}"
      action="{{ route('students.destroy',Qs::hash(optional($s->user)->id)) }}"
      class="d-none">

@csrf
@method('DELETE')

</form>

@endif

</div>
</div>

</td>

</tr>

@endforeach

</tbody>
</table>

</div>