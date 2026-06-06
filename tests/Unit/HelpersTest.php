<?php

namespace Tests\Unit;

use App\Helpers\Mk;
use App\Helpers\Qs;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    // ─── Mk::getSuffix ────────────────────────────────────────────────────────

    public function test_getSuffix_returns_null_for_zero_or_negative()
    {
        $this->assertNull(Mk::getSuffix(0));
        $this->assertNull(Mk::getSuffix(-1));
    }

    public function test_getSuffix_st_nd_rd_th_for_normal_numbers()
    {
        $this->assertStringContainsString('st', Mk::getSuffix(1));
        $this->assertStringContainsString('nd', Mk::getSuffix(2));
        $this->assertStringContainsString('rd', Mk::getSuffix(3));
        $this->assertStringContainsString('th', Mk::getSuffix(4));
        $this->assertStringContainsString('th', Mk::getSuffix(10));
        $this->assertStringContainsString('th', Mk::getSuffix(20));
    }

    public function test_getSuffix_teen_numbers_always_use_th()
    {
        // 11th, 12th, 13th — the "teens" exception
        $this->assertStringContainsString('th', Mk::getSuffix(11));
        $this->assertStringContainsString('th', Mk::getSuffix(12));
        $this->assertStringContainsString('th', Mk::getSuffix(13));
        // 111, 112, 113 also follow the same rule
        $this->assertStringContainsString('th', Mk::getSuffix(111));
        $this->assertStringContainsString('th', Mk::getSuffix(112));
        $this->assertStringContainsString('th', Mk::getSuffix(113));
    }

    public function test_getSuffix_21st_22nd_23rd_are_not_teens()
    {
        $this->assertStringContainsString('st', Mk::getSuffix(21));
        $this->assertStringContainsString('nd', Mk::getSuffix(22));
        $this->assertStringContainsString('rd', Mk::getSuffix(23));
    }

    // ─── Qs::getMarkType ──────────────────────────────────────────────────────

    public function test_getMarkType_returns_correct_label_for_each_code()
    {
        $this->assertEquals('junior',      Qs::getMarkType('J'));
        $this->assertEquals('senior',      Qs::getMarkType('S'));
        $this->assertEquals('nursery',     Qs::getMarkType('N'));
        $this->assertEquals('primary',     Qs::getMarkType('P'));
        $this->assertEquals('pre_nursery', Qs::getMarkType('PN'));
        $this->assertEquals('creche',      Qs::getMarkType('C'));
    }

    public function test_getMarkType_returns_input_unchanged_for_unknown_code()
    {
        $this->assertEquals('X', Qs::getMarkType('X'));
        $this->assertEquals('',  Qs::getMarkType(''));
    }

    // ─── Qs role / team helpers ───────────────────────────────────────────────

    public function test_getTeamSA_contains_admin_and_super_admin_only()
    {
        $team = Qs::getTeamSA();
        $this->assertEqualsCanonicalizing(['admin', 'super_admin'], $team);
    }

    public function test_getTeamAccount_includes_accountant()
    {
        $this->assertContains('accountant', Qs::getTeamAccount());
        $this->assertContains('admin',      Qs::getTeamAccount());
        $this->assertContains('super_admin',Qs::getTeamAccount());
    }

    public function test_getStaff_excludes_student_and_parent()
    {
        $staff = Qs::getStaff();
        $this->assertNotContains('student', $staff);
        $this->assertNotContains('parent',  $staff);
    }

    public function test_getStaff_with_remove_excludes_given_types()
    {
        $staff = Qs::getStaff(['teacher', 'accountant']);
        $this->assertNotContains('teacher',   $staff);
        $this->assertNotContains('accountant', $staff);
        $this->assertContains('admin', $staff);
    }

    public function test_getAllUserTypes_contains_all_seven_roles()
    {
        $types = Qs::getAllUserTypes();
        foreach (['super_admin', 'admin', 'teacher', 'accountant', 'librarian', 'student', 'parent'] as $role) {
            $this->assertContains($role, $types);
        }
    }

    public function test_getAllUserTypes_with_remove_omits_given_roles()
    {
        $types = Qs::getAllUserTypes(['student', 'parent']);
        $this->assertNotContains('student', $types);
        $this->assertNotContains('parent',  $types);
    }

    // ─── Qs::getUserRecord / getStudentData ───────────────────────────────────

    public function test_getUserRecord_returns_full_list_when_no_exclusions()
    {
        $fields = Qs::getUserRecord();
        $this->assertContains('name',   $fields);
        $this->assertContains('email',  $fields);
        $this->assertContains('phone',  $fields);
    }

    public function test_getUserRecord_removes_specified_fields()
    {
        $fields = Qs::getUserRecord(['email', 'phone']);
        $this->assertNotContains('email', $fields);
        $this->assertNotContains('phone', $fields);
        $this->assertContains('name', $fields);
    }

    public function test_getStudentData_returns_required_student_fields()
    {
        $fields = Qs::getStudentData();
        $this->assertContains('my_class_id',   $fields);
        $this->assertContains('section_id',    $fields);
        $this->assertContains('year_admitted', $fields);
    }

    // ─── Qs::headSA ───────────────────────────────────────────────────────────

    public function test_headSA_returns_true_only_for_user_id_1()
    {
        $this->assertTrue(Qs::headSA(1));
        $this->assertFalse(Qs::headSA(2));
        $this->assertFalse(Qs::headSA(0));
    }

    // ─── Qs::getRemarks ───────────────────────────────────────────────────────

    public function test_getRemarks_is_non_empty_and_contains_pass_and_fail()
    {
        $remarks = Mk::getRemarks();
        $this->assertNotEmpty($remarks);
        $this->assertContains('Pass', $remarks);
        $this->assertContains('Fail', $remarks);
    }

    // ─── Qs::getDaysOfTheWeek ─────────────────────────────────────────────────

    public function test_getDaysOfTheWeek_returns_exactly_7_days()
    {
        $this->assertCount(7, Qs::getDaysOfTheWeek());
        $this->assertContains('Monday',  Qs::getDaysOfTheWeek());
        $this->assertContains('Sunday',  Qs::getDaysOfTheWeek());
    }

    // ─── Qs::generateUserCode ─────────────────────────────────────────────────

    public function test_generateUserCode_produces_7_char_string()
    {
        $code = Qs::generateUserCode();
        $this->assertEquals(7, strlen($code));
    }

    public function test_generateUserCode_generates_unique_codes()
    {
        $codes = array_unique(array_map(fn() => Qs::generateUserCode(), range(1, 20)));
        // All 20 should be unique (collisions astronomically unlikely with uniqid)
        $this->assertCount(20, $codes);
    }
}
