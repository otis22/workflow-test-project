<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Application\Contracts\TaskRepository;
use App\Domain\Task\Task;

final class InMemoryTaskRepository implements TaskRepository
{
    /**
     * @var array<string, Task>
     */
    private array $tasks = [];

    #[\Override]
    public function save(Task $task): void
    {
        $this->tasks[$task->id] = $task;
    }

    #[\Override]
    public function getById(string $taskId): ?Task
    {
        return $this->tasks[$taskId] ?? null;
    }
}
