<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\Contracts\ProjectRepository;
use App\Application\Contracts\TaskRepository;
use App\Domain\Shared\DomainRuleViolation;
use App\Domain\Task\Task;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use DateTimeImmutable;

final readonly class CreateTaskService
{
    public function __construct(
        private ProjectRepository $projects,
        private TaskRepository $tasks,
    ) {}

    public function execute(
        string $taskId,
        string $projectId,
        string $creatorId,
        ?string $assigneeId,
        string $title,
        ?string $description,
        TaskStatus $status,
        TaskPriority $priority,
        ?DateTimeImmutable $dueDate = null,
    ): Task {
        $project = $this->projects->getById($projectId);

        if ($project === null) {
            throw new DomainRuleViolation('Project not found.');
        }

        $task = Task::create(
            id: $taskId,
            project: $project,
            creatorId: $creatorId,
            assigneeId: $assigneeId,
            title: $title,
            description: $description,
            status: $status,
            priority: $priority,
            dueDate: $dueDate,
        );

        $this->tasks->save($task);

        return $task;
    }
}
