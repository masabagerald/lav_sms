<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\MyClass;
use App\Services\Reports\AcademicReportService;
use App\Services\Reports\FinanceReportService;
use App\Services\Reports\StudentReportService;
use Illuminate\Http\Request;
use PDF;

/**
 * Reporting hub.
 *
 * Route-level access control (see routes/web.php):
 *  - Finance routes   -> teamAccount
 *  - Student/Academic -> teamSAT
 */
class ReportController extends Controller
{
    protected $finance, $students, $academic, $classes;

    public function __construct(
        FinanceReportService $finance,
        StudentReportService $students,
        AcademicReportService $academic
    ) {
        $this->finance = $finance;
        $this->students = $students;
        $this->academic = $academic;
        $this->classes = MyClass::orderBy('name')->get(['id', 'name']);
    }

    /* ============================== HUB ============================== */

    public function index()
    {
        return view('pages.support_team.reports.hub');
    }

    /* ============================ FINANCE ============================ */

    public function payments(Request $req)
    {
        [$session, $term, $classId] = $this->scope($req);

        return view('pages.support_team.reports.finance_dashboard', [
            's'         => $this->finance->dashboardKpis($session, $term, $classId),
            'trend'     => $this->finance->monthlyTrend($session),
            'byClass'   => $this->finance->classBreakdown($session, $term),
            'recent'    => $this->finance->recentTransactions($session),
            'years'     => $this->finance->sessions(),
            'classes'   => $this->classes,
            'f_session' => $session,
            'f_term'    => $term,
            'f_class'   => $classId,
        ]);
    }

    public function feeStatus(Request $req)
    {
        [$session, $term, $classId] = $this->scope($req);
        $search = trim((string) $req->query('search', ''));
        $status = in_array($req->query('status'), ['paid', 'partial', 'unpaid'], true) ? $req->query('status') : 'all';

        [$rows, $paginator, $totals] = $this->finance->feeStatus($session, $term, $classId, $search, $status);

        if ($req->query('export') === 'csv') {
            return $this->csv('student_fee_status', $rows->map(fn ($r) => [
                $r['adm_no'], $r['student'], $r['class'], $r['section'],
                $r['expected'], $r['paid'], $r['balance'], ucfirst($r['state']), $r['contact'],
            ]), ['Adm No', 'Student', 'Class', 'Section', 'Expected', 'Paid', 'Balance', 'Status', 'Guardian Contact'],
            "Session: {$session}" . ($term ? " | Term: T{$term}" : '') . " | Students: {$totals['students']} | Expected: {$totals['expected']} | Paid: {$totals['paid']} | Balance: {$totals['balance']}");
        }

        return view('pages.support_team.reports.fee_status', [
            'rows'      => $rows,
            'paginator' => $paginator,
            'totals'    => $totals,
            'status'    => $status,
            'search'    => $search,
            'years'     => $this->finance->sessions(),
            'classes'   => $this->classes,
            'f_session' => $session,
            'f_term'    => $term,
            'f_class'   => $classId,
        ]);
    }

    public function debtors(Request $req)
    {
        [$session, $term, $classId] = $this->scope($req);
        $debtors = $this->finance->debtors($session, $term, $classId);

        if ($req->query('export') === 'csv') {
            return $this->csv('debtors_outstanding_fees', collect($debtors->items())->map(fn ($d) => [
                $d->adm_no, $d->student, $d->class_name, $d->paid, $d->balance,
            ]), ['Adm No', 'Student', 'Class', 'Paid', 'Outstanding Balance'],
            "Session: {$session} | Debtors: {$debtors->total()} | Total outstanding: " . collect($debtors->items())->sum('balance'));
        }

        return view('pages.support_team.reports.debtors', [
            'debtors'   => $debtors,
            'years'     => $this->finance->sessions(),
            'classes'   => $this->classes,
            'f_session' => $session,
            'f_term'    => $term,
            'f_class'   => $classId,
        ]);
    }

    public function daily(Request $req)
    {
        [$session, , ] = $this->scope($req);
        $from = $req->query('from');
        $to = $req->query('to');

        $grouped = $this->finance->dailyCollections($session, $from, $to);

        if ($req->query('export') === 'csv') {
            $flat = $grouped->flatten(1)->map(fn ($r) => [
                $r->pay_date, $r->student, $r->fee, $r->ref_no, $r->amt_paid,
            ]);

            return $this->csv('payment_reconciliation', $flat,
                ['Date', 'Student', 'Fee Type', 'Receipt Ref', 'Amount Received'],
                "Session: {$session}"
                . ($from ? " | From: {$from}" : '')
                . ($to ? " | To: {$to}" : '')
                . " | Transactions: " . $flat->count()
                . " | Total: " . $flat->sum(4));
        }

        return view('pages.support_team.reports.daily_collections', [
            'days'      => $grouped,
            'grand'     => $grouped->flatten(1)->sum('amt_paid'),
            'count'     => $grouped->flatten(1)->count(),
            'years'     => $this->finance->sessions(),
            'f_session' => $session,
            'f_from'    => $from,
            'f_to'      => $to,
        ]);
    }

