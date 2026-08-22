<?php

namespace App\Services\Reports;

use App\Models\Exam;
use App\Models\MyClass;

/**
 * Academic performance reporting built on marks + exam_records.
 */
class AcademicReportService
{
    /** Exams available for a session (for the selector). */
    public function exams(string $session)
    {
        return Exam::where('year', $session)->orderBy('term')->get();
    }

    /**
     * Performance overview for an exam + class.
     * All aggregates computed in SQL.
     */
    public function overview(string $session, int $examId, int $classId): array
    {
        // Subject averages from marks (tca + exm = subject total)
        $subjects = DB::table('marks')
            ->join('subjects', 'subjects.id', '=', 'marks.subject_id')
            ->where('marks.year', $session)
            ->where('marks.exam_id', $examId)
            ->where('marks.my_class_id', $classId)
            ->groupBy('marks.subject_id', 'subjects.name')
            ->orderBy('subjects.name')
            ->selectRaw("subjects.name AS subject,
                COUNT(marks.id) AS entries,
                ROUND(AVG(marks.tca + marks.exm), 1) AS average,
                MAX(marks.tca + marks.exm) AS highest,
                MIN(marks.tca + marks.exm) AS lowest,
                SUM(CASE WHEN (marks.tca + marks.exm) >= 50 THEN 1 ELSE 0 END) AS passes")
            ->get()
            ->map(function ($r) {
                $r->pass_rate = $r->entries > 0 ? round($r->passes / $r->entries * 100, 1) : 0.0;

                return $r;
            });

        // Grade distribution via recorded grade_id
        $grades = DB::table('marks')
            ->join('grades', 'grades.id', '=', 'marks.grade_id')
            ->where('marks.year', $session)
            ->where('marks.exam_id', $examId)
            ->where('marks.my_class_id', $classId)
            ->groupBy('grades.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->selectRaw("grades.name AS grade, COUNT(*) AS total")
            ->pluck('total', 'grade');

        // Top students by exam_records average
        $top = DB::table('exam_records')
            ->join('users', 'users.id', '=', 'exam_records.student_id')
            ->where('exam_records.year', $session)
            ->where('exam_records.exam_id', $examId)
            ->where('exam_records.my_class_id', $classId)
            ->orderByDesc('exam_records.ave')
            ->limit(10)
            ->get([
                'users.name AS student',
                'exam_records.total',
                'exam_records.ave',
                'exam_records.pos',
            ]);

        $summary = DB::table('exam_records')
            ->where('year', $session)->where('exam_id', $examId)->where('my_class_id', $classId)
            ->selectRaw('COUNT(*) AS students, COALESCE(ROUND(AVG(ave),1),0) AS class_ave')
            ->first();

        return [
            'subjects'   => $subjects,
            'grades'     => $grades,
            'top'        => $top,
            'students'   => $summary->students ?? 0,
            'class_ave'  => $summary->class_ave ?? 0,
        ];
    }
}
