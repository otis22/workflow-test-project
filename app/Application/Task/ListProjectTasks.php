<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;
use DateTimeImmutable;

final readonly class ListProjectTasks
{
    public function __construct(private TaskRepository $tasks) {}

    /** @return list<Task> */
    public function execute(
        int $projectId,
        ?Status $status = null,
        ?DateTimeImmutable $dueBefore = null,
    ): array {
        return $this->tasks->listByProject($projectId, $status, $dueBefore);
    }
}
