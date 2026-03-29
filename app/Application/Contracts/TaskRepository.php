<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Task\Task;

interface TaskRepository
{
    public function save(Task $task): void;

    public function getById(string $taskId): ?Task;
}
