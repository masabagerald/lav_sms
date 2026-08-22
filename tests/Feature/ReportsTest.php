<?php

namespace Tests\Feature;

use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\Receipt;
use App\Models\Setting;
use App\Models\StudentRecord;
use App\Services\LicenseService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(LicenseService::class, function ($mock) {
            $mock->shouldReceive('validate')->andReturn(['valid' => true, 'data' => ['expires_at' => now()->addYear()], 'message' => '']);
        });

        Setting::insert([
            ['type' => 'system_name', 'description' => 'Test School'],
            ['type' => 'system_title', 'description' => 'TS'],
            ['type' => 'current_session', 'description' => '2025-2026'],
            ['type' => 'phone', 'description' => '077'],
            ['type' => 'address', 'description' => 'Kampala'],
            ['type' => 'system_email', 'description' => 't@t.com'],
            ['type' => 'logo', 'description' => ''],
            ['type' => 'term_ends', 'description' => ''],
            ['type' => 'term_begins', 'description' => ''],
            ['type' => 'lock_exam', 'description' => '0'],
            ['type' => 'next_term_fees_o', 'description' => '0'],
            ['type' => 'next_term_fees_a', 'description' => '0'],
        ]);

        $classTypeId = DB::table('class_types')->insertGetId(['code' => 'S', 'name' => 'Senior']);
        $class = DB::table('my_classes')->insertGetId(['name' => 'Senior 1', 'class_type_id' => $classTypeId]);
        DB::table('sections')->insert(['name' => 'A', 'my_class_id' => $class, 'active' => 1]);
        $sectionId = DB::table('sections')->where('my_class_id', $class)->value('id');

        $this->superAdmin = $this->makeUser('sa@test.com', 'super_admin');
        $this->teacher = $this->makeUser('te@test.com', 'teacher');

        // 3 students in Senior 1 for session 2025-2026
        foreach (['Alice', 'Bob', 'Clara'] as $i => $name) {
            $u = User::create([
                'name' => $name, "email" => "s$i@test.com", 'username' => "s$i",
                'password' => bcrypt('x'), 'user_type' => 'student',
                'code' => 'S00' . $i, 'gender' => $i % 2 ? 'female' : 'male',
                'dob' => '2011-05-10',
            ]);
            StudentRecord::create([
                'session' => '2025-2026', 'user_id' => $u->id,
                'my_class_id' => $class, 'section_id' => $sectionId, 'adm_no' => 'A00' . $i,
                'guardian_name' => "G$i", 'fees' => 0,
            ]);
        }

        // Fee structure: 100k class fee + 20k general fee = 120k expected per student
        Payment::create(['title' => 'Tuition', 'amount' => 100000, 'my_class_id' => $class,
                         'year' => '2025-2026', 'ref_no' => 'T1']);
        Payment::create(['title' => 'Levies', 'amount' => 20000, 'my_class_id' => null,
                         'year' => '2025-2026', 'ref_no' => 'G1']);

        $students = User::where('user_type', 'student')->get();

        // Alice: fully paid (100k on tuition)
        $pr1 = PaymentRecord::create(['student_id' => $students[0]->id, 'payment_id' => Payment::first()->id,
            'amt_paid' => 100000, 'balance' => 0, 'paid' => 1, 'year' => '2025-2026', 'ref_no' => 'R1', 'term' => 1]);
        Receipt::create(['pr_id' => $pr1->id, 'amt_paid' => 100000, 'balance' => 0,
                         'year' => '2025-2026', 'payment_date' => now()->subDay()->toDateString()]);

        // Bob: partial (30k)
        $pr2 = PaymentRecord::create(['student_id' => $students[1]->id, 'payment_id' => Payment::first()->id,
            'amt_paid' => 30000, 'balance' => 70000, 'paid' => 0, 'year' => '2025-2026', 'ref_no' => 'R2', 'term' => 2]);
        Receipt::create(['pr_id' => $pr2->id, 'amt_paid' => 30000, 'balance' => 70000,
                         'year' => '2025-2026', 'payment_date' => now()->toDateString()]);

        // Clara: nothing — no payment record at all
    }

    protected function makeUser(string $email, string $type): User
    {
        return User::create([
            'name' => ucfirst($type), 'email' => $email, 'username' => $type . rand(1, 999),
            'password' => bcrypt('x'), 'user_type' => $type, 'code' => uniqid(),
        ]);
    }

    /* ---------------- Financial math ---------------- */

    /** @test */
    public function expected_minus_collected_equals_outstanding_and_rate_is_correct()
    {
        $svc = app(\App\Services\Reports\FinanceReportService::class);
        $k = $svc->dashboardKpis('2025-2026');

        $this->assertEquals(3 * 120000, $k['expected'], 'Expected = structure x enrolled');
        $this->assertEquals(130000, $k['collected'], 'Collected = sum of amt_paid');
        $this->assertEquals($k['expected'] - $k['collected'], $k['outstanding'], 'Outstanding identity');
        $this->assertEquals(360000 - 130000, $k['outstanding'], '360k expected - 130k paid');
        $this->assertEquals(round(130000 / 360000 * 100, 1), $k['rate'], 'Collection rate');
        $this->assertEquals(1, $k['fully_paid']);
        $this->assertEquals(1, $k['partially_paid']);
        $this->assertEquals(1, $k['no_payment']);
        $this->assertEquals(3, $k['enrolled']);
    }

    /** @test */
    public function term_filter_narrows_collections()
    {
        $svc = app(\App\Services\Reports\FinanceReportService::class);

        $t1 = $svc->dashboardKpis('2025-2026', 1);
        $this->assertEquals(100000, $t1['collected']);

        $t2 = $svc->dashboardKpis('2025-2026', 2);
        $this->assertEquals(30000, $t2['collected']);
    }

    /** @test */
    public function empty_sessions_are_handled_without_division_by_zero()
    {
        $svc = app(\App\Services\Reports\FinanceReportService::class);
        $k = $svc->dashboardKpis('1999-2000');

        $this->assertEquals(0, $k['expected']);
        $this->assertEquals(0, $k['rate']);
        $this->assertEquals(0, $k['outstanding']);
    }

    /** @test */
    public function fee_status_rows_carry_expected_paid_balance_state()
    {
        $svc = app(\App\Services\Reports\FinanceReportService::class);
        [$rows] = $svc->feeStatus('2025-2026', null, null, null, 'all');

        $byName = collect($rows)->keyBy('student');

        $this->assertSame('paid', $byName['Alice']['state']);
        $this->assertEquals(100000, $byName['Alice']['paid']);
        $this->assertEquals(120000, $byName['Alice']['expected']);
    }

    /** @test */
    public function fee_status_states_are_classified_correctly()
    {
        $svc = app(\App\Services\Reports\FinanceReportService::class);
        [$rows] = $svc->feeStatus('2025-2026', null, null, null, 'all');

        $states = collect($rows)->pluck('state', 'student');
        $this->assertSame('partial', $states['Bob']);

        // Clara has no records at all -> unpaid, but balance still shown against structure
        $this->assertSame('unpaid', $states['Clara']);
        $clara = collect($rows)->firstWhere('student', 'Clara');
        $this->assertEquals(120000, $clara['expected']);   // billed by structure even without PR rows
        $this->assertEquals(120000, $clara['balance']);
    }

    /* ---------------- HTTP / permissions / exports ---------------- */

    /** @test */
    public function finance_reports_block_teachers_but_allow_account_level_users()
    {
        $this->actingAs($this->teacher)
            ->get(route('reports.payments'))
            ->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->get(route('reports.payments'))
            ->assertOk()
            ->assertSee('Finance Dashboard')
            ->assertSee(number_format(360000));
    }

    /** @test */
    public function student_reports_render_for_academic_staff()
    {
        $this->actingAs($this->superAdmin);

        $this->get(route('reports.students.register'))
            ->assertOk()->assertSee('Alice')->assertSee('A002');
        $this->get(route('reports.demographics'))->assertOk();
        $this->get(route('reports.enrollment'))->assertOk();
        $this->get(route('reports.academic'))->assertOk();
    }

    /** @test */
    public function csv_exports_stream_with_metadata()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('reports.fee_status.csv', ['export' => 'csv']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function register_pdf_downloads()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('reports.students.register', ['format' => 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function search_filters_fee_status_rows()
    {
        $svc = app(\App\Services\Reports\FinanceReportService::class);
        [, $pag] = $svc->feeStatus('2025-2026', null, null, 'Alice', 'all');

        $this->assertCount(1, $pag->items());
        $this->assertSame('Alice', $pag->items()[0]->student);
    }
}
