<?php

declare(strict_types=1);

namespace App\Domain\Project;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class ProjectMember
{
    public function __construct(
        public int $id,
        public int $projectId,
        public int $userId,
        public DateTimeImmutable $createdAt,
    ) {
        if ($projectId <= 0) {
            throw new InvalidArgumentException('ProjectMember projectId must be positive');
        }

        if ($userId <= 0) {
            throw new InvalidArgumentException('ProjectMember userId must be positive');
        }
    }
}
