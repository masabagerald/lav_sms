<?php

namespace App\Services\Reports;

use App\Helpers\Qs;
use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\Receipt;
use App\Models\StudentRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Finance reporting queries.
 *
 * All heavy aggregation happens in SQL. Definitions used across reports:
 *  - EXPECTED    : fee structure (payments.amount x enrolled students) for scope
 *  - COLLECTED   : sum(payment_records.amt_paid) for scope
 *  - OUTSTANDING : EXPECTED - COLLECTED (floored at zero)
 *  - RATE        : COLLECTED / EXPECTED * 100 (0 when EXPECTED is 0)
 */
class FinanceReportService
{
    /** Session filter options (newest first, always includes current session). */
    public function sessions(): array
    {
        return Payment::select('year')->distinct()->pluck('year')
            ->merge(StudentRecord::select('session')->distinct()->pluck('session'))
            ->merge([Qs::getCurrentSession()])
            ->filter()->unique()->sortDesc()->values()->all();
    }

    protected function isCurrent(string $session): bool
    {
        return $session === Qs::getCurrentSession();
    }

    /**
     * Expected fees for a scope from the FEE STRUCTURE side, so students who were
     * never billed still count towards outstanding balances.
     */
    public function expectedFees(string $session, $term = null, $classId = null): int
    {
        // NOTE: the payments (fee structure) table has no term column;
        // term scoping applies to collections (payment_records) only.
        $q = Payment::where('year', $session);

        if ($classId) {
            // Class-specific fees for this class x its enrolment + general fees x all enrolment
            $classFee = (int) (clone $q)->where('my_class_id', $classId)->sum('amount');
            $generalFee = (int) (clone $q)->whereNull('my_class_id')->sum('amount');
            $students = StudentRecord::where('session', $session)->where('my_class_id', $classId)->count();

            return ($classFee + $generalFee) * $students;
        }

        // Whole school: sum(class fees x class enrolment) + general fees x total enrolment
        $perClass = (clone $q)->whereNotNull('payments.my_class_id')
            ->join('student_records', function ($j) use ($session) {
                $j->on('student_records.my_class_id', '=', 'payments.my_class_id')
                    ->where('student_records.session', $session);
            })
            ->selectRaw('COALESCE(SUM(payments.amount),0) AS total')
            ->value('total');

        $generalPerStudent = (int) (clone $q)->whereNull('my_class_id')->sum('amount');
        $students = StudentRecord::where('session', $session)->count();

        return ((int) $perClass) + ($generalPerStudent * $students);
    }

    /** Base payment-record query for a scope. */
    protected function recordQuery(string $session, $term = null, $classId = null)
    {
        return PaymentRecord::query()
            ->where('payment_records.year', $session)
            ->when($term, fn ($qq) => $qq->where(function ($w) use ($term) {
                $w->whereNull('payment_records.term')->orWhere('payment_records.term', $term);
            }))
            ->when($classId, fn ($qq) => $qq->whereHas('payment', fn ($p) => $p->where('my_class_id', $classId)));
    }

