<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Application\Contracts\CommentRepository;
use App\Domain\Comment\Comment;

final class InMemoryCommentRepository implements CommentRepository
{
    /**
     * @var array<string, Comment>
     */
    private array $comments = [];

    #[\Override]
    public function save(Comment $comment): void
    {
        $this->comments[$comment->id] = $comment;
    }

    /**
     * @return list<Comment>
     */
    public function all(): array
    {
        return array_values($this->comments);
    }
}
