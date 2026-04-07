<?php

declare(strict_types=1);

use App\Application\Hashing\PasswordHasher;
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

it('accepts a password of exactly the minimum length', function (): void {
    $user = makeRegisterUser()->execute('Alice', 'alice@example.com', '12345678');

    expect($user->passwordHash)->toBe('hashed:12345678');
});

it('does not hash the password when name or email are invalid', function (): void {
    $hasher = new class implements PasswordHasher
    {
        public int $hashCalls = 0;

        public function hash(string $plain): string
        {
            $this->hashCalls++;

            return 'hashed:'.$plain;
        }

        public function verify(string $plain, string $hash): bool
        {
            return $hash === 'hashed:'.$plain;
        }
    };

    $useCase = new RegisterUser(new InMemoryUserRepository, $hasher, new FakeClock);

    try {
        $useCase->execute('Alice', 'not-an-email', '12345678');
    } catch (InvalidArgumentException) {
        // expected
    }

    expect($hasher->hashCalls)->toBe(0);
});

it('assigns sequential ids on multiple registrations', function (): void {
    $repo = new InMemoryUserRepository;
    $useCase = makeRegisterUser($repo);

    $a = $useCase->execute('Alice', 'a@example.com', 'super-secret');
    $b = $useCase->execute('Bob', 'b@example.com', 'super-secret');

    expect($a->id)->toBe(1)
        ->and($b->id)->toBe(2);
});
