<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\User\RegisterUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginAlice(): int
{
    /** @var RegisterUser $useCase */
    $useCase = app(RegisterUser::class);
    $user = $useCase->execute('Alice', 'alice@example.com', 'super-secret');

    // RegisterUser does not log in on its own; call SessionGuard explicitly.
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);

    return $user->id;
}

it('logs out an authenticated user and redirects to home', function (): void {
    $aliceId = loginAlice();

    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    expect($guard->currentUserId())->toBe($aliceId);

    $response = $this->post('/logout');

    $response->assertStatus(302)->assertRedirect('/');
    expect(app(SessionGuard::class)->currentUserId())->toBeNull();
});

it('is idempotent when called as a guest', function (): void {
    $response = $this->post('/logout');

    $response->assertStatus(302)->assertRedirect('/');
});

it('rejects logout via GET', function (): void {
    $response = $this->get('/logout');

    $response->assertStatus(405);
});

it('nav shows a logout form when the user is authenticated', function (): void {
    loginAlice();

    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('Logout');
});
