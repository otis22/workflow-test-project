<?php

declare(strict_types=1);

namespace App\Domain\Comment;

use App\Domain\Project\Project;
use DomainException;
use InvalidArgumentException;

final readonly class Comment
{
    public function __construct(
        public int $id,
        public int $taskId,
        public int $authorId,
        public string $body,
    ) {}

    public static function create(
        int $id,
        int $taskId,
        int $authorId,
        string $body,
        Project $project,
    ): self {
        if (trim($body) === '') {
            throw new InvalidArgumentException('Comment body cannot be empty.');
        }

        if (! $project->isMember($authorId)) {
            throw new DomainException('Only project members can comment.');
        }

        return new self(
            id: $id,
            taskId: $taskId,
            authorId: $authorId,
            body: $body,
        );
    }
}
