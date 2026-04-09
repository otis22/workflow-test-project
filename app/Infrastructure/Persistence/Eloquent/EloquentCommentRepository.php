<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Task\Comment;
use App\Domain\Task\CommentRepository;
use App\Infrastructure\Persistence\Eloquent\Model\CommentModel;
use RuntimeException;

final readonly class EloquentCommentRepository implements CommentRepository
{
    public function __construct(private CommentMapper $mapper) {}

    public function save(Comment $comment): Comment
    {
        $model = $this->resolveModelForSave($comment->id);

        $model->fill($this->mapper->toRow($comment));
        $model->save();

        return $this->mapper->toDomain($model->refresh());
    }

    /** @return list<Comment> */
    public function listByTask(int $taskId): array
    {
        return CommentModel::query()
            ->where('task_id', $taskId)
            ->orderBy('id')
            ->get()
            ->map(fn (CommentModel $model): Comment => $this->mapper->toDomain($model))
            ->values()
            ->all();
    }

    private function resolveModelForSave(int $id): CommentModel
    {
        if ($id <= 0) {
            return new CommentModel;
        }

        $existing = CommentModel::query()->find($id);
        if (! $existing instanceof CommentModel) {
            throw new RuntimeException(sprintf('Comment with id %d not found for update', $id));
        }

        return $existing;
    }
}
