<?php

declare(strict_types=1);

use App\Domain\User\User;

it('creates a user with valid data', function (): void {
    $now = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );

    expect($user->id)->toBe(1)
        ->and($user->name)->toBe('Alice')
        ->and($user->email)->toBe('alice@example.com')
        ->and($user->passwordHash)->toBe('hashed')
        ->and($user->createdAt)->toEqual($now)
        ->and($user->updatedAt)->toEqual($now);
});

it('rejects whitespace-only name', function (): void {
    $now = new DateTimeImmutable;
    new User(
        id: 1,
        name: '   ',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'User name must not be empty');

it('rejects whitespace-only password hash', function (): void {
    $now = new DateTimeImmutable;
    new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: '   ',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'User password hash must not be empty');

it('rejects empty name', function (): void {
    $now = new DateTimeImmutable;
    new User(
        id: 1,
        name: '',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'User name must not be empty');

it('rejects invalid email', function (): void {
    $now = new DateTimeImmutable;
    new User(
        id: 1,
        name: 'Alice',
        email: 'not-an-email',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'User email is not valid');

it('rejects empty password hash', function (): void {
    $now = new DateTimeImmutable;
    new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: '',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'User password hash must not be empty');

it('returns a new instance with updated name and bumps updatedAt', function (): void {
    $created = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $updated = new DateTimeImmutable('2026-01-02T00:00:00Z');
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $created,
        updatedAt: $created,
    );

    $renamed = $user->withName('Bob', $updated);

    expect($renamed)->not->toBe($user)
        ->and($renamed->name)->toBe('Bob')
        ->and($renamed->email)->toBe('alice@example.com')
        ->and($renamed->createdAt)->toEqual($created)
        ->and($renamed->updatedAt)->toEqual($updated)
        ->and($user->name)->toBe('Alice');
});

it('returns a new instance with updated email and bumps updatedAt', function (): void {
    $created = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $updated = new DateTimeImmutable('2026-01-02T00:00:00Z');
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $created,
        updatedAt: $created,
    );

    $changed = $user->withEmail('alice2@example.com', $updated);

    expect($changed)->not->toBe($user)
        ->and($changed->email)->toBe('alice2@example.com')
        ->and($changed->createdAt)->toEqual($created)
        ->and($changed->updatedAt)->toEqual($updated)
        ->and($user->email)->toBe('alice@example.com')
        ->and($user->updatedAt)->toEqual($created);
});

it('rejects invalid email in withEmail', function (): void {
    $now = new DateTimeImmutable;
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );

    $user->withEmail('not-an-email', $now);
})->throws(InvalidArgumentException::class, 'User email is not valid');

it('returns a new instance with updated password hash and bumps updatedAt', function (): void {
    $created = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $updated = new DateTimeImmutable('2026-01-02T00:00:00Z');
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'old',
        createdAt: $created,
        updatedAt: $created,
    );

    $changed = $user->withPasswordHash('new', $updated);

    expect($changed)->not->toBe($user)
        ->and($changed->passwordHash)->toBe('new')
        ->and($changed->createdAt)->toEqual($created)
        ->and($changed->updatedAt)->toEqual($updated)
        ->and($user->passwordHash)->toBe('old')
        ->and($user->updatedAt)->toEqual($created);
});

it('rejects empty name in withName', function (): void {
    $now = new DateTimeImmutable;
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );

    $user->withName('  ', $now);
})->throws(InvalidArgumentException::class, 'User name must not be empty');

it('rejects empty password hash in withPasswordHash', function (): void {
    $now = new DateTimeImmutable;
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );

    $user->withPasswordHash('', $now);
})->throws(InvalidArgumentException::class, 'User password hash must not be empty');
