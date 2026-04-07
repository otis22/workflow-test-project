<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\Clock\Clock;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\TaskNotFoundException;
use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Task\Comment;
use App\Domain\Task\CommentRepository;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;

final readonly class AddComment
{
    public function __construct(
        private CommentRepository $comments,
        private TaskRepository $tasks,
        private ProjectMemberRepository $members,
        private Clock $clock,
    ) {}

    public function execute(int $taskId, int $authorId, string $body): Comment
    {
        $task = $this->tasks->findById($taskId);
        if (! $task instanceof Task) {
            throw TaskNotFoundException::forId($taskId);
        }

        if (! $this->members->findByProjectAndUser($task->projectId, $authorId) instanceof ProjectMember) {
            throw NotAProjectMemberException::forUserAndProject($authorId, $task->projectId);
        }

        return $this->comments->save(new Comment(
            id: 0,
            taskId: $taskId,
            authorId: $authorId,
            body: $body,
            createdAt: $this->clock->now(),
        ));
    }
}
