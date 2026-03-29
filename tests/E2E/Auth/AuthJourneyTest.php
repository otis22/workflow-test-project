<?php

namespace Tests\E2E\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_complete_the_auth_journey(): void
    {
        $registrationResponse = $this->registerUser(
            name: 'Ada Lovelace',
            email: 'ada@example.com',
            password: 'password123',
        );

        $registrationResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $dashboardResponse = $this->get(route('dashboard'));

        $dashboardResponse
            ->assertOk()
            ->assertSee('Welcome back, Ada Lovelace');

        $logoutResponse = $this
            ->withSession(['_token' => 'logout-token'])
            ->post(route('logout'), [
                '_token' => 'logout-token',
            ]);

        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();

        $loginResponse = $this->loginUser(
            email: 'ada@example.com',
            password: 'password123',
        );

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
