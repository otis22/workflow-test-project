<?php

namespace App\Application\Comments;

use App\Application\Comments\Exceptions\InvalidCommentAuthor;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class AddComment
{
    public function __invoke(Task $task, User $author, string $body): Comment
    {
        if (! $task->project->hasMember($author)) {
            throw InvalidCommentAuthor::mustBelongToProject();
        }

        return Comment::query()->create([
            'task_id' => $task->id,
            'author_id' => $author->id,
            'body' => $body,
        ]);
    }
}
