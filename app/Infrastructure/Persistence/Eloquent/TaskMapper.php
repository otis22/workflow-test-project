<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use DateTimeImmutable;

final class TaskMapper
{
    public function toDomain(TaskModel $model): Task
    {
        return new Task(
            id: (int) $model->id,
            projectId: (int) $model->project_id,
            creatorId: (int) $model->creator_id,
            assigneeId: $model->assignee_id !== null ? (int) $model->assignee_id : null,
            title: $model->title,
            description: $model->description,
            status: Status::from($model->status),
            priority: Priority::from($model->priority),
            dueDate: $model->due_date !== null
                ? new DueDate(DateTimeImmutable::createFromInterface($model->due_date))
                : null,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(Task $task): array
    {
        return [
            'project_id' => $task->projectId,
            'creator_id' => $task->creatorId,
            'assignee_id' => $task->assigneeId,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'priority' => $task->priority->value,
            'due_date' => $task->dueDate?->value,
            'created_at' => $task->createdAt,
            'updated_at' => $task->updatedAt,
        ];
    }
}
