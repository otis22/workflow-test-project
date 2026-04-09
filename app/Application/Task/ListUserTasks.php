<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;

final readonly class ListUserTasks
{
    public function __construct(private TaskRepository $tasks) {}

    /**
     * Returns tasks assigned to the actor calling the use case.
     *
     * The single parameter is the actor's own identity. The use case
     * does not allow querying tasks of another user — actor authentication
     * is the responsibility of the session/controller layer.
     *
     * @return list<Task>
     */
    public function execute(int $actorId): array
    {
        return $this->tasks->listByAssignee($actorId);
    }
}
