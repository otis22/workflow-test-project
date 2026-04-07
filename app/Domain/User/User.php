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
        if ($name === '') {
            throw new InvalidArgumentException('User name must not be empty');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('User email is not valid');
        }

        if ($passwordHash === '') {
            throw new InvalidArgumentException('User password hash must not be empty');
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
