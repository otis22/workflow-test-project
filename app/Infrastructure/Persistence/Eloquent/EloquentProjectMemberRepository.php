<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use RuntimeException;

final readonly class EloquentProjectMemberRepository implements ProjectMemberRepository
{
    public function __construct(private ProjectMemberMapper $mapper) {}

    public function findByProjectAndUser(int $projectId, int $userId): ?ProjectMember
    {
        $model = ProjectMemberModel::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();

        return $model instanceof ProjectMemberModel ? $this->mapper->toDomain($model) : null;
    }

    /** @return list<int> */
    public function projectIdsForUser(int $userId): array
    {
        return ProjectMemberModel::query()
            ->where('user_id', $userId)
            ->pluck('project_id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    public function save(ProjectMember $member): ProjectMember
    {
        $model = $this->resolveModelForSave($member->id);

        $model->fill($this->mapper->toRow($member));
        $model->save();

        return $this->mapper->toDomain($model->refresh());
    }

    private function resolveModelForSave(int $id): ProjectMemberModel
    {
        if ($id <= 0) {
            return new ProjectMemberModel;
        }

        $existing = ProjectMemberModel::query()->find($id);
        if (! $existing instanceof ProjectMemberModel) {
            throw new RuntimeException(sprintf('ProjectMember with id %d not found for update', $id));
        }

        return $existing;
    }
}
