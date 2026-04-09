<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Task\Comment;
use App\Infrastructure\Persistence\Eloquent\Model\CommentModel;
use DateTimeImmutable;

final class CommentMapper
{
    public function toDomain(CommentModel $model): Comment
    {
        return new Comment(
            id: (int) $model->id,
            taskId: (int) $model->task_id,
            authorId: (int) $model->author_id,
            body: $model->body,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(Comment $comment): array
    {
        return [
            'task_id' => $comment->taskId,
            'author_id' => $comment->authorId,
            'body' => $comment->body,
            'created_at' => $comment->createdAt,
        ];
    }
}
