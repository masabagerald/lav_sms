@extends('layouts.master')

@section('content')
<div class="container-fluid">

    {{-- Title --}}
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-bold">Payment Report</h4>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3">

                {{-- Year --}}
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        @for ($y = now()->year; $y >= now()->year - 6; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- Class --}}
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="my_class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}"
                                {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Term --}}
                <div class="col-md-2">
                    <label class="form-label">Term</label>
                    <select name="term" class="form-select">
                        <option value="">All Terms</option>
                        <option value="Term 1" {{ $term == 'Term 1' ? 'selected' : '' }}>Term 1</option>
                        <option value="Term 2" {{ $term == 'Term 2' ? 'selected' : '' }}>Term 2</option>
                        <option value="Term 3" {{ $term == 'Term 3' ? 'selected' : '' }}>Term 3</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="paid" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ $paid === '1' ? 'selected' : '' }}>Paid</option>
                        <option value="0" {{ $paid === '0' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                {{-- Button --}}
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        Apply Filters
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="paymentTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Class/Section</th>
                             <th>Admission number</th>
                            <th>Ref No</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Year</th>
                            <th>Term</th>
                            <th>Last payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $record)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $record->student->name?? 'N/A' }}</td>
                                <td>{{ $record->student->Student_record->class_section }}</td>
                                <td>{{ $record->student->Student_record->adm_no }}</td>
                                <td>{{ $record->ref_no }}</td>
                                <td>{{ number_format($record->amt_paid, 2) }}</td>
                                <td>{{ number_format($record->balance, 2) }}</td>
                                <td>
                                    @if($record->paid)
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $record->year }}</td>
                                <td>{{ $record->term }}</td>
                                <td>{{ $record->updated_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    No payment records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script>
    $(document).ready(function () {
        $('#paymentTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            responsive: true
        });
    });
</script>
@endsection

