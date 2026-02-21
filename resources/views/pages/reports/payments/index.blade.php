@extends('layouts.master')

@section('content')
<div class="container-fluid">

    {{-- Title --}}
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-bold">Payment Report</h4>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalPaid = $payments->sum('amt_paid');
        $totalBalance = $payments->sum('balance');
    @endphp
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Amount Paid</h6>
                    <h4 class="fw-bold text-success">{{ number_format($totalPaid, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Balance</h6>
                    <h4 class="fw-bold text-danger">{{ number_format($totalBalance, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Records</h6>
                    <h4 class="fw-bold">{{ $payments->count() }}</h4>
                </div>
            </div>
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
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Term --}}
                <div class="col-md-2">
                    <label class="form-label">Payment Types</label>
                    <select name="payment_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($payment_types  as $payment)
                            <option value="{{ $payment->id }}" {{ $payment_id == $payment->id ? 'selected' : '' }}>{{ $payment->title }}</option>
                        @endforeach
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
                    <button class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="paymentTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Parent/Guardian</th>
                            <th>Parent/Guardian Contant</th>
                            <th>Class / Section</th>
                            <th>Adm No</th>
                             <th>Payment Type</th>
                            <th>Ref No</th>
                            <th class="text-end">Amount Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th>Year</th>
                           
                            <th>Last Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $record)
                            <tr class="{{ !$record->paid ? 'table-warning' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $record->student->name ?? 'N/A' }}</td>
                                <td>{{ $record->student->guardian_name?? 'N/A' }}</td>
                            
                                <td>{{ $record->student->student_record->guardian_phone ?? $record->student->student_record->user->phone }}</td>
                                <td>{{ $record->student->Student_record->class_section }}</td>
                                <td>{{ $record->student->Student_record->adm_no }}</td>
                                  <td>{{ $record->payment->title ?? ''}}</td>
                                <td>{{ $record->ref_no }}</td>
                                <td class="text-end fw-semibold text-success">{{ number_format($record->amt_paid, 2) }}</td>
                                <td class="text-end fw-semibold text-danger">{{ number_format($record->balance, 2) }}</td>
                                <td>
                                    @if($record->paid)
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $record->year }}</td>
                              
                                <td>{{ $record->updated_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No payment records found</td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Totals in tfoot --}}
                    @if($payments->count())
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">TOTAL</td>
                            <td class="text-end text-success">{{ number_format($totalPaid, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($totalBalance, 2) }}</td>
                            <td colspan="4"></td>
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

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];

    $('#paymentTable').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        responsive: true,
        order: [[0, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                filename: 'payment_report_' + today
            },
            {
                extend: 'csv',
                filename: 'payment_report_' + today
            },
            {
                extend: 'pdf',
                filename: 'payment_report_' + today
            }
        ]
    });
});
</script>



@endsection
