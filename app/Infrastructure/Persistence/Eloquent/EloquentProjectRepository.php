<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectRepository;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use RuntimeException;

final readonly class EloquentProjectRepository implements ProjectRepository
{
    public function __construct(private ProjectMapper $mapper) {}

    public function findById(int $id): ?Project
    {
        $model = ProjectModel::query()->find($id);

        return $model instanceof ProjectModel ? $this->mapper->toDomain($model) : null;
    }

    public function save(Project $project): Project
    {
        $model = $this->resolveModelForSave($project->id);

        $model->fill($this->mapper->toRow($project));
        $model->save();

        return $this->mapper->toDomain($model->refresh());
    }

    private function resolveModelForSave(int $id): ProjectModel
    {
        if ($id <= 0) {
            return new ProjectModel;
        }

        $existing = ProjectModel::query()->find($id);
        if (! $existing instanceof ProjectModel) {
            throw new RuntimeException(sprintf('Project with id %d not found for update', $id));
        }

        return $existing;
    }
}
