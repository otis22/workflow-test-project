<?php

declare(strict_types=1);

namespace App\Domain\Comment;

use App\Domain\Project\Project;
use App\Domain\Shared\DomainRuleViolation;
use DateTimeImmutable;

final readonly class Comment
{
    private function __construct(
        public string $id,
        public string $taskId,
        public string $authorId,
        public string $body,
        public DateTimeImmutable $createdAt,
    ) {}

    public static function add(
        string $id,
        Project $project,
        string $taskId,
        string $authorId,
        string $body,
        DateTimeImmutable $createdAt,
    ): self {
        if (trim($id) === '') {
            throw new DomainRuleViolation('Comment id is required.');
        }

        if (trim($taskId) === '') {
            throw new DomainRuleViolation('Comment task id is required.');
        }

        if (! $project->hasMember($authorId)) {
            throw new DomainRuleViolation('Comment author must be a project member.');
        }

        if (trim($body) === '') {
            throw new DomainRuleViolation('Comment body is required.');
        }

        return new self(
            id: $id,
            taskId: $taskId,
            authorId: $authorId,
            body: trim($body),
            createdAt: $createdAt,
        );
    }
}
