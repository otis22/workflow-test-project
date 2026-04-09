<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Project\ProjectMember;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use DateTimeImmutable;

final class ProjectMemberMapper
{
    public function toDomain(ProjectMemberModel $model): ProjectMember
    {
        return new ProjectMember(
            id: (int) $model->id,
            projectId: (int) $model->project_id,
            userId: (int) $model->user_id,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(ProjectMember $member): array
    {
        return [
            'project_id' => $member->projectId,
            'user_id' => $member->userId,
            'created_at' => $member->createdAt,
        ];
    }
}
