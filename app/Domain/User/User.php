<?php

declare(strict_types=1);

namespace App\Domain\User;

use InvalidArgumentException;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $name,
        public Email $email,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('User name cannot be empty.');
        }
    }
}
