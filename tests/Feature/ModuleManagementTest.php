<?php

namespace Tests\Feature;

use App\Helpers\Qm;
use App\Models\ActivityLog;
use App\Models\Dorm;
use App\Models\Setting;
use App\Services\LicenseService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // License middleware would block every route otherwise
        $this->mock(LicenseService::class, function ($mock) {
            $mock->shouldReceive('validate')->andReturn(['valid' => true, 'data' => ['expires_at' => now()->addYear()], 'message' => '']);
        });

        // Minimal settings expected by layout/views
        Setting::insert([
            ['type' => 'system_name', 'description' => 'Test School'],
            ['type' => 'system_title', 'description' => 'TS'],
            ['type' => 'current_session', 'description' => '2025-2026'],
            ['type' => 'phone', 'description' => '0770000000'],
            ['type' => 'address', 'description' => 'Kampala'],
            ['type' => 'system_email', 'description' => 'test@school.com'],
            ['type' => 'logo', 'description' => ''],
            ['type' => 'term_ends', 'description' => ''],
            ['type' => 'term_begins', 'description' => ''],
            ['type' => 'lock_exam', 'description' => '0'],
            ['type' => 'alt_email', 'description' => ''],
            ['type' => 'email_host', 'description' => ''],
            ['type' => 'email_pass', 'description' => ''],
            ['type' => 'next_term_fees_o', 'description' => '0'],
            ['type' => 'next_term_fees_a', 'description' => '0'],
            ['type' => 'next_term_fees_s', 'description' => '0'],
            ['type' => 'next_term_fees_j', 'description' => '0'],
            ['type' => 'next_term_fees_p', 'description' => '0'],
            ['type' => 'next_term_fees_n', 'description' => '0'],
            ['type' => 'next_term_fees_c', 'description' => '0'],
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin', 'email' => 'sa@test.com', 'username' => 'sa',
            'password' => bcrypt('secret'), 'user_type' => 'super_admin',
            'code' => 'SA001', 'remember_token' => 'x',
        ]);

        $this->admin = User::create([
            'name' => 'Plain Admin', 'email' => 'admin@test.com', 'username' => 'admin',
            'password' => bcrypt('secret'), 'user_type' => 'admin',
            'code' => 'AD001', 'remember_token' => 'x',
        ]);
    }

    /** @test */
    public function all_modules_are_enabled_by_default()
    {
        foreach (Qm::all()->keys() as $slug) {
            $this->assertTrue(Qm::enabled($slug), "$slug should be enabled by default");
        }
    }

    /** @test */
    public function super_admin_sees_the_modules_console_with_cards()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('settings'))
            ->assertOk()
            ->assertSee('tab-modules')
            ->assertSee('Dormitories')
            ->assertSee('Examinations');
    }

    /** @test */
    public function module_cards_carry_real_slugs_not_indexes()
    {
        // Regression: Collection::groupBy() drops keys by default, which made the
        // UI post numeric "slugs" (0,1,2...) and fail with "Unknown module".
        $html = $this->actingAs($this->superAdmin)->get(route('settings'))->content();

        foreach (Qm::all()->keys() as $slug) {
            $this->assertStringContainsString('data-slug="' . $slug . '"', $html);
        }
    }

    /** @test */
    public function plain_admin_cannot_open_settings_or_toggle_modules()
    {
        $this->actingAs($this->admin)->get(route('settings'))->assertRedirect();

        $this->actingAs($this->admin)
            ->post(route('settings.modules.toggle'), ['slug' => 'dormitories'])
            ->assertRedirect();
    }

    /** @test */
    public function disabling_a_module_hides_it_from_menu_and_blocks_its_routes()
    {
        Dorm::create(['name' => 'Test Hostel']);

        $this->actingAs($this->superAdmin);

        // Disable dormitories (no dependents -> no force needed)
        $this->post(route('settings.modules.toggle'), ['slug' => 'dormitories'])
            ->assertOk()
            ->assertJson(['ok' => true, 'state' => 'disabled']);

        $this->assertFalse(Qm::enabled('dormitories'));

        // Sidebar hides the item
        $html = $this->get(route('dashboard'))->content();
        $this->assertStringNotContainsString('Dormitories</span>', $html);

        // Direct URL is blocked with a redirect + flash message
        $response = $this->get(route('dorms.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('flash_danger');

        // Data is untouched
        $this->assertDatabaseHas('dorms', ['name' => 'Test Hostel']);
    }

    /** @test */
    public function re_enabling_restores_navigation_and_access()
    {
        Dorm::create(['name' => 'Test Hostel']);

        $this->actingAs($this->superAdmin);
        Qm::persistDisabled(['dormitories']);

        $this->post(route('settings.modules.toggle'), ['slug' => 'dormitories'])
            ->assertOk()
            ->assertJson(['state' => 'enabled']);

        $this->assertTrue(Qm::enabled('dormitories'));

        $html = $this->get(route('dashboard'))->content();
        $this->assertStringContainsString('Dormitories</span>', $html);

        $this->get(route('dorms.index'))
            ->assertOk()
            ->assertSee('Test Hostel');
    }

    /** @test */
    public function required_modules_cannot_be_disabled()
    {
        $this->actingAs($this->superAdmin);

        foreach (Qm::all()->filter(function ($m) { return $m['required']; })->keys() as $slug) {
            $this->post(route('settings.modules.toggle'), ['slug' => $slug])
                ->assertStatus(422);

            $this->assertTrue(Qm::enabled($slug));
        }
    }

    /** @test */
    public function disabling_a_module_with_active_dependents_is_rejected_unless_forced()
    {
        $this->actingAs($this->superAdmin);

        // examinations depends_on students & classes (both enabled by default)
        $this->post(route('settings.modules.toggle'), ['slug' => 'students'])
            ->assertStatus(409)
            ->assertJsonFragment(['code' => 'dependent_modules']);

        $this->assertTrue(Qm::enabled('students'), 'students must remain enabled after rejected request');

        // Forced disable succeeds
        $this->post(route('settings.modules.toggle'), ['slug' => 'students', 'force' => 1])
            ->assertOk()
            ->assertJson(['state' => 'disabled']);

        $this->assertFalse(Qm::enabled('students'));

        // ...and dependent module routes are blocked too (examinations requires students? No:
        // examinations itself is still enabled but its dependency is off; its own routes stay
        // reachable because only ITS state gates them.)
        $this->assertFalse(Qm::enabled('examinations') === false, 'examinations state unchanged by students toggle');
    }

    /** @test */
    public function module_state_persists_across_requests_and_logins()
    {
        Qm::persistDisabled(['pins']);

        // Fresh user instance = fresh login
        $fresh = User::find($this->superAdmin->id);
        $this->actingAs($fresh);

        $this->assertFalse(Qm::enabled('pins'));
        $this->get(route('pins.index'))->assertRedirect(route('dashboard'));

        // Persisted in the settings table
        $this->assertDatabaseHas('settings', ['type' => Qm::SETTING_KEY]);
    }

    /** @test */
    public function toggling_creates_audit_log_entries()
    {
        $countBefore = ActivityLog::count();

        $this->actingAs($this->superAdmin)
            ->post(route('settings.modules.toggle'), ['slug' => 'timetables'])
            ->assertOk();

        $log = ActivityLog::latest('id')->first();
        $this->assertEquals("module.disabled", $log->action);
        $this->assertEquals($this->superAdmin->id, $log->user_id);
        $this->assertSame('disabled', $log->properties['new']);
        $this->assertSame('enabled', $log->properties['previous']);
        $this->assertEquals($countBefore + 1, ActivityLog::count());
    }

    /** @test */
    public function unknown_slugs_are_rejected()
    {
        $this->actingAs($this->superAdmin)
            ->post(route('settings.modules.toggle'), ['slug' => 'not-a-module'])
            ->assertStatus(404);
    }
}
