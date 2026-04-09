<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\User\RegisterUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::get('/_test-protected', fn (): string => 'protected-ok')
        ->middleware('auth.session');
});

it('redirects guests to the login page', function (): void {
    $response = $this->get('/_test-protected');

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('passes through authenticated requests', function (): void {
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $user = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);

    $response = $this->get('/_test-protected');

    $response->assertStatus(200)->assertSee('protected-ok');
});

it('remembers the intended URL for redirect-after-login', function (): void {
    $this->get('/_test-protected');

    expect(session()->get('url.intended'))->toBe(url('/_test-protected'));
});
