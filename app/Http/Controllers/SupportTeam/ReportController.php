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
        $status     = $request->paid; // 1 = Fully Paid, 0 = Partial Paid, 2 = Not Paid

        $classes = MyClass::all();
        $payment_types = Payment::all();

        // Load students
        $students = StudentRecord::with(['user', 'my_class', 'section'])
            ->when($classId, fn($q) => $q->where('my_class_id', $classId))
            ->get();

        // Map payment info
        $students = $students->map(function($student) use ($year, $payment_id) {

            $paymentRecord = PaymentRecord::where('student_id', $student->user_id)
                ->where('year', $year)
                ->when($payment_id, fn($q) => $q->where('payment_id', $payment_id))
                ->first();

            if (!$paymentRecord) {
                $student->payment_status = 2; // Not Paid
                $student->amt_paid = 0;
                $student->balance = 0;
                $student->payment_type = null;
                $student->ref_no = null;
            } else {
                $student->amt_paid     = $paymentRecord->amt_paid;
                $student->balance      = $paymentRecord->balance;
                $student->payment_type = $paymentRecord->payment->title ?? 'N/A';
                $student->ref_no       = $paymentRecord->ref_no;
                $student->payment_status = $paymentRecord->paid ? 1 : 0;
            }

            return $student;
        });

        // Apply status filter
        if ($status !== null && $status !== '') {
            $students = $students->filter(fn($s) => $s->payment_status == $status);
        }

        return view('pages.reports.payments.payment_report', [
            'students'      => $students,
            'classes'       => $classes,
            'payment_types' => $payment_types,
            'year'          => $year,
            'classId'       => $classId,
            'payment_id'    => $payment_id,
            'status'        => $status,
        ]);
    }








  

}
