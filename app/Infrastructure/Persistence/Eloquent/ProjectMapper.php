<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Project\Project;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use DateTimeImmutable;

final class ProjectMapper
{
    public function toDomain(ProjectModel $model): Project
    {
        return new Project(
            id: (int) $model->id,
            ownerId: (int) $model->owner_id,
            name: $model->name,
            description: $model->description,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(Project $project): array
    {
        return [
            'owner_id' => $project->ownerId,
            'name' => $project->name,
            'description' => $project->description,
            'created_at' => $project->createdAt,
            'updated_at' => $project->updatedAt,
        ];
    }
}
