<?php

declare(strict_types=1);

use App\Domain\User\User;

it('creates a user with valid data', function (): void {
    $now = new DateTimeImmutable;
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
        ->and($user->passwordHash)->toBe('hashed');
});

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

it('returns a new instance with updated email', function (): void {
    $now = new DateTimeImmutable;
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'hashed',
        createdAt: $now,
        updatedAt: $now,
    );

    $changed = $user->withEmail('alice2@example.com', $now);

    expect($changed->email)->toBe('alice2@example.com')
        ->and($user->email)->toBe('alice@example.com');
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

it('returns a new instance with updated password hash', function (): void {
    $now = new DateTimeImmutable;
    $user = new User(
        id: 1,
        name: 'Alice',
        email: 'alice@example.com',
        passwordHash: 'old',
        createdAt: $now,
        updatedAt: $now,
    );

    $changed = $user->withPasswordHash('new', $now);

    expect($changed->passwordHash)->toBe('new')
        ->and($user->passwordHash)->toBe('old');
});
