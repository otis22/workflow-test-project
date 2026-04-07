<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Comment
{
    public function __construct(
        public int $id,
        public int $taskId,
        public int $authorId,
        public string $body,
        public DateTimeImmutable $createdAt,
    ) {
        if ($taskId <= 0) {
            throw new InvalidArgumentException('Comment taskId must be positive');
        }

        if ($authorId <= 0) {
            throw new InvalidArgumentException('Comment authorId must be positive');
        }

        if (trim($body) === '') {
            throw new InvalidArgumentException('Comment body must not be empty');
        }
    }
}
