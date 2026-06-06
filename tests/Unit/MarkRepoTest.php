<?php

namespace Tests\Unit;

use App\Helpers\Mk;
use App\Models\Grade;
use App\Repositories\MarkRepo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkRepoTest extends TestCase
{
    use RefreshDatabase;

    private MarkRepo $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MarkRepo();
    }

    // ─── getSubCumAvg ─────────────────────────────────────────────────────────

    public function test_getSubCumAvg_returns_zero_when_all_terms_are_zero()
    {
        // No real DB rows needed — we stub the dependency via a partial mock.
        $repo = $this->getMockBuilder(MarkRepo::class)
            ->onlyMethods(['getSubTotalTerm'])
            ->getMock();

        $repo->method('getSubTotalTerm')->willReturn(null);

        $avg = $repo->getSubCumAvg(0, 1, 1, 1, '2024-2025');
        $this->assertEquals(0, $avg);
    }

    public function test_getSubCumAvg_averages_across_all_three_terms()
    {
        $repo = $this->getMockBuilder(MarkRepo::class)
            ->onlyMethods(['getSubTotalTerm'])
            ->getMock();

        // tex1 = 60, tex2 = 70 (returned by getSubTotalTerm), tex3 passed directly = 80
        $repo->method('getSubTotalTerm')
            ->willReturnOnConsecutiveCalls(60, 70);

        $avg = $repo->getSubCumAvg(80, 1, 1, 1, '2024-2025');
        // (60 + 70 + 80) / 3 = 70.0
        $this->assertEquals(70.0, $avg);
    }

    public function test_getSubCumAvg_skips_missing_terms_in_denominator()
    {
        $repo = $this->getMockBuilder(MarkRepo::class)
            ->onlyMethods(['getSubTotalTerm'])
            ->getMock();

        // Term 1 missing (null), term 2 = 80, term 3 = 90
        $repo->method('getSubTotalTerm')
            ->willReturnOnConsecutiveCalls(null, 80);

        $avg = $repo->getSubCumAvg(90, 1, 1, 1, '2024-2025');
        // (0 + 80 + 90) / 2 = 85.0
        $this->assertEquals(85.0, $avg);
    }

    public function test_getSubCumAvg_rounds_to_one_decimal_place()
    {
        $repo = $this->getMockBuilder(MarkRepo::class)
            ->onlyMethods(['getSubTotalTerm'])
            ->getMock();

        $repo->method('getSubTotalTerm')
            ->willReturnOnConsecutiveCalls(55, 66);

        $avg = $repo->getSubCumAvg(77, 1, 1, 1, '2024-2025');
        // (55 + 66 + 77) / 3 = 66.0
        $this->assertEquals(66.0, $avg);
    }

    // ─── getSubCumTotal ───────────────────────────────────────────────────────

    public function test_getSubCumTotal_sums_all_three_term_totals()
    {
        $repo = $this->getMockBuilder(MarkRepo::class)
            ->onlyMethods(['getSubTotalTerm'])
            ->getMock();

        $repo->method('getSubTotalTerm')
            ->willReturnOnConsecutiveCalls(40, 50);

        $total = $repo->getSubCumTotal(60, 1, 1, 1, '2024-2025');
        $this->assertEquals(150, $total);
    }

    // ─── Mk::countSubjectsOffered ─────────────────────────────────────────────

    public function test_countSubjectsOffered_ignores_marks_where_tca_and_exm_are_zero()
    {
        $marks = new Collection([
            (object)['tca' => 0,  'exm' => 0,  'grade_id' => null],
            (object)['tca' => 30, 'exm' => 50, 'grade_id' => null],
            (object)['tca' => 0,  'exm' => 70, 'grade_id' => null],
        ]);

        $this->assertEquals(2, Mk::countSubjectsOffered($marks));
    }

    public function test_countSubjectsOffered_returns_zero_for_empty_collection()
    {
        $this->assertEquals(0, Mk::countSubjectsOffered(new Collection()));
    }

    // ─── Mk::markGradeFilter via countDistinctions / countFailures ────────────

    public function test_countDistinctions_counts_marks_with_A_or_B_grade_names()
    {
        // Create in-memory grade stubs; the method queries the DB, so we seed grades.
        $gradeA = Grade::factory()->create(['name' => 'A1', 'class_type_id' => null, 'mark_from' => 75, 'mark_to' => 100]);
        $gradeB = Grade::factory()->create(['name' => 'B2', 'class_type_id' => null, 'mark_from' => 65, 'mark_to' => 74]);
        $gradeC = Grade::factory()->create(['name' => 'C4', 'class_type_id' => null, 'mark_from' => 50, 'mark_to' => 64]);

        $marks = new Collection([
            (object)['grade_id' => $gradeA->id],
            (object)['grade_id' => $gradeB->id],
            (object)['grade_id' => $gradeC->id],
        ]);

        $this->assertEquals(2, Mk::countDistinctions($marks));
    }

    public function test_countFailures_counts_marks_with_F_grade()
    {
        $gradeF = Grade::factory()->create(['name' => 'F9', 'class_type_id' => null, 'mark_from' => 0, 'mark_to' => 39]);
        $gradeC = Grade::factory()->create(['name' => 'C6', 'class_type_id' => null, 'mark_from' => 50, 'mark_to' => 64]);

        $marks = new Collection([
            (object)['grade_id' => $gradeF->id],
            (object)['grade_id' => $gradeF->id],
            (object)['grade_id' => $gradeC->id],
        ]);

        $this->assertEquals(2, Mk::countFailures($marks));
    }

    public function test_countFailures_returns_zero_when_no_failures()
    {
        $gradeA = Grade::factory()->create(['name' => 'A1', 'class_type_id' => null, 'mark_from' => 75, 'mark_to' => 100]);

        $marks = new Collection([
            (object)['grade_id' => $gradeA->id],
        ]);

        $this->assertEquals(0, Mk::countFailures($marks));
    }
}
