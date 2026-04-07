<?php

declare(strict_types=1);

use App\Application\User\Exception\EmailAlreadyTakenException;
use App\Application\User\Exception\WeakPasswordException;
use App\Application\User\RegisterUser;
use App\Domain\User\User;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeRegisterUser(
    ?InMemoryUserRepository $repo = null,
    ?FakeClock $clock = null,
): RegisterUser {
    return new RegisterUser(
        $repo ?? new InMemoryUserRepository,
        new FakePasswordHasher,
        $clock ?? new FakeClock,
    );
}

it('registers a new user with hashed password and clock timestamps', function (): void {
    $repo = new InMemoryUserRepository;
    $clock = new FakeClock(new DateTimeImmutable('2026-04-07T10:00:00Z'));
    $useCase = makeRegisterUser($repo, $clock);

    $user = $useCase->execute('Alice', 'alice@example.com', 'super-secret');

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->id)->toBe(1)
        ->and($user->name)->toBe('Alice')
        ->and($user->email)->toBe('alice@example.com')
        ->and($user->passwordHash)->toBe('hashed:super-secret')
        ->and($user->createdAt)->toEqual($clock->now())
        ->and($user->updatedAt)->toEqual($clock->now());

    expect($repo->findByEmail('alice@example.com'))->not->toBeNull();
});

it('rejects duplicate email', function (): void {
    $repo = new InMemoryUserRepository;
    $useCase = makeRegisterUser($repo);
    $useCase->execute('Alice', 'alice@example.com', 'super-secret');

    $useCase->execute('Alice2', 'alice@example.com', 'another-pass');
})->throws(EmailAlreadyTakenException::class);

it('rejects weak password (less than 8 chars)', function (): void {
    makeRegisterUser()->execute('Alice', 'alice@example.com', 'short');
})->throws(WeakPasswordException::class);

it('rejects invalid email via domain entity validation', function (): void {
    makeRegisterUser()->execute('Alice', 'not-an-email', 'super-secret');
})->throws(InvalidArgumentException::class, 'User email is not valid');

it('rejects empty name via domain entity validation', function (): void {
    makeRegisterUser()->execute('', 'alice@example.com', 'super-secret');
})->throws(InvalidArgumentException::class, 'User name must not be empty');

it('assigns sequential ids on multiple registrations', function (): void {
    $repo = new InMemoryUserRepository;
    $useCase = makeRegisterUser($repo);

    $a = $useCase->execute('Alice', 'a@example.com', 'super-secret');
    $b = $useCase->execute('Bob', 'b@example.com', 'super-secret');

    expect($a->id)->toBe(1)
        ->and($b->id)->toBe(2);
});
