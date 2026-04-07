<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;

final readonly class ListUserTasks
{
    public function __construct(private TaskRepository $tasks) {}

    /** @return list<Task> */
    public function execute(int $userId): array
    {
        return $this->tasks->listByAssignee($userId);
    }
}
