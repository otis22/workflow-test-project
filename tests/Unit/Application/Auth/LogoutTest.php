<?php

declare(strict_types=1);

use App\Application\Auth\Logout;
use Tests\Support\Fakes\InMemorySessionGuard;

it('clears the session', function (): void {
    $session = new InMemorySessionGuard;
    $session->login(42);

    (new Logout($session))->execute();

    expect($session->currentUserId())->toBeNull();
});

it('is idempotent on an empty session', function (): void {
    $session = new InMemorySessionGuard;

    (new Logout($session))->execute();

    expect($session->currentUserId())->toBeNull();
});
