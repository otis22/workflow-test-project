<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Project\Project;
use DomainException;
use InvalidArgumentException;

final class Task
{
    public function __construct(
        public readonly int $id,
        public readonly int $projectId,
        public readonly int $creatorId,
        public readonly string $title,
        public readonly string $description,
        public TaskStatus $status,
        public TaskPriority $priority,
        public ?int $assigneeId,
        public ?\DateTimeImmutable $dueDate,
    ) {}

    public static function create(
        int $id,
        Project $project,
        int $creatorId,
        string $title,
        string $description = '',
        TaskPriority $priority = TaskPriority::Medium,
    ): self {
        if (trim($title) === '') {
            throw new InvalidArgumentException('Task title cannot be empty.');
        }

        if (! $project->isMember($creatorId)) {
            throw new DomainException('Task creator must be a project member.');
        }

        return new self(
            id: $id,
            projectId: $project->id,
            creatorId: $creatorId,
            title: $title,
            description: $description,
            status: TaskStatus::Todo,
            priority: $priority,
            assigneeId: null,
            dueDate: null,
        );
    }

    public function changeStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    public function changePriority(TaskPriority $priority): void
    {
        $this->priority = $priority;
    }

    public function assignTo(int $userId, Project $project): void
    {
        if (! $project->isMember($userId)) {
            throw new DomainException('Assignee must be a project member.');
        }

        $this->assigneeId = $userId;
    }

    public function setDueDate(\DateTimeImmutable $date): void
    {
        $this->dueDate = $date;
    }
}
