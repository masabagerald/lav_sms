<?php

namespace App\Services\Reports;

use App\Helpers\Qs;
use App\Models\MyClass;
use App\Models\StudentRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Student management reporting: register, demographics, enrollment.
 */
class StudentReportService
{
    public function sessions(): array
    {
        return StudentRecord::select('session')->distinct()->pluck('session')
            ->merge([Qs::getCurrentSession()])
            ->filter()->unique()->sortDesc()->values()->all();
    }

    /** Student register rows. */
    public function register(string $session, $classId = null)
    {
        return StudentRecord::query()
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->leftJoin('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_records.section_id')
            ->where('student_records.session', $session)
            ->when($classId, fn ($q) => $q->where('student_records.my_class_id', $classId))
            ->orderBy('my_classes.name')->orderBy('users.name')
            ->get([
                'student_records.adm_no',
                'users.name AS student',
                'users.gender',
                'users.dob',
                'my_classes.name AS class_name',
                'sections.name AS section_name',
                'student_records.guardian_name',
                DB::raw("COALESCE(student_records.guardian_phone, users.phone, '') AS contact"),
                'student_records.grad',
            ]);
    }

    /** Gender / age / boarding distributions in one pass. */
    public function demographics(string $session, $classId = null): array
    {
        $base = StudentRecord::query()
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->leftJoin('dorms', 'dorms.id', '=', 'student_records.dorm_id')
            ->where('student_records.session', $session)
            ->when($classId, fn ($q) => $q->where('student_records.my_class_id', $classId));

        $gender = (clone $base)->selectRaw("COALESCE(NULLIF(users.gender,''),'unspecified') AS g, COUNT(DISTINCT student_records.user_id) AS total")
            ->groupBy('g')->pluck('total', 'g');

        $ageYears = "DATE_PART('year', AGE(CURRENT_DATE, users.dob::timestamp))";
        $age = (clone $base)->selectRaw("
                CASE
                    WHEN users.dob IS NULL OR users.dob = '' THEN 'Unknown'
                    WHEN $ageYears < 12 THEN 'Under 12'
                    WHEN $ageYears < 14 THEN '12-13'
                    WHEN $ageYears < 16 THEN '14-15'
                    WHEN $ageYears < 18 THEN '16-17'
                    WHEN $ageYears < 20 THEN '18-19'
                    ELSE '20+'
                END AS bucket,
                COUNT(DISTINCT student_records.user_id) AS total")
            ->groupBy('bucket')->pluck('total', 'bucket');

        $boarding = (clone $base)->selectRaw("
                CASE WHEN student_records.dorm_id IS NULL THEN 'Day' ELSE COALESCE(dorms.name,'Boarding') END AS place,
                COUNT(DISTINCT student_records.user_id) AS total")
            ->groupBy('place')->orderByDesc('total')->pluck('total', 'place');

        $byClass = MyClass::withCount(['student_record' => fn ($q) => $q->where('session', $session)])
            ->when($classId, fn ($q) => $q->where('id', $classId))
            ->orderBy('name')->get()
            ->pluck('student_record_count', 'name')
            ->filter();

        $total = (clone $base)->distinct('student_records.user_id')->count('student_records.user_id');

        return compact('gender', 'age', 'boarding', 'byClass', 'total');
    }

    /** Enrollment: growth by admission year + new admissions this session. */
    public function enrollment(string $session): array
    {
        $growth = StudentRecord::query()
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->selectRaw("COALESCE(NULLIF(student_records.year_admitted,''),'Unknown') AS yr, COUNT(DISTINCT student_records.user_id) AS total")
            ->where('student_records.session', $session)
            ->groupBy('yr')->orderBy('yr')->get();

        [$startYear, $endYear] = array_pad(explode('-', $session), 2, $session);

        $newThisSession = StudentRecord::where('session', $session)
            ->whereBetween('created_at', [
                Carbon::createFromDate((int) $startYear, 1, 1)->startOfDay(),
                Carbon::createFromDate((int) $endYear, 12, 31)->endOfDay(),
            ])->count();

        $byClass = MyClass::withCount(['student_record' => fn ($q) => $q->where('session', $session)])
            ->orderBy('name')->get()
            ->map(fn ($c) => ['name' => $c->name, 'total' => $c->student_record_count]);

        $graduated = StudentRecord::where('session', $session)->where('grad', 1)->count();

        return [
            'growth'         => $growth,
            'new_admissions' => $newThisSession,
            'by_class'       => $byClass,
            'total'          => $byClass->sum('total'),
            'graduated'      => $graduated,
        ];
    }
}
