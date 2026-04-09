<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use Illuminate\Contracts\Session\Session;

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

it('login migrates the session id to defend against session fixation', function (): void {
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    /** @var Session $session */
    $session = app(Session::class);

    // Start a session so we have a stable id to compare against.
    $session->start();
    $idBefore = $session->getId();

    $guard->login(42);

    expect($session->getId())->not->toBe($idBefore)
        ->and($guard->currentUserId())->toBe(42);
});