    /* ============================ STUDENTS =========================== */

    public function register(Request $req)
    {
        [$session, , $classId] = $this->scope($req);
        $rows = $this->students->register($session, $classId)->sortBy('student')->values();

        if ($req->query('format') === 'pdf') {
            $pdf = PDF::loadView('pages.support_team.reports.print.register_pdf', [
                'rows'      => $rows,
                'school'    => Qs::getSetting('system_name'),
                'title'     => 'Student Register',
                'period'    => "Session {$session}" . ($classId && ($c = $this->classes->firstWhere('id', $classId)) ? " — {$c->name}" : ''),
                'generated' => now()->format('d M Y, H:i'),
                'by'        => auth()->user()->name,
            ])->setPaper('a4', 'landscape');

            return $pdf->download("student_register_{$session}.pdf");
        }

        if ($req->query('export') === 'csv') {
            return $this->csv('student_register', $rows->map(fn ($r) => [
                $r->adm_no, $r->student, ucfirst((string) $r->gender), $r->dob,
                trim(($r->class_name ?? '') . ' ' . ($r->section_name ?? '')),
                $r->guardian_name, $r->contact,
            ]), ['Adm No', 'Student', 'Gender', 'Date of Birth', 'Class', 'Guardian', 'Contact'],
            "Session: {$session} | Students: " . $rows->count());
        }

        return view('pages.support_team.reports.student_register', [
            'rows'      => $rows,
            'years'     => $this->students->sessions(),
            'classes'   => $this->classes,
            'f_session' => $session,
            'f_class'   => $classId,
        ]);
    }

    public function demographics(Request $req)
    {
        [$session, , $classId] = $this->scope($req);

        return view('pages.support_team.reports.demographics', [
            'd'         => $this->students->demographics($session, $classId),
            'years'     => $this->students->sessions(),
            'classes'   => $this->classes,
            'f_session' => $session,
            'f_class'   => $classId,
        ]);
    }

    public function enrollment(Request $req)
    {
        [$session, , ] = $this->scope($req);

        return view('pages.support_team.reports.enrollment', [
            'e'         => $this->students->enrollment($session),
            'years'     => $this->students->sessions(),
            'f_session' => $session,
        ]);
    }

    /* ============================ ACADEMIC =========================== */

    public function academic(Request $req)
    {
        [$session, , ] = $this->scope($req);
        $exams = $this->academic->exams($session);
        $examId = (int) ($req->query('exam_id') ?: optional($exams->first())->id);
        $classId = (int) ($req->query('my_class_id') ?: optional($this->classes->first())->id);

        $data = null;
        if ($examId && $classId) {
            $data = $this->academic->overview($session, $examId, $classId);
        }

        return view('pages.support_team.reports.academic_overview', [
            'data'      => $data,
            'exams'     => $exams,
            'years'     => $this->students->sessions(),
            'classes'   => $this->classes,
            'f_session' => $session,
            'f_exam'    => $examId,
            'f_class'   => $classId,
        ]);
    }

    /* ============================ HELPERS ============================ */

    /** Shared filter scope: session defaults to the current academic session. */
    protected function scope(Request $req): array
    {
        $session = $req->query('year') ?: Qs::getCurrentSession();
        $term = $req->query('term') ?: null;
        $classId = $req->query('my_class_id') ?: null;

        return [(string) $session, $term ? (int) $term : null, $classId ? (int) $classId : null];
    }

    /** Stream a CSV download with a metadata preamble row. */
    protected function csv(string $name, $rows, array $header, string $meta)
    {
        return response()->streamDownload(function () use ($rows, $header, $meta) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, [Qs::getSetting('system_name')]);
            fputcsv($out, [$meta]);
            fputcsv($out, ['Generated: ' . now()->format('d M Y H:i') . ' by ' . (auth()->user()->name ?? '')]);
            fputcsv($out, []);
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => (string) $v, is_array($row) ? $row : $row->toArray()));
            }
            fclose($out);
        }, "{$name}_" . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
