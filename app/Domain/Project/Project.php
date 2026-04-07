<?php

declare(strict_types=1);

namespace App\Domain\Project;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Project
{
    public function __construct(
        public int $id,
        public int $ownerId,
        public string $name,
        public string $description,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
        if ($ownerId <= 0) {
            throw new InvalidArgumentException('Project must have an owner');
        }

        if (trim($name) === '') {
            throw new InvalidArgumentException('Project name must not be empty');
        }
    }

    public function withName(string $name, DateTimeImmutable $updatedAt): self
    {
        return new self(
            id: $this->id,
            ownerId: $this->ownerId,
            name: $name,
            description: $this->description,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function withDescription(string $description, DateTimeImmutable $updatedAt): self
    {
        return new self(
            id: $this->id,
            ownerId: $this->ownerId,
            name: $this->name,
            description: $description,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
        );
    }
}
