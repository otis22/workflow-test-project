<?php

declare(strict_types=1);

namespace App\Infrastructure\Hashing;

use App\Application\Hashing\PasswordHasher;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class LaravelPasswordHasher implements PasswordHasher
{
    public function __construct(private Hasher $hasher) {}

    #[\Override]
    public function hash(string $plain): string
    {
        return $this->hasher->make($plain);
    }

    #[\Override]
    public function verify(string $plain, string $hash): bool
    {
        return $this->hasher->check($plain, $hash);
    }
}
