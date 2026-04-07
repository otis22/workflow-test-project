<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\Comment;
use App\Domain\Task\CommentRepository;

final readonly class ListTaskComments
{
    public function __construct(private CommentRepository $comments) {}

    /** @return list<Comment> */
    public function execute(int $taskId): array
    {
        return $this->comments->listByTask($taskId);
    }
}
