<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Project\Project;
use App\Domain\Shared\DomainRuleViolation;
use DateTimeImmutable;

final class Task
{
    private function __construct(
        public readonly string $id,
        public readonly string $projectId,
        public readonly string $creatorId,
        public ?string $assigneeId,
        public string $title,
        public ?string $description,
        public TaskStatus $status,
        public TaskPriority $priority,
        public ?DateTimeImmutable $dueDate,
    ) {}

    public static function create(
        string $id,
        Project $project,
        string $creatorId,
        ?string $assigneeId,
        string $title,
        ?string $description,
        TaskStatus $status,
        TaskPriority $priority,
        ?DateTimeImmutable $dueDate = null,
    ): self {
        if (trim($id) === '') {
            throw new DomainRuleViolation('Task id is required.');
        }

        if (! $project->hasMember($creatorId)) {
            throw new DomainRuleViolation('Task creator must be a project member.');
        }

        if ($assigneeId !== null && ! $project->hasMember($assigneeId)) {
            throw new DomainRuleViolation('Task assignee must be a project member.');
        }

        if (trim($title) === '') {
            throw new DomainRuleViolation('Task title is required.');
        }

        return new self(
            id: $id,
            projectId: $project->id,
            creatorId: $creatorId,
            assigneeId: $assigneeId,
            title: trim($title),
            description: $description !== null ? trim($description) : null,
            status: $status,
            priority: $priority,
            dueDate: $dueDate,
        );
    }

    public function changeStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    public function reassign(Project $project, ?string $assigneeId): void
    {
        if ($assigneeId !== null && ! $project->hasMember($assigneeId)) {
            throw new DomainRuleViolation('Task assignee must be a project member.');
        }

        $this->assigneeId = $assigneeId;
    }
}
