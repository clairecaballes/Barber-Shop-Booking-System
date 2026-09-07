<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_login_with_valid_credentials(): void
    {
        $owner = $this->createOwner();

        $this->post(route('login.attempt'), [
            'email' => 'owner@test.com',
            'password' => 'password',
        ])->assertRedirect(route('calendar.index'));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $this->post(route('login.attempt'), [
            'email' => 'owner@test.com',
            'password' => 'wrong-password',
        ])->assertRedirectBack()
            ->assertSessionHasErrors('email');
    }

    public function test_logout(): void
    {
        $this->actingAs($this->createOwner());

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_dashboard_is_accessible_when_authenticated(): void
    {
        $this->actingAs($this->createOwner());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_account_update_profile(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner);

        $this->patch(route('account.update'), [
            'name' => 'New Name',
            'email' => 'new@test.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'name' => 'New Name',
            'email' => 'new@test.com',
        ]);
    }

    public function test_account_change_password(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner);

        $this->patch(route('account.password'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-secret-password', $owner->fresh()->password));
    }

    public function test_account_password_wrong_current_password(): void
    {
        $this->actingAs($this->createOwner());

        $this->patch(route('account.password'), [
            'current_password' => 'wrong',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertRedirectBack()
            ->assertSessionHasErrors('current_password');
    }
}
