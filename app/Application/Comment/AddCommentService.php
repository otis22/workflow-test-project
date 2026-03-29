<?php

declare(strict_types=1);

namespace App\Application\Comment;

use App\Application\Contracts\CommentRepository;
use App\Application\Contracts\ProjectRepository;
use App\Domain\Comment\Comment;
use App\Domain\Shared\DomainRuleViolation;
use DateTimeImmutable;

final readonly class AddCommentService
{
    public function __construct(
        private ProjectRepository $projects,
        private CommentRepository $comments,
    ) {}

    public function execute(
        string $commentId,
        string $projectId,
        string $taskId,
        string $authorId,
        string $body,
        DateTimeImmutable $createdAt,
    ): Comment {
        $project = $this->projects->getById($projectId);

        if ($project === null) {
            throw new DomainRuleViolation('Project not found.');
        }

        $comment = Comment::add(
            id: $commentId,
            project: $project,
            taskId: $taskId,
            authorId: $authorId,
            body: $body,
            createdAt: $createdAt,
        );

        $this->comments->save($comment);

        return $comment;
    }
}
