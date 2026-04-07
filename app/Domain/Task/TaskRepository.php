<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;

interface TaskRepository
{
    public function findById(int $id): ?Task;

    public function save(Task $task): Task;

    /** @return list<Task> */
    public function listByProject(int $projectId, ?Status $status = null, ?DateTimeImmutable $dueBefore = null): array;

    /** @return list<Task> */
    public function listByAssignee(int $userId): array;
}
