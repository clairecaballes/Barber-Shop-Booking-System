<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function createOwner(): User
    {
        return User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);
    }

    public function test_owasp_security_headers_are_sent(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'same-origin');

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    public function test_login_is_throttled_per_email_and_ip(): void
    {
        Cache::flush();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.attempt'), [
                'email' => 'owner@test.com',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post(route('login.attempt'), [
            'email' => 'owner@test.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);

        Cache::flush();
    }

    public function test_password_change_revokes_other_devices_sessions(): void
    {
        $owner = $this->createOwner();
        $this->actingAs($owner);

        DB::table('sessions')->insert([
            'id' => 'stale-device-abc',
            'user_id' => $owner->id,
            'ip_address' => '10.0.0.2',
            'user_agent' => 'Tester',
            'payload' => 'a:0:{}',
            'last_activity' => time(),
        ]);

        $this->patch(route('account.password'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('sessions', ['id' => 'stale-device-abc']);
        $this->assertTrue(Hash::check('new-secret-password', $owner->fresh()->password));
    }

    public function test_password_change_rotates_session_id_and_csrf_token(): void
    {
        $owner = $this->createOwner();
        $this->actingAs($owner);

        $oldId = $this->app['session']->getId();
        $oldToken = $this->app['session']->token();

        $this->patch(route('account.password'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertRedirect();

        $this->assertNotSame($oldId, $this->app['session']->getId());
        $this->assertNotSame($oldToken, $this->app['session']->token());
    }

    public function test_logout_is_a_csrf_protected_post(): void
    {
        $this->createOwner();

        $this->get(route('logout'))
            ->assertMethodNotAllowed();
    }
}
