<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the registration form for guests', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200)
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Confirm password')
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('method="POST"', false)
        ->assertSee('action="'.route('register').'"', false);
});

it('exposes the register route link from the home page', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee(route('register'));
});

it('redirects authenticated users away from the registration form', function (): void {
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $existing = UserModel::query()->create([
        'name' => 'Existing',
        'email' => 'existing@example.com',
        'password_hash' => 'hash',
    ]);
    $guard->login((int) $existing->id);

    $response = $this->get('/register');

    $response->assertStatus(302)->assertRedirect('/');
});

it('registers a new user and logs them in', function (): void {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'super-secret',
        'password_confirmation' => 'super-secret',
    ]);

    $response->assertStatus(302)->assertRedirect('/');

    /** @var UserRepository $users */
    $users = app(UserRepository::class);
    $created = $users->findByEmail('alice@example.com');
    expect($created)->not->toBeNull()
        ->and($created->name)->toBe('Alice');

    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    expect($guard->currentUserId())->toBe($created->id);
});

it('rejects registration with an empty name', function (): void {
    $response = $this->from('/register')->post('/register', [
        'name' => '',
        'email' => 'alice@example.com',
        'password' => 'super-secret',
        'password_confirmation' => 'super-secret',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('name');
});

it('rejects registration with an invalid email', function (): void {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Alice',
        'email' => 'not-an-email',
        'password' => 'super-secret',
        'password_confirmation' => 'super-secret',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('email');
});

it('rejects registration with a password shorter than 8 characters', function (): void {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('password');
});

it('rejects registration with a mismatched password confirmation', function (): void {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'super-secret',
        'password_confirmation' => 'different-secret',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('password');
});

it('rejects registration with a duplicate email case-insensitively', function (): void {
    $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'super-secret',
        'password_confirmation' => 'super-secret',
    ]);

    // Simulate a fresh guest session for the second attempt.
    $this->app['session']->flush();

    $response = $this->from('/register')->post('/register', [
        'name' => 'Alice2',
        'email' => 'ALICE@example.COM',
        'password' => 'another-secret',
        'password_confirmation' => 'another-secret',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('email');
});
