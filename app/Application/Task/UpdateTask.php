<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\Clock\Clock;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\TaskNotFoundException;
use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;
use DateTimeImmutable;

final readonly class UpdateTask
{
    public function __construct(
        private TaskRepository $tasks,
        private ProjectMemberRepository $members,
        private Clock $clock,
    ) {}

    public function execute(
        int $taskId,
        ?string $title = null,
        ?string $description = null,
        ?Status $status = null,
        ?Priority $priority = null,
        bool $changeDueDate = false,
        ?DueDate $dueDate = null,
        bool $changeAssignee = false,
        ?int $assigneeId = null,
    ): Task {
        $task = $this->tasks->findById($taskId);
        if (! $task instanceof Task) {
            throw TaskNotFoundException::forId($taskId);
        }

        if ($changeAssignee) {
            $this->ensureAssigneeIsMember($task->projectId, $assigneeId);
        }

        $now = $this->clock->now();
        $task = $this->applyScalarChanges($task, $now, $title, $description, $status, $priority);

        if ($changeDueDate) {
            $task = $task->withDueDate($dueDate, $now);
        }

        if ($changeAssignee) {
            $task = $task->withAssignee($assigneeId, $now);
        }

        return $this->tasks->save($task);
    }

    private function ensureAssigneeIsMember(int $projectId, ?int $assigneeId): void
    {
        if ($assigneeId === null) {
            return;
        }

        if (! $this->members->findByProjectAndUser($projectId, $assigneeId) instanceof ProjectMember) {
            throw NotAProjectMemberException::forUserAndProject($assigneeId, $projectId);
        }
    }

    private function applyScalarChanges(
        Task $task,
        DateTimeImmutable $now,
        ?string $title,
        ?string $description,
        ?Status $status,
        ?Priority $priority,
    ): Task {
        if ($title !== null) {
            $task = $task->withTitle($title, $now);
        }
        if ($description !== null) {
            $task = $task->withDescription($description, $now);
        }
        if ($status instanceof Status) {
            $task = $task->withStatus($status, $now);
        }
        if ($priority instanceof Priority) {
            $task = $task->withPriority($priority, $now);
        }

        return $task;
    }
}
