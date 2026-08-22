<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\MyClass;
use App\Models\PaymentRecord;
use App\Models\StudentRecord;
use App\Repositories\UserRepo;
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

    public function dashboard()
    {
        $d=[];
        if(Qs::userIsTeamSAT()){
            $d['users'] = $this->user->getAll();
        }

        if(Qs::userIsTeamSA()){
            $d += $this->getAdminStats();
        }

        return view('pages.support_team.dashboard', $d);
    }

    protected function getAdminStats()
    {
        $data = [];

        // ---- Students ----
        $sr = StudentRecord::query();
        $data['total_students'] = (clone $sr)->count();
        $data['new_students'] = (clone $sr)->whereYear('created_at', now()->year)->count();
        $data['students_by_class'] = MyClass::orderBy('name')->withCount('student_record')->get();
        $data['by_gender'] = DB::table('student_records')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->select('users.gender', DB::raw('COUNT(*) AS total'))
            ->groupBy('users.gender')
            ->pluck('total', 'gender');

        // ---- Payments (current session) ----
        $session = Qs::getCurrentSession();
        $pr = PaymentRecord::where('year', $session);
        $data['session'] = $session;
        $data['fees_collected'] = (clone $pr)->sum('amt_paid');
        $data['fees_outstanding'] = (clone $pr)->sum('balance');
        $data['payments_count'] = (clone $pr)->where('paid', 1)->count();
        $data['recent_payments'] = (clone $pr)->latest('id')->take(8)->get()->load('payment', 'student');

        // Fee collection trend - last 6 months
        $trend = PaymentRecord::where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['amt_paid', 'created_at'])
            ->groupBy(function ($r) {
                return $r->created_at->format('M Y');
            })
            ->map(function ($group) {
                return $group->sum('amt_paid');
            });

        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i);
        });
        $data['trend_labels'] = $months->map(function ($m) {
            return $m->format('M');
        });
        $data['trend_data'] = $months->map(function ($m) use ($trend) {
            return $trend->get($m->format('M Y'), 0);
        });

        return $data;
    }
}
