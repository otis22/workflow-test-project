<?php

declare(strict_types=1);

namespace App\Domain\User;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $passwordHash,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('User name must not be empty');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('User email is not valid');
        }

        if (trim($passwordHash) === '') {
            throw new InvalidArgumentException('User password hash must not be empty');
        }

        if ($updatedAt->format('U.u') < $createdAt->format('U.u')) {
            throw new InvalidArgumentException('User updatedAt must not be earlier than createdAt');
        }
    }

    public function withName(string $name, DateTimeImmutable $updatedAt): self
    {
        return new self(
            id: $this->id,
            name: $name,
            email: $this->email,
            passwordHash: $this->passwordHash,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function withEmail(string $email, DateTimeImmutable $updatedAt): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $email,
            passwordHash: $this->passwordHash,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function withPasswordHash(string $passwordHash, DateTimeImmutable $updatedAt): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            passwordHash: $passwordHash,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
        );
    }
}
