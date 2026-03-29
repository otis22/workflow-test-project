<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response
            ->assertOk()
            ->assertSee('Sign in to TaskFlow');
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'password123',
        ]);

        $response = $this
            ->withSession(['_token' => 'login-token'])
            ->post(route('login.store'), [
                '_token' => 'login-token',
                'email' => $user->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_log_in_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'password123',
        ]);

        $response = $this
            ->from(route('login'))
            ->withSession(['_token' => 'login-token'])
            ->post(route('login.store'), [
                '_token' => 'login-token',
                'email' => 'ada@example.com',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['_token' => 'logout-token'])
            ->post(route('logout'), [
                '_token' => 'logout-token',
            ]);

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
