<?php

declare(strict_types=1);

namespace Tests\Support\Fakes;

use App\Domain\Task\Comment;
use App\Domain\Task\CommentRepository;

final class InMemoryCommentRepository implements CommentRepository
{
    /** @var array<int, Comment> */
    private array $comments = [];

    private int $nextId = 1;

    public function save(Comment $comment): Comment
    {
        $id = $comment->id > 0 ? $comment->id : $this->nextId++;
        $stored = new Comment(
            id: $id,
            taskId: $comment->taskId,
            authorId: $comment->authorId,
            body: $comment->body,
            createdAt: $comment->createdAt,
        );
        $this->comments[$id] = $stored;

        return $stored;
    }

    /** @return list<Comment> */
    public function listByTask(int $taskId): array
    {
        $result = [];
        foreach ($this->comments as $comment) {
            if ($comment->taskId === $taskId) {
                $result[] = $comment;
            }
        }
        usort($result, fn (Comment $a, Comment $b): int => $a->id <=> $b->id);

        return $result;
    }
}
