<?php

declare(strict_types=1);

namespace App\Domain\Project;

interface ProjectMemberRepository
{
    public function findByProjectAndUser(int $projectId, int $userId): ?ProjectMember;

    /** @return list<int> */
    public function projectIdsForUser(int $userId): array;

    public function save(ProjectMember $member): ProjectMember;
}
