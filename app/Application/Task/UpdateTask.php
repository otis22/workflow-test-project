<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Models\Project;
use App\Models\Task;
use DomainException;

final class UpdateTask
{
    public function execute(Task $task, array $data): Task
    {
        if (isset($data['assignee_id'])) {
            $project = Project::findOrFail($task->project_id);
            if (! $project->members()->where('user_id', $data['assignee_id'])->exists()) {
                throw new DomainException('Assignee must be a project member.');
            }
        }

        $task->update($data);

        return $task->fresh();
    }
}
