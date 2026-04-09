<?php

declare(strict_types=1);

use App\Application\Hashing\PasswordHasher;

it('hashes a password producing a non-empty distinct string', function (): void {
    /** @var PasswordHasher $hasher */
    $hasher = app(PasswordHasher::class);

    $hash = $hasher->hash('super-secret');

    expect($hash)->toBeString()
        ->and($hash)->not->toBe('super-secret')
        ->and(strlen($hash))->toBeGreaterThan(20);
});

it('verifies a password against its own hash', function (): void {
    /** @var PasswordHasher $hasher */
    $hasher = app(PasswordHasher::class);

    $hash = $hasher->hash('super-secret');

    expect($hasher->verify('super-secret', $hash))->toBeTrue();
});

it('rejects verify with a wrong password', function (): void {
    /** @var PasswordHasher $hasher */
    $hasher = app(PasswordHasher::class);

    $hash = $hasher->hash('super-secret');

    expect($hasher->verify('wrong', $hash))->toBeFalse();
});
