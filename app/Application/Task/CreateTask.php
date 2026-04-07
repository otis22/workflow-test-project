<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\Clock\Clock;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\ProjectNotFoundException;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Project\ProjectRepository;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;

final readonly class CreateTask
{
    public function __construct(
        private TaskRepository $tasks,
        private ProjectRepository $projects,
        private ProjectMemberRepository $members,
        private Clock $clock,
    ) {}

    public function execute(
        int $projectId,
        int $creatorId,
        string $title,
        string $description,
        Status $status,
        Priority $priority,
        ?int $assigneeId,
        ?DueDate $dueDate,
    ): Task {
        if (! $this->projects->findById($projectId) instanceof Project) {
            throw ProjectNotFoundException::forId($projectId);
        }

        if (! $this->members->findByProjectAndUser($projectId, $creatorId) instanceof ProjectMember) {
            throw NotAProjectMemberException::forUserAndProject($creatorId, $projectId);
        }

        if ($assigneeId !== null
            && ! $this->members->findByProjectAndUser($projectId, $assigneeId) instanceof ProjectMember) {
            throw NotAProjectMemberException::forUserAndProject($assigneeId, $projectId);
        }

        $now = $this->clock->now();

        return $this->tasks->save(new Task(
            id: 0,
            projectId: $projectId,
            creatorId: $creatorId,
            assigneeId: $assigneeId,
            title: $title,
            description: $description,
            status: $status,
            priority: $priority,
            dueDate: $dueDate,
            createdAt: $now,
            updatedAt: $now,
        ));
    }
}
