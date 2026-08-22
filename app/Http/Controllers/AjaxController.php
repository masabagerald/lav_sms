<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\StudentRecord;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AjaxController extends Controller
{
    protected $loc, $my_class;

    public function __construct(LocationRepo $loc, MyClassRepo $my_class)
    {
        $this->loc = $loc;
        $this->my_class = $my_class;
    }

    public function get_lga($state_id)
    {
//        $state_id = Qs::decodeHash($state_id);
//        return ['id' => Qs::hash($q->id), 'name' => $q->name];

        $lgas = $this->loc->getLGAs($state_id);
        return $data = $lgas->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    public function get_class_sections($class_id)
    {
        $sections = $this->my_class->getClassSections($class_id);
        return $sections = $sections->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    public function get_class_subjects($class_id)
    {
        $sections = $this->my_class->getClassSections($class_id);
        $subjects = $this->my_class->findSubjectByClass($class_id);

        if(Qs::userIsTeacher()){
            $subjects = $this->my_class->findSubjectByTeacher(Auth::user()->id)->where('my_class_id', $class_id);
        }

        $d['sections'] = $sections->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
        $d['subjects'] = $subjects->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();

        return $d;
    }

    /**
     * Global quick lookup (staff only): students by name/ADM no,
     * plus staff accounts for admins. Used by the navbar search box.
     */
    public function quick_search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if(strlen($q) < 2 || !(Qs::userIsTeamSAT() || Qs::userIsTeamAccount())) {
            return response()->json([]);
        }

        $students = StudentRecord::select('student_records.id', 'users.name', 'student_records.adm_no', 'my_classes.name AS class_name')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->leftJoin('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->where(function($qr) use ($q) {
                $qr->where('users.name', 'like', "%{$q}%")
                   ->orWhere('student_records.adm_no', 'like', "%{$q}%");
            })
            ->orderBy('users.name')
            ->limit(6)
            ->get()
            ->map(function($s) {
                $meta = trim(($s->adm_no ? 'ADM '.$s->adm_no : '').($s->class_name ? ' · '.$s->class_name : ''), ' ·');
                return [
                    'type' => 'student',
                    'label' => $s->name,
                    'meta'  => $meta ?: 'Student',
                    'url'   => route('students.show', Qs::hash($s->id)),
                ];
            })
            ->values();

        $results = collect($students->all());

        // Staff accounts are only surfaced to school administrators
        if(Qs::userIsTeamSA()) {
            $staff = User::whereIn('user_type', Qs::getStaff())
                ->where(function($qr) use ($q) {
                    $qr->where('name', 'like', "%{$q}%")
                       ->orWhere('username', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                })
                ->orderBy('name')
                ->limit(4)
                ->get()
                ->map(function($u) {
                    return [
                        'type' => 'staff',
                        'label' => $u->name,
                        'meta'  => ucwords(str_replace('_', ' ', $u->user_type)),
                        'url'   => route('users.show', Qs::hash($u->id)),
                    ];
                })
                ->values();

            $results = $results->merge(collect($staff->all()));
        }

        return response()->json($results->values());
    }

}
