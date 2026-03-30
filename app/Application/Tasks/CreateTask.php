<?php

namespace App\Application\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;

class CreateTask
{
    public function __construct(
        private readonly EnsureTaskParticipantsBelongToProject $ensureTaskParticipantsBelongToProject,
    ) {}

    public function __invoke(
        Project $project,
        User $creator,
        string $title,
        ?string $description,
        string $status,
        string $priority,
        ?string $dueDate,
        ?User $assignee = null,
    ): Task {
        ($this->ensureTaskParticipantsBelongToProject)($project, $creator, $assignee);

        /** @var Task $task */
        $task = $project->tasks()->create([
            'creator_id' => $creator->id,
            'assignee_id' => $assignee?->id,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate === null ? null : CarbonImmutable::parse($dueDate)->toDateString(),
        ]);

        $task->refresh();

        /** @var Task $task */
        $task = $task->load(['project', 'creator', 'assignee']);

        return $task;
    }
}
