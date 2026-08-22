<div class="table-responsive">

<table class="table table-striped table-hover datatable-button-html5-columns">

<thead>
<tr>
<th>S/N</th>
<th>Student</th>
<th>ADM No</th>
<th>Section</th>
<th>Gender</th>
<th>Age</th>
<th>House</th>
<th>Fees</th>
<th>Adm. Year</th>
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
             src="{{ optional($s->user)->photo ?? Qs::getDefaultUserImage() }}"
             onerror="this.src='{{ Qs::getDefaultUserImage() }}'"
             alt="{{ optional($s->user)->name }}">
        <span class="cell-identity">
            <a href="{{ route('students.show',Qs::hash($s->id)) }}" class="name">{{ optional($s->user)->name }}</a>
        </span>
    </div>
</td>

<td><span class="font-weight-semibold">{{ $s->adm_no }}</span></td>

<td>{{ optional($s->section)->name }}</td>

<td>
    @if(optional($s->user)->gender)
        <span class="chip-gender {{ strtolower($s->user->gender) == 'male' ? 'chip-male' : 'chip-female' }}">{{ $s->user->gender }}</span>
    @else
        —
    @endif
</td>

<td>{{ $s->age }}</td>

<td>{{ $s->house ?: '—' }}</td>

<td class="money-cell">{{ $s->fees ? number_format((float) $s->fees) : '—' }}</td>

<td><span class="text-muted">{{ $s->year_admitted }}</span></td>

<td class="text-center">

<div class="dropdown">

<a class="list-icons-item" data-toggle="dropdown" aria-label="Actions for {{ optional($s->user)->name }}">
<i class="icon-menu9"></i>
</a>

<div class="dropdown-menu dropdown-menu-right">

<a href="{{ route('students.show',Qs::hash($s->id)) }}" class="dropdown-item">
<i class="icon-eye"></i> View Profile
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
   class="dropdown-item text-danger">
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
