<?php

namespace App\Http\Controllers\SupportTeam;

use App\Http\Controllers\Controller;
use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\StudentRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //

public function index(Request $request)
    {
        $currentYear = Carbon::now()->year;

   

        $year     = $request->get('year', $currentYear);
        $classId  = $request->get('my_class_id');
        $payment_id   = $request->get('payment_id');
        $paid     = $request->get('paid');

        $payments = PaymentRecord::with([
                'student.student_record.user',
                'student.student_record.my_class',
                'student.student_record.section',
                'payment'
            ])
            ->where('year', $year)
            ->when($classId, function ($q) use ($classId) {
                $q->whereHas('student.student_record', function ($q) use ($classId) {
                    $q->where('my_class_id', $classId);
                });
            })
             ->when($payment_id , function ($q) use ($payment_id ) {
               $q->where('payment_id', $payment_id);

             
            })
            
            ->when($paid !== null, function ($q) use ($paid) {
                $q->where('paid', $paid);
            })
            ->latest()
            ->get();

        $classes = MyClass::orderBy('name')->get();
        $payment_types = Payment::all();

      
        return view('pages.reports.payments.index', compact(
            'payments',
            'classes',
            'year',
            'classId',
            'payment_id',
       
            'paid',
            'payment_types'
        ));
    }



public function paymentReport(Request $request)
{
    $year       = $request->year ?? now()->year;
    $classId    = $request->my_class_id;
    $payment_id = $request->payment_id;
    $status     = $request->paid; 
    // 1 = fully paid
    // 0 = partial
    // 2 = not paid

    $classes = MyClass::all();
    $payment_types = Payment::all();

    $students = StudentRecord::with([
        'user',
        'my_class',
        'payment_records' => function ($query) use ($year, $payment_id) {
            $query->where('year', $year);

            if ($payment_id) {
                $query->where('payment_id', $payment_id);
            }
        }
    ])
    ->when($classId, function ($q) use ($classId) {
        $q->where('my_class_id', $classId);
    })
    ->get();

    // Determine computed status
    $students->map(function ($student) {
        $record = $student->payment_records->first();

        if (!$record) {
            $student->payment_status = 2; // Not paid
        } elseif ($record->paid == 1) {
            $student->payment_status = 1; // Fully paid
        } else {
            $student->payment_status = 0; // Partial
        }

        return $student;
    });

    // Filter by selected status
    if ($status !== null && $status !== '') {
        $students = $students->where('payment_status', (int)$status);
    }

    return view('pages.reports.payments.payment_report', compact(
        'students',
        'classes',
        'payment_types',
        'year',
        'classId',
        'payment_id',
        'status'
    ));
}





  

}
