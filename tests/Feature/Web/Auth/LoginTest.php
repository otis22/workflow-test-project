<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\User\RegisterUser;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function registerAlice(): int
{
    /** @var RegisterUser $useCase */
    $useCase = app(RegisterUser::class);
    $user = $useCase->execute('Alice', 'alice@example.com', 'super-secret');

    return $user->id;
}

it('shows the login form for guests', function (): void {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('name="password"', false)
        ->assertSee('method="POST"', false)
        ->assertSee('action="'.route('login').'"', false);
});

it('exposes the login route link from the home page', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee(route('login'));
});

it('redirects authenticated users away from the login form', function (): void {
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $existing = UserModel::query()->create([
        'name' => 'Existing',
        'email' => 'existing@example.com',
        'password_hash' => 'hash',
    ]);
    $guard->login((int) $existing->id);

    $response = $this->get('/login');

    $response->assertStatus(302)->assertRedirect('/');
});

it('logs in with correct credentials and redirects', function (): void {
    $aliceId = registerAlice();
    // Fresh guest session.
    $this->app['session']->flush();

    $response = $this->post('/login', [
        'email' => 'alice@example.com',
        'password' => 'super-secret',
    ]);

    $response->assertStatus(302)->assertRedirect('/');

    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    expect($guard->currentUserId())->toBe($aliceId);
});

it('logs in with case-insensitive email and surrounding whitespace', function (): void {
    registerAlice();
    $this->app['session']->flush();

    $response = $this->post('/login', [
        'email' => '  ALICE@Example.COM  ',
        'password' => 'super-secret',
    ]);

    $response->assertStatus(302)->assertRedirect('/');

    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    expect($guard->currentUserId())->not->toBeNull();
});

it('rejects unknown email with a generic credentials error', function (): void {
    $response = $this->from('/login')->post('/login', [
        'email' => 'ghost@example.com',
        'password' => 'super-secret',
    ]);

    $response->assertRedirect('/login')->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toBe('Invalid credentials.');
});

it('rejects wrong password with the same generic error as unknown email', function (): void {
    registerAlice();
    $this->app['session']->flush();

    $response = $this->from('/login')->post('/login', [
        'email' => 'alice@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login')->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toBe('Invalid credentials.');
});

it('rejects submission with an empty email', function (): void {
    $response = $this->from('/login')->post('/login', [
        'email' => '',
        'password' => 'super-secret',
    ]);

    $response->assertRedirect('/login')->assertSessionHasErrors('email');
});

it('rejects submission with an empty password', function (): void {
    $response = $this->from('/login')->post('/login', [
        'email' => 'alice@example.com',
        'password' => '',
    ]);

    $response->assertRedirect('/login')->assertSessionHasErrors('password');
});
