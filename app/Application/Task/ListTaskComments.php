<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\TaskNotFoundException;
use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Task\Comment;
use App\Domain\Task\CommentRepository;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;

final readonly class ListTaskComments
{
    public function __construct(
        private CommentRepository $comments,
        private TaskRepository $tasks,
        private ProjectMemberRepository $members,
    ) {}

    /** @return list<Comment> */
    public function execute(int $actorId, int $taskId): array
    {
        $task = $this->tasks->findById($taskId);
        if (! $task instanceof Task) {
            throw TaskNotFoundException::forId($taskId);
        }

        if (! $this->members->findByProjectAndUser($task->projectId, $actorId) instanceof ProjectMember) {
            throw NotAProjectMemberException::forUserAndProject($actorId, $task->projectId);
        }

        return $this->comments->listByTask($taskId);
    }
}
