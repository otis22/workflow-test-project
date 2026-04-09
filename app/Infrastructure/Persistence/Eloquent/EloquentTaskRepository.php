<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use DateTimeImmutable;
use RuntimeException;

final readonly class EloquentTaskRepository implements TaskRepository
{
    public function __construct(private TaskMapper $mapper) {}

    public function findById(int $id): ?Task
    {
        $model = TaskModel::query()->find($id);

        return $model instanceof TaskModel ? $this->mapper->toDomain($model) : null;
    }

    public function save(Task $task): Task
    {
        $model = $this->resolveModelForSave($task->id);

        $model->fill($this->mapper->toRow($task));
        $model->save();

        return $this->mapper->toDomain($model->refresh());
    }

    /** @return list<Task> */
    public function listByProject(int $projectId, ?Status $status = null, ?DateTimeImmutable $dueBefore = null): array
    {
        $query = TaskModel::query()
            ->where('project_id', $projectId);

        if ($status instanceof Status) {
            $query->where('status', $status->value);
        }

        if ($dueBefore instanceof DateTimeImmutable) {
            $query->whereNotNull('due_date')->where('due_date', '<', $dueBefore);
        }

        return $query
            ->orderBy('id')
            ->get()
            ->map(fn (TaskModel $model): Task => $this->mapper->toDomain($model))
            ->values()
            ->all();
    }

    /** @return list<Task> */
    public function listByAssignee(int $userId): array
    {
        return TaskModel::query()
            ->where('assignee_id', $userId)
            ->orderBy('id')
            ->get()
            ->map(fn (TaskModel $model): Task => $this->mapper->toDomain($model))
            ->values()
            ->all();
    }

    private function resolveModelForSave(int $id): TaskModel
    {
        if ($id <= 0) {
            return new TaskModel;
        }

        $existing = TaskModel::query()->find($id);
        if (! $existing instanceof TaskModel) {
            throw new RuntimeException(sprintf('Task with id %d not found for update', $id));
        }

        return $existing;
    }
}
