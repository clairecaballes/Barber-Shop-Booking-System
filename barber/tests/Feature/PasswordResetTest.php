<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_code_is_mailed_and_token_stored_for_matching_email(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');

        Mail::assertSent(PasswordResetCode::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_unknown_email_gets_the_same_generic_reply(): void
    {
        Mail::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'nobody@nowhere.test'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');

        Mail::assertNothingSent();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'nobody@nowhere.test']);
    }

    public function test_reset_with_valid_code_updates_password_and_clears_token(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->from(route('password.reset'))
            ->post(route('password.update'), [
                'email' => $user->email,
                'code' => '123456',
                'password' => 'newsecret1',
                'password_confirmation' => 'newsecret1',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('newsecret1', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_rejects_a_wrong_code(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->from(route('password.reset'))
            ->post(route('password.update'), [
                'email' => $user->email,
                'code' => '999999',
                'password' => 'newsecret1',
                'password_confirmation' => 'newsecret1',
            ])
            ->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_reset_rejects_an_expired_code(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(20),
        ]);

        $this->from(route('password.reset'))
            ->post(route('password.update'), [
                'email' => $user->email,
                'code' => '123456',
                'password' => 'newsecret1',
                'password_confirmation' => 'newsecret1',
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}