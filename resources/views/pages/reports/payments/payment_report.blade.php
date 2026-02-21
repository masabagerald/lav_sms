@extends('layouts.master')

@section('content')
<div class="container-fluid">

    {{-- Title --}}
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-bold">Student Payment Status Report</h4>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php
        $fullyPaid   = $students->where('payment_status', 1)->count();
        $partialPaid = $students->where('payment_status', 0)->count();
        $notPaid     = $students->where('payment_status', 2)->count();
        $totalStudents = $students->count();
    @endphp

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Fully Paid</h6>
                    <h4 class="fw-bold text-success">{{ $fullyPaid }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Partial Paid</h6>
                    <h4 class="fw-bold text-warning">{{ $partialPaid }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Not Paid</h6>
                    <h4 class="fw-bold text-danger">{{ $notPaid }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Students</h6>
                    <h4 class="fw-bold">{{ $totalStudents }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('students.payments') }}" class="row g-3">

                {{-- Year --}}
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        @for ($y = now()->year; $y >= now()->year - 6; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Class --}}
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="my_class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Type --}}
                <div class="col-md-3">
                    <label class="form-label">Payment Type</label>
                    <select name="payment_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($payment_types as $payment)
                            <option value="{{ $payment->id }}" {{ $payment_id == $payment->id ? 'selected' : '' }}>
                                {{ $payment->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="paid" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Partial Paid</option>
                        <option value="2" {{ $status === '2' ? 'selected' : '' }}>Not Paid</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="spaymentTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Guardian</th>
                            <th>Contact</th>
                            <th>Class</th>
                            <th>Stream</th>
                            <th>Adm No</th>
                            <th>Payment Type</th>
                            <th>Ref No</th>
                            <th class="text-end">Amount Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr class="
                                {{ $student->payment_status == 2 ? 'table-danger' : '' }}
                                {{ $student->payment_status == 0 ? 'table-warning' : '' }}
                            ">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $student->user->name ?? 'N/A' }}</td>
                                <td>{{ $student->guardian_name ?? 'N/A' }}</td>
                                <td>{{ $student->guardian_phone ?? $student->user->phone ?? 'N/A' }}</td>
                                <td>{{ $student->my_class->name ?? 'N/A' }}</td>
                                <td>{{ $student->section->name ?? 'N/A' }}</td>
                                <td>{{ $student->adm_no ?? 'N/A' }}</td>
                                <td>{{ $student->payment_type ?? '-' }}</td>
                                <td>{{ $student->ref_no ?? '-' }}</td>
                                <td class="text-end">{{ number_format($student->amt_paid, 2) }}</td>
                                <td class="text-end">{{ number_format($student->balance, 2) }}</td>
                                <td>
                                    @if($student->payment_status == 1)
                                        <span class="badge bg-success">Fully Paid</span>
                                    @elseif($student->payment_status == 0)
                                        <span class="badge bg-warning text-dark">Partial Paid</span>
                                    @else
                                        <span class="badge bg-danger">Not Paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Totals --}}
                    @if($students->count())
                    <tfoot class="table-secondary fw-bold text-end">
                        <tr>
                            <td colspan="9" class="text-end">TOTAL</td>
                            <td>{{ number_format($students->sum('amt_paid'), 2) }}</td>
                            <td>{{ number_format($students->sum('balance'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif

                </table>
            </div>
        </div>
    </div>

</div>

{{-- DataTables --}}
<script>
$(document).ready(function () {
    const today = new Date().toISOString().split('T')[0];

    $('#spaymentTable').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        responsive: true,
        order: [[0, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', filename: 'payment_report_' + today },
            { extend: 'csv', filename: 'payment_report_' + today },
            { extend: 'pdf', filename: 'payment_report_' + today }
        ]
    });
});
</script>
@endsection