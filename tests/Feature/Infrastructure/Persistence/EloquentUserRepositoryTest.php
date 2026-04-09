<?php

declare(strict_types=1);

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeDomainUser(int $id = 0, string $email = 'alice@example.com'): User
{
    $now = new DateTimeImmutable('2026-04-09T10:00:00Z');

    return new User(
        id: $id,
        name: 'Alice',
        email: $email,
        passwordHash: 'hash:secret',
        createdAt: $now,
        updatedAt: $now,
    );
}

it('saves a new domain user and returns entity with assigned id', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);

    $saved = $repo->save(makeDomainUser());

    expect($saved)->toBeInstanceOf(User::class)
        ->and($saved->id)->toBeGreaterThan(0)
        ->and($saved->name)->toBe('Alice')
        ->and($saved->email)->toBe('alice@example.com')
        ->and($saved->passwordHash)->toBe('hash:secret');
});

it('finds a user by email, returning a domain entity', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);
    $repo->save(makeDomainUser());

    $found = $repo->findByEmail('alice@example.com');

    expect($found)->toBeInstanceOf(User::class)
        ->and($found->email)->toBe('alice@example.com')
        ->and($found->passwordHash)->toBe('hash:secret');
});

it('returns null from findByEmail when user does not exist', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);

    expect($repo->findByEmail('ghost@example.com'))->toBeNull();
});

it('finds a user by id', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);
    $saved = $repo->save(makeDomainUser());

    $found = $repo->findById($saved->id);

    expect($found)->toBeInstanceOf(User::class)
        ->and($found->id)->toBe($saved->id);
});

it('returns null from findById when user does not exist', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);

    expect($repo->findById(999))->toBeNull();
});

it('updates an existing user and preserves createdAt while bumping updatedAt', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);
    $saved = $repo->save(makeDomainUser());
    $originalCreatedAt = $saved->createdAt;

    $newUpdatedAt = new DateTimeImmutable('2026-04-09T11:00:00Z');
    $updated = $saved->withName('Alice Updated', $newUpdatedAt);
    $result = $repo->save($updated);

    expect($result->id)->toBe($saved->id)
        ->and($result->name)->toBe('Alice Updated')
        ->and($result->createdAt->format('U'))->toBe($originalCreatedAt->format('U'))
        ->and($result->updatedAt->format('U'))->toBe($newUpdatedAt->format('U'));

    // Reload and confirm typing preservation and persisted timestamp.
    $reloaded = $repo->findById($saved->id);
    expect($reloaded->name)->toBe('Alice Updated')
        ->and($reloaded->createdAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($reloaded->updatedAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($reloaded->updatedAt->format('U'))->toBe($newUpdatedAt->format('U'));
});

it('throws when saving a user with a positive id that does not exist', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);

    $stale = new User(
        id: 999,
        name: 'Ghost',
        email: 'ghost@example.com',
        passwordHash: 'hash',
        createdAt: new DateTimeImmutable,
        updatedAt: new DateTimeImmutable,
    );

    $repo->save($stale);
})->throws(RuntimeException::class, 'User with id 999 not found for update');

it('preserves createdAt and updatedAt as DateTimeImmutable on round-trip', function (): void {
    /** @var UserRepository $repo */
    $repo = app(UserRepository::class);
    $saved = $repo->save(makeDomainUser());

    $loaded = $repo->findById($saved->id);

    expect($loaded->createdAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($loaded->updatedAt)->toBeInstanceOf(DateTimeImmutable::class);
});
