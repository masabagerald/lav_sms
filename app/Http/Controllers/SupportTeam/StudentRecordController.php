<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\AdminNoHelper;
use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Http\Requests\Student\StudentRecordCreate;
use App\Http\Requests\Student\StudentRecordUpdate;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;




class StudentRecordController extends Controller
{
    protected $loc, $my_class, $user, $student;

   public function __construct(LocationRepo $loc, MyClassRepo $my_class, UserRepo $user, StudentRepo $student)
   {
       $this->middleware('teamSA', ['only' => ['edit','update', 'reset_pass', 'create', 'store', 'graduated'] ]);
       $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->user = $user;
        $this->student = $student;
   }

    public function reset_pass($st_id)
    {
        $st_id = Qs::decodeHash($st_id);
        $data['password'] = Hash::make('student');
        $this->user->update($st_id, $data);
        return back()->with('flash_success', __('msg.p_reset'));
    }

    public function create()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        return view('pages.support_team.students.add', $data);
    }

    public function store(StudentRecordCreate $req)
    {
       
       $data =  $req->only(Qs::getUserRecord());
       $sr =  $req->only(Qs::getStudentData());

        $ct = $this->my_class->findTypeByClass($req->my_class_id)->code;
       /* $ct = ($ct == 'J') ? 'JSS' : $ct;
        $ct = ($ct == 'S') ? 'SS' : $ct;*/

        $data['user_type'] = 'student';
        $data['name'] = ucwords($req->name);
        $data['code'] = strtoupper(Str::random(10));
        $data['password'] = Hash::make('student');
        $data['photo'] = Qs::getDefaultUserImage();
        $adm_no = $req->adm_no;

        $year = $sr['year_admitted'];

   

        $data['username'] = AdminNoHelper::generateAdmissionNo($ct, $sr['year_admitted']);

        

     

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$data['code'], $f['name']);
            $data['photo'] = asset('storage/' . $f['path']);
        }

        $user = $this->user->create($data); // Create User

        $sr['adm_no'] = $data['username'];
        $sr['user_id'] = $user->id;
        $sr['session'] = Qs::getSetting('current_session');

        $this->student->createRecord($sr); // Create Student
        return Qs::jsonStoreOk();
    }

    public function listByClass($class_id)
    {
        $data['my_class'] = $mc = $this->my_class->getMC(['id' => $class_id])->first();
        $data['students'] = $this->student->findStudentsByClass($class_id);
        $data['sections'] = $this->my_class->getClassSections($class_id);

        return is_null($mc) ? Qs::goWithDanger() : view('pages.support_team.students.list', $data);
    }

    public function graduated()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['students'] = $this->student->allGradStudents();

        return view('pages.support_team.students.graduated', $data);
    }

    public function not_graduated($sr_id)
    {
        $d['grad'] = 0;
        $d['grad_date'] = NULL;
        $d['session'] = Qs::getSetting('current_session');
        $this->student->updateRecord($sr_id, $d);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function show($sr_id)
    {
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $data['sr'] = $this->student->getRecord(['id' => $sr_id])->first();

        /* Prevent Other Students/Parents from viewing Profile of others */
        if(Auth::user()->id != $data['sr']->user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($data['sr']->user_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        return view('pages.support_team.students.show', $data);
    }

    public function edit($sr_id)
    {
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $data['sr'] = $this->student->getRecord(['id' => $sr_id])->first();
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        return view('pages.support_team.students.edit', $data);
    }

    public function update(StudentRecordUpdate $req, $sr_id)
    {
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $sr = $this->student->getRecord(['id' => $sr_id])->first();
        $d =  $req->only(Qs::getUserRecord());
        $d['name'] = ucwords($req->name);

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$sr->user->code, $f['name']);
            $d['photo'] = asset('storage/' . $f['path']);
        }

        $this->user->update($sr->user->id, $d); // Update User Details

        $srec = $req->only(Qs::getStudentData());

        $this->student->updateRecord($sr_id, $srec); // Update St Rec

        /*** If Class/Section is Changed in Same Year, Delete Marks/ExamRecord of Previous Class/Section ****/
        Mk::deleteOldRecord($sr->user->id, $srec['my_class_id']);

        return Qs::jsonUpdateOk();
    }

    public function destroy($st_id)
    {
        $st_id = Qs::decodeHash($st_id);
        if(!$st_id){return Qs::goWithDanger();}

        $sr = $this->student->getRecord(['user_id' => $st_id])->first();
        $path = Qs::getUploadPath('student').$sr->user->code;
        Storage::exists($path) ? Storage::deleteDirectory($path) : false;
        $this->user->delete($sr->user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }


public function importStudentsCsv(Request $request)
{
    $request->validate([
        'file'        => 'required|file|mimes:csv,txt',
        'my_class_id' => 'required|exists:my_classes,id',
    ]);

    $myClass = MyClass::findOrFail($request->my_class_id);

    // Get class type code (S / J) for admission number generation
    $classTypeCode = $this->my_class
        ->findTypeByClass($myClass->id)
        ->code;

    $file = fopen($request->file('file')->getRealPath(), 'r');

    DB::beginTransaction();

    try {

        // Read header row
        $header = fgetcsv($file);

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($file)) !== false) {

            $data = array_combine($header, $row);

            // Required CSV fields
            if (
                empty($data['Name']) ||
                empty($data['Stream'])
            ) {
                $skipped++;
                continue;
            }

            // Resolve section from Stream (e.g. "S4 A" → A)
            $sectionName = strtoupper(trim(substr($data['Stream'], -1)));

            $section = Section::where('name', $sectionName)
                ->where('my_class_id', $myClass->id)
                ->first();

            // Skip if section not found
            if (!$section) {
                $skipped++;
                continue;
            }

            // Generate NEW admission number
            $admNo = AdminNoHelper::generateAdmissionNo(
                $classTypeCode,
                date('Y')
            );

            // Prevent accidental duplicates
            if (StudentRecord::where('adm_no', $admNo)->exists()) {
                $skipped++;
                continue;
            }

            // Create user
            $user = User::create([
                'name'      => ucwords(strtolower($data['Name'])),
                'username'  => $admNo,
                'user_type' => 'student',
                'code'      => strtoupper(Str::random(10)),
                'password'  => Hash::make('student'),
                'photo'     => Qs::getDefaultUserImage(),
            ]);

            // Create student record
            StudentRecord::create([
                'user_id'       => $user->id,
                'my_class_id'   => $myClass->id,
                'section_id'    => $section->id,
                'adm_no'        => $admNo,
                'old_reg_no'    => $data['Admission Number'] ?? null,
                'session'       => Qs::getSetting('current_session'),
                'year_admitted' => date('Y'),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $imported++;
        }

        fclose($file);
        DB::commit();

        return redirect()->back()->with(
            'success',
            "Import complete: {$imported} students added, {$skipped} skipped."
        );

    } catch (\Throwable $e) {

        fclose($file);
        DB::rollBack();

        

        return redirect()->back()->withErrors([
            'file' => 'Import failed: ' . $e->getMessage()
        ]);
    }
}

private function resolveClassSectionAndType($stream)
{
    $stream = strtoupper(trim($stream));

    preg_match('/S(\d)/', $stream, $classMatch);
    preg_match('/([A-Z])$/', $stream, $sectionMatch);

    $classNumber = $classMatch[1];
    $sectionName = $sectionMatch[1];

    $className = 'Senior ' . $classNumber;

    $myClass = \App\Models\MyClass::where('name', $className)->firstOrFail();
    $section = \App\Models\Section::where('name', $sectionName)->firstOrFail();

    // Get class type code (S / J)
    $classTypeCode = $this->my_class
        ->findTypeByClass($myClass->id)
        ->code;

    return [$myClass->id, $section->id, $classTypeCode];
}



}
