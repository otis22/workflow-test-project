<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\Task\Exception\NotAProjectMemberException;
use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;
use DateTimeImmutable;

final readonly class ListProjectTasks
{
    public function __construct(
        private TaskRepository $tasks,
        private ProjectMemberRepository $members,
    ) {}

    /** @return list<Task> */
    public function execute(
        int $actorId,
        int $projectId,
        ?Status $status = null,
        ?DateTimeImmutable $dueBefore = null,
    ): array {
        if (! $this->members->findByProjectAndUser($projectId, $actorId) instanceof ProjectMember) {
            throw NotAProjectMemberException::forUserAndProject($actorId, $projectId);
        }

        return $this->tasks->listByProject($projectId, $status, $dueBefore);
    }
}
