<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Models\Project;
use App\Models\Task;
use DomainException;

final class CreateTask
{
    public function execute(
        int $projectId,
        int $creatorId,
        string $title,
        string $description = '',
        string $priority = 'medium',
    ): Task {
        $project = Project::findOrFail($projectId);

        if (! $project->members()->where('user_id', $creatorId)->exists()) {
            throw new DomainException('Creator must be a project member.');
        }

        return Task::create([
            'project_id' => $projectId,
            'creator_id' => $creatorId,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
        ]);
    }
}
