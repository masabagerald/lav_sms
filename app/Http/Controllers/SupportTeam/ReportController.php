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


  

}
