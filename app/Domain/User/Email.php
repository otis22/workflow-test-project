<?php

declare(strict_types=1);

namespace App\Domain\User;

use InvalidArgumentException;

final readonly class Email implements \Stringable
{
    private string $email;

    public function __construct(string $email)
    {
        $email = trim($email);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: {$email}");
        }

        $this->email = $email;
    }

    public function value(): string
    {
        return $this->email;
    }

    public function equals(self $other): bool
    {
        return $this->email === $other->email;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
