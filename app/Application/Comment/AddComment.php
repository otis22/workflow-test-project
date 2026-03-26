<?php

declare(strict_types=1);

namespace App\Application\Comment;

use App\Models\Comment;
use App\Models\Project;
use DomainException;

final class AddComment
{
    public function execute(int $taskId, int $authorId, string $body, Project $project): Comment
    {
        if (! $project->members()->where('user_id', $authorId)->exists()) {
            throw new DomainException('Only project members can comment.');
        }

        return Comment::create([
            'task_id' => $taskId,
            'author_id' => $authorId,
            'body' => $body,
        ]);
    }
}
