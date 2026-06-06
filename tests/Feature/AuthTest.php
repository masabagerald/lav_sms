<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckLicense;
use App\Models\Setting;
use App\Services\LicenseService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The CheckLicense middleware blocks every route when no license file exists.
        $this->mock(LicenseService::class, function ($mock) {
            $mock->shouldReceive('validate')->andReturn(['valid' => true]);
        });

        // The login view calls Qs::getSystemName() which reads from the settings table.
        Setting::insert([
            ['type' => 'system_name', 'description' => 'Test School'],
            ['type' => 'system_title', 'description' => 'TS'],
            ['type' => 'current_session', 'description' => '2025-2026'],
            ['type' => 'lock_exam', 'description' => '0'],
        ]);
    }

    // ─── Login page ───────────────────────────────────────────────────────────

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_login_page_shows_identity_and_password_fields()
    {
        $response = $this->get('/login');
        $response->assertSee('identity', false);
        $response->assertSee('password', false);
    }

    // ─── Authentication ───────────────────────────────────────────────────────
    // The app uses an 'identity' field that accepts either email or username.

    public function test_login_with_email_authenticates_user()
    {
        $user = User::factory()->create([
            'password'  => Hash::make('secret'),
            'user_type' => 'admin',
        ]);

        $this->post('/login', [
            'identity' => $user->email,
            'password' => 'secret',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_username_authenticates_user()
    {
        $user = User::factory()->create([
            'password'  => Hash::make('secret'),
            'user_type' => 'teacher',
        ]);

        $this->post('/login', [
            'identity' => $user->username,
            'password' => 'secret',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_is_rejected()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct'),
        ]);

        $this->post('/login', [
            'identity' => $user->email,
            'password' => 'wrong',
        ]);

        $this->assertGuest();
    }

    public function test_non_existent_user_is_rejected()
    {
        $this->post('/login', [
            'identity' => 'nobody@school.test',
            'password' => 'anything',
        ]);

        $this->assertGuest();
    }

    public function test_login_with_blank_identity_is_rejected()
    {
        // The custom username() method uses 'identity'; submitting without it
        // fails validation before the auth attempt.
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        $this->post('/login', ['identity' => '', 'password' => 'secret']);

        $this->assertGuest();
    }

    // ─── Route protection ─────────────────────────────────────────────────────

    public function test_dashboard_redirects_guests_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_users_index_redirects_guests_to_login()
    {
        $response = $this->get('/users');
        $response->assertRedirect('/login');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_log_out()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }
}
