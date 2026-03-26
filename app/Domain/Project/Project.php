<?php

declare(strict_types=1);

namespace App\Domain\Project;

use InvalidArgumentException;

final class Project
{
    /** @var array<int, true> */
    private array $memberIds = [];

    public function __construct(
        public readonly int $id,
        public readonly int $ownerId,
        public readonly string $name,
        public readonly string $description,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Project name cannot be empty.');
        }

        $this->memberIds[$ownerId] = true;
    }

    public function isMember(int $userId): bool
    {
        return ($this->memberIds[$userId] ?? false) === true;
    }

    public function addMember(int $userId): void
    {
        $this->memberIds[$userId] = true;
    }
}
