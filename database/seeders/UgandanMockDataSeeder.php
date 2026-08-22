<?php

namespace Database\Seeders;

use App\Helpers\Qs;
use App\Models\Dorm;
use App\Models\Section;
use App\Models\StudentRecord;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * UGANDAN DEMO DATA - students, teachers and parents with authentic Ugandan
 * names, districts, phone numbers and guardian details, then fees & payments
 * via DemoPaymentsTableSeeder. Re-running replaces previous demo records.
 */
class UgandanMockDataSeeder extends Seeder
{
    protected array $maleNames = [
        'Joseph', 'Emmanuel', 'Moses', 'David', 'Samuel', 'Isaac', 'Ronald', 'Denis', 'Hassan', 'Patrick',
        'Gerald', 'Fred', 'Julius', 'Simon', 'Peter', 'Paul', 'Robert', 'Martin', 'Henry', 'Brian',
        'Tonny', 'Richard', 'Godfrey', 'Charles', 'Jimmy', 'Bosco', 'Medard', 'Ashraf', 'Shafik', 'Musa',
        'Abubaker', 'Ismael', 'Yusuf', 'Swaibu', 'Vincent', 'Nicholas', 'Livingstone', 'Benon', 'Ezra', 'Norman',
    ];

    protected array $femaleNames = [
        'Grace', 'Mary', 'Sarah', 'Rebecca', 'Esther', 'Prossy', 'Betty', 'Joyce', 'Susan', 'Christine',
        'Harriet', 'Immaculate', 'Winnie', 'Patricia', 'Doreen', 'Brenda', 'Shamim', 'Zainab', 'Aisha', 'Fatuma',
        'Ritah', 'Joan', 'Viola', 'Faith', 'Hope', 'Sandra', 'Sharon', 'Sylvia', 'Moreen', 'Jackline',
        'Agnes', 'Teopista', 'Resty', 'Allen', 'Evelyn', 'Gorreti', 'Justine', 'Hadijah', 'Annet', 'Stella',
    ];

    protected array $surnames = [
        'Mugisha', 'Byaruhanga', 'Okello', 'Odongo', 'Akello', 'Tumusiime', 'Nsubuga', 'Ssemakula', 'Wasswa', 'Kato',
        'Nakato', 'Kyeyune', 'Lubega', 'Kirabo', 'Atim', 'Apio', 'Opio', 'Ochieng', 'Wanyama', 'Chemutai',
        'Chebet', 'Musinguzi', 'Kabugo', 'Muwanga', 'Bukenya', 'Nalubega', 'Namatovu', 'Kawooya', 'Bwambale', 'Muhindo',
        'Masereka', 'Candia', 'Ogwang', 'Okumu', 'Adong', 'Amongin', 'Anguzu', 'Ojok', 'Birungi', 'Ainomugisha',
        'Natukunda', 'Arinda', 'Niwagaba', 'Katusiime', 'Asasira', 'Tushabe', 'Ampurire', 'Nyakato', 'Ssekandi', 'Kigozi',
    ];

    /** Districts of Uganda used for addresses (states table is seeded with them) */
    protected array $districts = [
        'Mubende', 'Mityana', 'Kampala', 'Wakiso', 'Mpigi', 'Masaka', 'Mukono', 'Kiboga', 'Luweero', 'Mbarara',
        'Fort Portal', 'Kyegegwa', 'Kassanda', 'Hoima', 'Jinja', 'Iganga', 'Mbale', 'Soroti', 'Lira', 'Gulu',
    ];

    protected array $religions = ['Christian', 'Christian', 'Christian', 'Muslim'];

    /** Global sequence guaranteeing unique emails/usernames */
    protected int $seq = 0;

    public function run()
    {
        // Clear previous demo people + records (admins/accountants from UsersTableSeeder are kept)
        DB::table('student_records')->delete();
        User::whereIn('user_type', ['teacher', 'parent', 'student'])->delete();
        DB::table('subjects')->update(['teacher_id' => null]);

        $this->createTeachers(12);
        $parentIds = $this->createParents(15);
        $this->createStudents(7, $parentIds);

        // Fees structure + payment history so charts/calendar have data
        $this->call(DemoPaymentsTableSeeder::class);
    }

