<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;

it('currentUserId returns null when no one is logged in', function (): void {
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);

    expect($guard->currentUserId())->toBeNull();
});

it('login stores the user id and currentUserId reads it back', function (): void {
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);

    $guard->login(42);

    expect($guard->currentUserId())->toBe(42);
});

it('logout clears the stored user id', function (): void {
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login(42);

    $guard->logout();

    expect($guard->currentUserId())->toBeNull();
});
