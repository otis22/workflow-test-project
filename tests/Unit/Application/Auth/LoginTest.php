<?php

declare(strict_types=1);

use App\Application\Auth\Exception\InvalidCredentialsException;
use App\Application\Auth\Login;
use App\Application\User\RegisterUser;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemorySessionGuard;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeLoginFixture(): array
{
    $repo = new InMemoryUserRepository;
    $hasher = new FakePasswordHasher;
    $session = new InMemorySessionGuard;

    $register = new RegisterUser($repo, $hasher, new FakeClock);
    $register->execute('Alice', 'alice@example.com', 'super-secret');

    $login = new Login($repo, $hasher, $session);

    return [$login, $session];
}

it('logs in with correct credentials and sets session user id', function (): void {
    [$login, $session] = makeLoginFixture();

    $user = $login->execute('alice@example.com', 'super-secret');

    expect($user->email)->toBe('alice@example.com')
        ->and($session->currentUserId())->toBe($user->id);
});

it('rejects unknown email with InvalidCredentialsException', function (): void {
    [$login] = makeLoginFixture();

    $login->execute('ghost@example.com', 'super-secret');
})->throws(InvalidCredentialsException::class, 'Invalid credentials');

it('logs in with case-insensitive email and surrounding whitespace', function (): void {
    [$login, $session] = makeLoginFixture();

    $user = $login->execute('  ALICE@Example.COM  ', 'super-secret');

    expect($user->email)->toBe('alice@example.com')
        ->and($session->currentUserId())->toBe($user->id);
});

it('rejects wrong password with InvalidCredentialsException', function (): void {
    [$login] = makeLoginFixture();

    $login->execute('alice@example.com', 'wrong-pass');
})->throws(InvalidCredentialsException::class, 'Invalid credentials');

it('does not establish session on failure', function (): void {
    [$login, $session] = makeLoginFixture();

    try {
        $login->execute('alice@example.com', 'wrong-pass');
    } catch (InvalidCredentialsException) {
        // expected
    }

    expect($session->currentUserId())->toBeNull();
});

it('uses identical exception messages for unknown email and wrong password (no enumeration)', function (): void {
    [$login] = makeLoginFixture();

    $unknownMessage = null;
    $wrongPassMessage = null;

    try {
        $login->execute('ghost@example.com', 'super-secret');
    } catch (InvalidCredentialsException $e) {
        $unknownMessage = $e->getMessage();
    }

    try {
        $login->execute('alice@example.com', 'wrong-pass');
    } catch (InvalidCredentialsException $e) {
        $wrongPassMessage = $e->getMessage();
    }

    expect($unknownMessage)->toBe($wrongPassMessage);
});
