<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_registration_page(): void
    {
        $response = $this->get(route('register'));

        $response
            ->assertOk()
            ->assertSee('Create your TaskFlow account');
    }

    public function test_guest_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this
            ->withSession(['_token' => 'registration-token'])
            ->post(route('register.store'), [
                '_token' => 'registration-token',
                'name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);
    }

    public function test_registration_requires_valid_input(): void
    {
        $response = $this
            ->from(route('register'))
            ->withSession(['_token' => 'registration-token'])
            ->post(route('register.store'), [
                '_token' => 'registration-token',
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ]);

        $response
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors([
                'name',
                'email',
                'password',
            ]);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }
}
