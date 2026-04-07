<?php

declare(strict_types=1);

namespace App\Domain\Task;

interface CommentRepository
{
    public function save(Comment $comment): Comment;

    /** @return list<Comment> */
    public function listByTask(int $taskId): array;
}