    /** Headline KPIs + student payment-status breakdown. */
    public function dashboardKpis(string $session, $term = null, $classId = null): array
    {
        $expected = $this->expectedFees($session, $term, $classId);
        $collected = (int) $this->recordQuery($session, $term, $classId)->sum('amt_paid');

        // One grouped query => student-level paid/balance totals
        $rows = DB::table('payment_records')
            ->join('student_records', 'student_records.user_id', '=', 'payment_records.student_id')
            ->where('payment_records.year', $session)
            ->when($term, fn ($qq) => $qq->where(function ($w) use ($term) {
                $w->whereNull('payment_records.term')->orWhere('payment_records.term', $term);
            }))
            ->when($classId, fn ($qq) => $qq->where('student_records.my_class_id', $classId))
            ->groupBy('payment_records.student_id')
            ->selectRaw('payment_records.student_id,
                COALESCE(SUM(payment_records.amt_paid),0) AS paid_total,
                COALESCE(SUM(payment_records.balance),0) AS balance_total,
                MIN(payment_records.paid) AS all_cleared')
            ->get();

        $enrolledQ = StudentRecord::where('session', $session);
        if ($classId) {
            $enrolledQ->where('my_class_id', $classId);
        }
        $enrolled = (clone $enrolledQ)->distinct('user_id')->count('user_id');

        $fully = $rows->filter(fn ($r) => $r->all_cleared == 1 && $r->balance_total <= 0)->count();
        $partial = $rows->filter(fn ($r) => $r->paid_total > 0 && !($r->all_cleared == 1 && $r->balance_total <= 0))->count();

        return [
            'expected'       => round($expected),
            'collected'      => round($collected),
            'outstanding'    => max(0, round($expected - $collected)),
            'rate'           => $expected > 0 ? round($collected / $expected * 100, 1) : 0.0,
            'enrolled'       => $enrolled,
            'fully_paid'     => $fully,
            'partially_paid' => $partial,
            'no_payment'     => max(0, $enrolled - $rows->count()),
        ];
    }

    /** Monthly collection trend (receipts), last N months ending appropriately. */
    public function monthlyTrend(string $session, int $months = 12): array
    {
        $rows = Receipt::query()
            ->join('payment_records', 'payment_records.id', '=', 'receipts.pr_id')
            ->where('payment_records.year', $session)
            ->groupBy('ym')
            ->selectRaw("TO_CHAR(COALESCE(receipts.payment_date, receipts.created_at), 'YYYY-MM') AS ym,
                COALESCE(SUM(receipts.amt_paid),0) AS total")
            ->pluck('total', 'ym');

        $end = now()->copy()->startOfMonth();
        if (!$this->isCurrent($session)) {
            $parts = explode('-', (string) $session);
            $endYear = (int) ($parts[1] ?? $parts[0]);
            $candidate = Carbon::createFromDate($endYear, 12, 1)->startOfMonth();
            if ($candidate->lt($end)) {
                $end = $candidate;
            }
        }

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $m = $end->copy()->subMonths($i);
            $key = $m->format('Y-m');
            $labels[] = $m->format('M y');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return [$labels, $data];
    }

    /** Per-class financial performance with drill-down ids. General fees spread evenly. */
    public function classBreakdown(string $session, $term = null): Collection
    {
        $classes = MyClass::withCount(['student_record' => fn ($q) => $q->where('session', $session)])
            ->orderBy('name')->get();

        $feeTotals = Payment::where('year', $session)
            ->selectRaw('COALESCE(my_class_id, 0) AS cid, COALESCE(SUM(amount),0) AS total')
            ->groupBy('cid')->pluck('total', 'cid');

        $collected = PaymentRecord::query()
            ->join('payments', 'payments.id', '=', 'payment_records.payment_id')
            ->where('payment_records.year', $session)
            ->when($term, fn ($qq) => $qq->where(function ($w) use ($term) {
                $w->whereNull('payment_records.term')->orWhere('payment_records.term', $term);
            }))
            ->selectRaw('COALESCE(payments.my_class_id, 0) AS cid, COALESCE(SUM(payment_records.amt_paid),0) AS total')
            ->groupBy('cid')->pluck('total', 'cid');

        $generalFee = (int) ($feeTotals[0] ?? 0);

        $out = $classes->map(function ($c) use ($feeTotals, $collected, $generalFee) {
            $structure = (int) ($feeTotals[$c->id] ?? 0);
            $n = $c->student_record_count;
            $expected = ($structure + $generalFee) * $n;
            $got = (int) ($collected[$c->id] ?? 0);

            return [
                'id'          => $c->id,
                'name'        => $c->name,
                'students'    => $n,
                'per_student' => $structure + $generalFee,
                'expected'    => $expected,
                'collected'   => $got,
                'outstanding' => max(0, $expected - $got),
                'rate'        => $expected > 0 ? round($got / $expected * 100, 1) : 0.0,
            ];
        })->values();

        // Whole-school row
        return collect($out->all())->merge([[
            'id'          => null,
            'name'        => 'TOTAL',
            'students'    => $out->sum('students'),
            'per_student' => null,
            'expected'    => $out->sum('expected'),
            'collected'   => $out->sum('collected'),
            'outstanding' => max(0, $out->sum('expected') - $out->sum('collected')),
            'rate'        => $out->sum('expected') > 0 ? round($out->sum('collected') / $out->sum('expected') * 100, 1) : 0.0,
        ]]);
    }

    /** Recent individual transactions. */
    public function recentTransactions(string $session, int $limit = 8): Collection
    {
        return Receipt::query()
            ->join('payment_records', 'payment_records.id', '=', 'receipts.pr_id')
            ->join('users', 'users.id', '=', 'payment_records.student_id')
            ->leftJoin('payments', 'payments.id', '=', 'payment_records.payment_id')
            ->where('payment_records.year', $session)
            ->orderByRaw('COALESCE(receipts.payment_date, receipts.created_at) DESC')
            ->limit($limit)
            ->get([
                'receipts.id',
                'users.name AS student',
                'payments.title AS fee',
                'payment_records.ref_no',
                'receipts.amt_paid',
                DB::raw("TO_CHAR(COALESCE(receipts.payment_date, receipts.created_at), 'DD Mon YYYY') AS pay_date"),
            ]);
    }

    /**
     * Student fee-status rows for one page of students.
     * Returns [rows, totals] — totals cover the WHOLE filtered set, not just the page.
     */
    public function feeStatus(string $session, $term, $classId, ?string $search, string $status, int $perPage = 15)
    {
        $base = StudentRecord::query()
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->leftJoin('my_classes as mc', 'mc.id', '=', 'student_records.my_class_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 'student_records.section_id')
            ->where('student_records.session', $session)
            ->when($classId, fn ($q) => $q->where('student_records.my_class_id', $classId))
            ->when($search !== null && $search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('users.name', 'like', "%{$search}%")
                        ->orWhere('student_records.adm_no', 'like', "%{$search}%");
                });
            });

        $paginator = (clone $base)->orderBy('users.name')
            ->select([
                'student_records.user_id',
                'users.name AS student',
                'student_records.adm_no',
                'student_records.my_class_id',
                'mc.name AS class_name',
                'sec.name AS section_name',
                'student_records.guardian_name',
                DB::raw("COALESCE(student_records.guardian_phone, users.phone, '') AS contact"),
            ])
            ->paginate($perPage);

        // Aggregates for the page's students
        $ids = $paginator->getCollection()->pluck('user_id')->all();
        $agg = DB::table('payment_records')
            ->whereIn('student_id', $ids)
            ->when($ids === [] ? false : true, fn ($q) => $q)
            ->where('year', $session)
            ->when($term, fn ($q) => $q->where(function ($w) use ($term) {
                $w->whereNull('term')->orWhere('term', $term);
            }))
            ->groupBy('student_id')
            ->selectRaw('student_id,
                COALESCE(SUM(amt_paid),0) AS paid,
                COALESCE(SUM(balance),0) AS bal,
                MIN(paid) AS all_cleared,
                COUNT(*) AS records')
            ->get()->keyBy('student_id');

        // Expected-per-student from the fee structure
        [$feeByClass, $generalFee] = $this->structureTotals($session, $term);

        $rows = $paginator->getCollection()->map(function ($s) use ($agg, $feeByClass, $generalFee) {
            $a = $agg[$s->user_id] ?? null;
            $expected = ($feeByClass[$s->my_class_id] ?? 0) + $generalFee;
            $paid = (int) ($a->paid ?? 0);
            $balance = max(0, $expected - $paid);
            $hasRecords = (int) ($a->records ?? 0) > 0;
            $cleared = $hasRecords && ($a->all_cleared == 1) && ($a->bal <= 0);

            $state = $cleared ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            return [
                'user_id'   => $s->user_id,
                'student'   => $s->student,
                'adm_no'    => $s->adm_no,
                'class'     => $s->class_name,
                'section'   => $s->section_name,
                'guardian'  => $s->guardian_name,
                'contact'   => $s->contact,
                'expected'  => $expected,
                'paid'      => $paid,
                'balance'   => $balance,
                'state'     => $state,
            ];
        });

        if ($status !== 'all') {
            $rows = $rows->filter(fn ($r) => $r['state'] === $status)->values();
        }

        // Totals across the entire filtered population (ignores pagination & status filter)
        $totalsQ = clone $base;
        $totals = [
            'students'  => (clone $totalsQ)->distinct('student_records.user_id')->count('student_records.user_id'),
            'expected'  => 0,
            'paid'      => 0,
            'balance'   => 0,
        ];
        $allIds = (clone $totalsQ)->pluck('student_records.user_id')->all();
        if ($allIds) {
            $sumAgg = DB::table('payment_records')->whereIn('student_id', $allIds)
                ->where('year', $session)
                ->when($term, fn ($q) => $q->where(function ($w) use ($term) {
                    $w->whereNull('term')->orWhere('term', $term);
                }))
                ->selectRaw('COALESCE(SUM(amt_paid),0) AS paid')->value('paid');
            $classCounts = (clone $totalsQ)
                ->selectRaw('student_records.my_class_id AS cid, COUNT(DISTINCT student_records.user_id) AS n')
                ->groupBy('student_records.my_class_id')
                ->pluck('n', 'cid');
            foreach ($classCounts as $cid => $n) {
                $totals['expected'] += (($feeByClass[$cid] ?? 0) + $generalFee) * $n;
            }
            $totals['paid'] = (int) $sumAgg;
            $totals['balance'] = max(0, $totals['expected'] - $totals['paid']);
        }

        return [$rows, $paginator, $totals];
    }

    /** Students with outstanding balances, largest first. */
    public function debtors(string $session, $term = null, $classId = null)
    {
        return DB::table('payment_records')
            ->join('users', 'users.id', '=', 'payment_records.student_id')
            ->leftJoin('student_records', function ($j) {
                $j->on('student_records.user_id', '=', 'payment_records.student_id')
                    ->where('student_records.session', '=', DB::raw('payment_records.year'));
            })
            ->leftJoin('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->where('payment_records.year', $session)
            ->when($term, fn ($q) => $q->where(function ($w) use ($term) {
                $w->whereNull('payment_records.term')->orWhere('payment_records.term', $term);
            }))
            ->when($classId, fn ($q) => $q->where('student_records.my_class_id', $classId))
            ->groupBy('payment_records.student_id', 'users.name', 'my_classes.name', 'student_records.adm_no')
            ->havingRaw('COALESCE(SUM(payment_records.balance),0) > 0')
            ->orderByRaw('SUM(payment_records.balance) DESC')
            ->select(
                'payment_records.student_id',
                'users.name AS student',
                'my_classes.name AS class_name',
                'student_records.adm_no',
                DB::raw('COALESCE(SUM(payment_records.amt_paid),0) AS paid'),
                DB::raw('COALESCE(SUM(payment_records.balance),0) AS balance')
            )
            ->paginate(20);
    }

    /** Fee-structure totals keyed by class id (+ general bucket). */
    protected function structureTotals(string $session, $term = null): array
    {
        $rows = Payment::where('year', $session)
            ->when($term, fn ($q) => $q->where(function ($w) use ($term) {
                $w->whereNull('term')->orWhere('term', $term);
            }))
            ->selectRaw('my_class_id, COALESCE(SUM(amount),0) AS total')
            ->groupBy('my_class_id')
            ->get();

        $general = 0;
        $byClass = collect();
        foreach ($rows as $r) {
            if ($r->my_class_id === null) {
                $general = (int) $r->total;                 // school-wide fees
            } else {
                $byClass[$r->my_class_id] = (int) $r->total; // class-specific fees
            }
        }

        return [$byClass, $general];
    }

    /** Transactions grouped by day for reconciliation (postgres-safe). */
    public function dailyCollections(string $session, $from = null, $to = null): Collection
    {
        $dateExpr = "COALESCE(receipts.payment_date, receipts.created_at)";

        return Receipt::query()
            ->join('payment_records', 'payment_records.id', '=', 'receipts.pr_id')
            ->join('users', 'users.id', '=', 'payment_records.student_id')
            ->leftJoin('payments', 'payments.id', '=', 'payment_records.payment_id')
            ->where('payment_records.year', $session)
            ->when($from, fn ($q) => $q->whereRaw("$dateExpr >= ?", [$from]))
            ->when($to, fn ($q) => $q->whereRaw("$dateExpr < (?::date + INTERVAL '1 day')", [$to]))
            ->orderByDesc('date_key')->orderBy('users.name')
            ->get([
                DB::raw("TO_CHAR($dateExpr, 'YYYY-MM-DD') AS date_key"),
                DB::raw("TO_CHAR($dateExpr, 'DD Mon YYYY') AS pay_date"),
                'users.name AS student',
                'payments.title AS fee',
                'payment_records.ref_no',
                'receipts.amt_paid',
            ])
            ->groupBy('date_key');
    }
}
