<?php

namespace App\Application\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class CreateTask
{
    public function __construct(
        private readonly EnsureTaskParticipantsBelongToProject $ensureTaskParticipantsBelongToProject,
    ) {}

    public function __invoke(
        Project $project,
        User $creator,
        TaskData $data,
    ): Task {
        ($this->ensureTaskParticipantsBelongToProject)($project, $creator, $data->assignee);

        /** @var Task $task */
        $task = $project->tasks()->create([
            'creator_id' => $creator->id,
            ...$data->toPersistenceAttributes(),
        ]);

        return $task;
    }
}
