<?php

namespace App\Http\Controllers\SupportTeam;

use App\Http\Controllers\Controller;
use App\Models\MyClass;
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
        $term     = $request->get('term');
        $paid     = $request->get('paid');

        $payments = PaymentRecord::with([
                'student.student_record.user',
                'student.student_record.my_class',
                'student.student_record.section'
            ])
            ->where('year', $year)
            ->when($classId, function ($q) use ($classId) {
                $q->whereHas('student', function ($q) use ($classId) {
                    $q->where('my_class_id', $classId);
                });
            })
            ->when($term, function ($q) use ($term) {
                $q->where('term', $term);
            })
            ->when($paid !== null, function ($q) use ($paid) {
                $q->where('paid', $paid);
            })
            ->latest()
            ->get();

        $classes = MyClass::orderBy('name')->get();

      
        return view('pages.reports.payments.index', compact(
            'payments',
            'classes',
            'year',
            'classId',
            'term',
            'paid'
        ));
    }


  

}