    protected function createTeachers(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->createUser($this->uniqueName('male'), 'teacher', 'teacher');
        }
    }

    protected function createParents(int $count): array
    {
        $ids = [];
        for ($i = 1; $i <= $count; $i++) {
            $ids[] = $this->createUser($this->uniqueName(rand(0, 1) ? 'male' : 'female'), '', 'parent')['id'];
        }

        return $ids;
    }

    protected function createStudents(int $perSection, array $parentIds): void
    {
        $sections = Section::with('my_class')->get();
        $dorms = Dorm::all()->pluck('id')->all();
        $bgIds = DB::table('blood_groups')->pluck('id')->all();
        $nalId = optional(DB::table('nationalities')->where('name', 'Ugandan')->first())->id
            ?? DB::table('nationalities')->value('id');
        $stateIds = DB::table('states')->pluck('id')->all();
        $session = Qs::getCurrentSession();
        $year = explode('-', $session)[0];
        $counter = 0;

        foreach ($sections as $section) {
            for ($n = 1; $n <= $perSection; $n++) {
                $gender = rand(0, 1) ? 'male' : 'female';
                $name = $this->uniqueName($gender);
                $seq = $this->nextSeq();

                $classAgeBase = (int) filter_var($section->my_class->name, FILTER_SANITIZE_NUMBER_INT); // 1..6
                $age = $classAgeBase + 12 + rand(0, 2);
                $district = $this->districts[array_rand($this->districts)];

                $user = $this->createUser(
                    $name,
                    '',
                    'student',
                    [
                        'gender' => $gender,
                        'dob' => sprintf('%d-%02d-%02d', now()->year - $age, rand(1, 12), rand(1, 28)),
                        'phone' => $this->ugPhone(),
                        'address' => $this->address($district),
                        'state_id' => $stateIds ? $stateIds[array_rand($stateIds)] : null,
                        'nal_id' => $nalId,
                        'bg_id' => $bgIds ? $bgIds[array_rand($bgIds)] : null,
                    ],
                    $seq
                );

                $counter++;
                StudentRecord::create([
                    'session' => $session,
                    'user_id' => $user['id'],
                    'my_class_id' => $section->my_class_id,
                    'section_id' => $section->id,
                    'my_parent_id' => $parentIds ? $parentIds[$counter % count($parentIds)] : null,
                    'dorm_id' => $dorms ? $dorms[array_rand($dorms)] : null,
                    'dorm_room_no' => 'R' . rand(1, 20),
                    'adm_no' => sprintf('MPSS/%s/%04d', substr($year, -2), $counter),
                    'year_admitted' => $year,
                    'age' => $age,
                    'fees' => 0,
                    'guardian_name' => $this->guardianName(),
                    'guardian_phone' => $this->ugPhone(),
                    'religion' => $this->religions[array_rand($this->religions)],
                ]);
            }
        }
    }

    /**
     * Insert a user row; returns the attributes (including id when persisted).
     */
    protected function createUser(string $name, string $title, string $type, array $extra = [], int $seq = null): array
    {
        $seq = $seq ?: $this->nextSeq();
        $base = [
            'name' => trim($title ? "$title $name" : $name),
            'email' => strtolower(Str::slug($name)) . $seq . '@mpss.ac.ug',
            'username' => Str::slug($name, '_') . $seq,
            'password' => Hash::make($type === 'student' ? 'student' : 'Mpss@1234'),
            'user_type' => $type,
            'code' => strtoupper(Str::random(10)),
            'remember_token' => Str::random(10),
        ];

        $id = DB::table('users')->insertGetId($base + $extra + ['created_at' => now(), 'updated_at' => now()]);
        $base['id'] = $id;

        return $base;
    }

    protected function nextSeq(): int
    {
        return ++$this->seq;
    }

    protected function uniqueName(string $gender): string
    {
        $first = $gender === 'male'
            ? $this->maleNames[array_rand($this->maleNames)]
            : $this->femaleNames[array_rand($this->femaleNames)];
        $surname = $this->surnames[array_rand($this->surnames)];

        return "$first $surname";
    }

    protected function guardianName(): string
    {
        $first = rand(0, 1)
            ? ['Mr.', 'Mzee'][rand(0, 1)]
            : ['Mrs.', 'Ms.'][rand(0, 1)];
        $name = ($first === 'Mr.' || $first === 'Mzee'
            ? $this->maleNames[array_rand($this->maleNames)]
            : $this->femaleNames[array_rand($this->femaleNames)])
            . ' ' . $this->surnames[array_rand($this->surnames)];

        return "$first $name";
    }

    protected function ugPhone(): string
    {
        $prefixes = ['077', '078', '076', '070', '075', '074', '039'];

        return $prefixes[array_rand($prefixes)] . rand(1000000, 9999999);
    }

    protected function address(string $district): string
    {
        $areas = ['Central', 'East', 'West', 'North', 'South', 'Town Council', 'Rural'];

        return 'Plot ' . rand(1, 200) . ', ' . $areas[array_rand($areas)] . ', ' . $district;
    }
}
