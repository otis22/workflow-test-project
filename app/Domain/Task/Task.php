<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Task
{
    public const array STATUSES = ['todo', 'in_progress', 'done'];

    public const array PRIORITIES = ['low', 'medium', 'high'];

    public function __construct(
        public int $id,
        public int $projectId,
        public int $creatorId,
        public ?int $assigneeId,
        public string $title,
        public string $description,
        public string $status,
        public string $priority,
        public ?DateTimeImmutable $dueDate,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
        if ($projectId <= 0) {
            throw new InvalidArgumentException('Task projectId must be positive');
        }

        if ($creatorId <= 0) {
            throw new InvalidArgumentException('Task creatorId must be positive');
        }

        if ($assigneeId !== null && $assigneeId <= 0) {
            throw new InvalidArgumentException('Task assigneeId must be positive when set');
        }

        if (trim($title) === '') {
            throw new InvalidArgumentException('Task title must not be empty');
        }

        if (! in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException(
                'Task status must be one of: '.implode(', ', self::STATUSES)
            );
        }

        if (! in_array($priority, self::PRIORITIES, true)) {
            throw new InvalidArgumentException(
                'Task priority must be one of: '.implode(', ', self::PRIORITIES)
            );
        }
    }

    public function withTitle(string $title, DateTimeImmutable $updatedAt): self
    {
        return $this->copyWith(['title' => $title, 'updatedAt' => $updatedAt]);
    }

    public function withDescription(string $description, DateTimeImmutable $updatedAt): self
    {
        return $this->copyWith(['description' => $description, 'updatedAt' => $updatedAt]);
    }

    public function withStatus(string $status, DateTimeImmutable $updatedAt): self
    {
        return $this->copyWith(['status' => $status, 'updatedAt' => $updatedAt]);
    }

    public function withPriority(string $priority, DateTimeImmutable $updatedAt): self
    {
        return $this->copyWith(['priority' => $priority, 'updatedAt' => $updatedAt]);
    }

    public function withAssignee(?int $assigneeId, DateTimeImmutable $updatedAt): self
    {
        return $this->copyWith(['assigneeId' => $assigneeId, 'updatedAt' => $updatedAt]);
    }

    public function withDueDate(?DateTimeImmutable $dueDate, DateTimeImmutable $updatedAt): self
    {
        return $this->copyWith(['dueDate' => $dueDate, 'updatedAt' => $updatedAt]);
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function copyWith(array $changes): self
    {
        return new self(
            id: $this->id,
            projectId: $this->projectId,
            creatorId: $this->creatorId,
            assigneeId: array_key_exists('assigneeId', $changes) ? $changes['assigneeId'] : $this->assigneeId,
            title: $changes['title'] ?? $this->title,
            description: $changes['description'] ?? $this->description,
            status: $changes['status'] ?? $this->status,
            priority: $changes['priority'] ?? $this->priority,
            dueDate: array_key_exists('dueDate', $changes) ? $changes['dueDate'] : $this->dueDate,
            createdAt: $this->createdAt,
            updatedAt: $changes['updatedAt'] ?? $this->updatedAt,
        );
    }
}
