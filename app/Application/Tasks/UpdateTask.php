<?php

namespace App\Application\Tasks;

use App\Models\Task;
use App\Models\User;

class UpdateTask
{
    public function __construct(
        private readonly EnsureTaskParticipantsBelongToProject $ensureTaskParticipantsBelongToProject,
    ) {}

    public function __invoke(
        Task $task,
        User $actor,
        TaskData $data,
    ): Task {
        ($this->ensureTaskParticipantsBelongToProject)($task->project, $actor, $data->assignee);

        $task->update($data->toPersistenceAttributes());

        $task->refresh();

        /** @var Task $task */
        $task = $task->load(['project', 'creator', 'assignee']);

        return $task;
    }
}
