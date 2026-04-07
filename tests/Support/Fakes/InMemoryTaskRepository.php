<?php

declare(strict_types=1);

namespace Tests\Support\Fakes;

use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;
use DateTimeImmutable;

final class InMemoryTaskRepository implements TaskRepository
{
    /** @var array<int, Task> */
    private array $tasks = [];

    private int $nextId = 1;

    public function findById(int $id): ?Task
    {
        return $this->tasks[$id] ?? null;
    }

    public function save(Task $task): Task
    {
        $id = $task->id > 0 ? $task->id : $this->nextId++;
        $stored = new Task(
            id: $id,
            projectId: $task->projectId,
            creatorId: $task->creatorId,
            assigneeId: $task->assigneeId,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            priority: $task->priority,
            dueDate: $task->dueDate,
            createdAt: $task->createdAt,
            updatedAt: $task->updatedAt,
        );
        $this->tasks[$id] = $stored;

        return $stored;
    }

    /** @return list<Task> */
    public function listByProject(int $projectId, ?Status $status = null, ?DateTimeImmutable $dueBefore = null): array
    {
        $result = [];
        foreach ($this->tasks as $task) {
            if ($task->projectId !== $projectId) {
                continue;
            }
            if ($status instanceof Status && $task->status !== $status) {
                continue;
            }
            if ($dueBefore instanceof DateTimeImmutable) {
                if ($task->dueDate === null) {
                    continue;
                }
                if ($task->dueDate->value >= $dueBefore) {
                    continue;
                }
            }
            $result[] = $task;
        }
        usort($result, fn (Task $a, Task $b): int => $a->id <=> $b->id);

        return $result;
    }

    /** @return list<Task> */
    public function listByAssignee(int $userId): array
    {
        $result = [];
        foreach ($this->tasks as $task) {
            if ($task->assigneeId === $userId) {
                $result[] = $task;
            }
        }
        usort($result, fn (Task $a, Task $b): int => $a->id <=> $b->id);

        return $result;
    }
}
