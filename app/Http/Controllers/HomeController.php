<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\MyClass;
use App\Models\PaymentRecord;
use App\Models\StudentRecord;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected $user;

    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }


    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.terms_of_use', $data);
    }

    public function dashboard(Request $request)
    {
        $d = [];
        if (Qs::userIsTeamSAT()) {
            $d['users'] = $this->user->getAll();
        }

        $year = $request->query('year') ?: Qs::getCurrentSession();
        $d['selected_year'] = $year;

        if (Qs::userIsTeamSA()) {
            $classId = (int) $request->query('class_id', 0);
            $term = (int) $request->query('term', 0);

            $d += $this->getAdminStats($year, $classId, $term);
            $d['calendar_events'] = $this->getCalendarEvents($year, $classId, $term);
        } elseif (Qs::userIsTeamSAT()) {
            // Teachers get the activity calendar too (no filters)
            $d['calendar_events'] = $this->getCalendarEvents($year);
        }

        return view('pages.support_team.dashboard', $d);
    }

    protected function getAdminStats($year, $classId = 0, $term = 0)
    {
        $data = [];
        $classId = (int) $classId;
        $term = (int) $term;

        // ---- Filter options / selection ----
        $data['years'] = $this->getFilterYears();
        $data['my_classes'] = MyClass::orderBy('name')->get();
        $data['selected_year'] = $year;
        $data['selected_class'] = $classId;
        $data['selected_term'] = $term;

        [$startYear, $endYear] = $this->sessionSpan($year);

        // ---- Students ----
        $sr = StudentRecord::query();
        if ($classId) {
            $sr->where('my_class_id', $classId);
        }
        $data['total_students'] = (clone $sr)->count();
        $data['new_students'] = (clone $sr)
            ->whereBetween('created_at', [
                Carbon::createFromDate($startYear, 1, 1)->startOfDay(),
                Carbon::createFromDate($endYear, 12, 31)->endOfDay(),
            ])->count();

        $genderQ = DB::table('student_records')
            ->join('users', 'users.id', '=', 'student_records.user_id');
        if ($classId) {
            $genderQ->where('student_records.my_class_id', $classId);
        }
        $data['by_gender'] = $genderQ
            ->select('users.gender', DB::raw('COUNT(*) AS total'))
            ->groupBy('users.gender')
            ->pluck('total', 'gender');

        $data['students_by_class'] = MyClass::orderBy('name')->withCount('student_record')->get();

        // ---- Payments ----
        $pr = PaymentRecord::where('year', $year);
        if ($term) {
            $pr->where('term', $term);
        }
        if ($classId) {
            $pr->whereHas('payment', function ($q) use ($classId) {
                $q->where('my_class_id', $classId);
            });
        }

        $data['fees_collected'] = (clone $pr)->sum('amt_paid');
        $data['fees_outstanding'] = (clone $pr)->sum('balance');
        $data['payments_count'] = (clone $pr)->where('paid', 1)->count();
        $data['recent_payments'] = (clone $pr)->latest('id')->take(8)->get()->load('payment', 'student');

        // ---- Fee collection trend (months of the selected session) ----
        $isCurrent = ($year === Qs::getCurrentSession());
        $end = $isCurrent ? now()->copy()->startOfMonth() : Carbon::createFromDate($endYear, 12, 1);
        $start = $end->copy()->subMonths(11);
        $floorStart = Carbon::createFromDate($startYear, 1, 1)->startOfMonth();
        if ($start->lt($floorStart)) {
            $start = $floorStart;
        }

        $rows = (clone $pr)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('payment_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->whereNull('payment_date')
                            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
                    });
            })
            ->get(['amt_paid', 'payment_date', 'created_at']);

        $trend = $rows->groupBy(function ($r) {
            $date = $r->payment_date ?: $r->created_at;

            return $date ? Carbon::parse($date)->format('Y-m') : '';
        })->map(function ($group) {
            return $group->sum('amt_paid');
        });

        $months = collect();
        for ($m = $start->copy(); $m->lte($end); $m->addMonth()) {
            $months->push($m->copy());
        }

        $data['trend_labels'] = $months->map(function ($m) {
            return $m->format('M y');
        });
        $data['trend_data'] = $months->map(function ($m) use ($trend) {
            return $trend->get($m->format('Y-m'), 0);
        });
        $data['trend_period'] = $months->first()->format('M Y') . ' — ' . $months->last()->format('M Y');

        return $data;
    }

    /**
     * Sessions available for filtering (current session always included, newest first).
     */
    protected function getFilterYears()
    {
        $payYears = PaymentRecord::select('year')->distinct()->pluck('year');
        $studentYears = StudentRecord::select('session')->distinct()->pluck('session');

        return $payYears
            ->merge($studentYears)
            ->merge([Qs::getCurrentSession()])
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * "2025-2026" => [2025, 2026]. Handles single-year sessions too.
     */
    protected function sessionSpan($year)
    {
        $parts = explode('-', (string) $year);
        $startYear = (int) $parts[0];
        $endYear = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : $startYear;

        return [$startYear, $endYear];
    }

    /**
     * Real school activity for the calendar: fee collections + new admissions.
     */
    protected function getCalendarEvents($year, $classId = 0, $term = 0)
    {
        $events = collect();
        [$startYear, $endYear] = $this->sessionSpan($year);
        $spanStart = Carbon::createFromDate($startYear, 1, 1)->startOfDay();
        $spanEnd = Carbon::createFromDate($endYear, 12, 31)->endOfDay();

        // Fee collections per day
        $payQ = PaymentRecord::where('year', $year)
            ->where(function ($q) use ($spanStart, $spanEnd) {
                $q->whereBetween('payment_date', [$spanStart, $spanEnd])
                    ->orWhere(function ($q2) use ($spanStart, $spanEnd) {
                        $q2->whereNull('payment_date')
                            ->whereBetween('created_at', [$spanStart, $spanEnd]);
                    });
            });
        if ($term) {
            $payQ->where('term', $term);
        }
        if ($classId) {
            $payQ->whereHas('payment', function ($q) use ($classId) {
                $q->where('my_class_id', $classId);
            });
        }

        $payQ->get(['amt_paid', 'paid', 'payment_date', 'created_at'])
            ->groupBy(function ($r) {
                $date = $r->payment_date ?: $r->created_at;

                return $date ? Carbon::parse($date)->toDateString() : '';
            })
            ->each(function ($group, $day) use (&$events) {
                if (!$day) {
                    return;
                }
                $total = $group->sum('amt_paid');
                $cleared = $group->where('paid', 1)->count();
                $events->push([
                    'title' => 'Fees: UGX ' . number_format($total),
                    'start' => $day,
                    'color' => '#43a047',
                    'description' => $group->count() . ' payment record(s) · ' . $cleared . ' fully cleared',
                ]);
            });

        // New admissions per day
        $admQ = StudentRecord::whereBetween('created_at', [$spanStart, $spanEnd])
            ->where('session', $year);
        if ($classId) {
            $admQ->where('my_class_id', $classId);
        }
        $admQ->get(['created_at'])
            ->groupBy(function ($r) {
                return optional($r->created_at)->toDateString();
            })
            ->reject(function ($group, $day) {
                return !$day;
            })
            ->each(function ($group, $day) use (&$events, $year) {
                $n = $group->count();
                $events->push([
                    'title' => $n . ' new admission' . ($n > 1 ? 's' : ''),
                    'start' => $day,
                    'color' => '#1e88e5',
                    'description' => 'Students admitted into session ' . $year,
                ]);
            });

        return $events->values();
    }
}
