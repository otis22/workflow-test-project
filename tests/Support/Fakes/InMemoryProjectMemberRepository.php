<?php

declare(strict_types=1);

namespace Tests\Support\Fakes;

use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;

final class InMemoryProjectMemberRepository implements ProjectMemberRepository
{
    /** @var array<int, ProjectMember> */
    private array $members = [];

    private int $nextId = 1;

    public function findByProjectAndUser(int $projectId, int $userId): ?ProjectMember
    {
        foreach ($this->members as $member) {
            if ($member->projectId === $projectId && $member->userId === $userId) {
                return $member;
            }
        }

        return null;
    }

    /** @return list<int> */
    public function projectIdsForUser(int $userId): array
    {
        $ids = [];
        foreach ($this->members as $member) {
            if ($member->userId === $userId) {
                $ids[] = $member->projectId;
            }
        }

        return $ids;
    }

    public function save(ProjectMember $member): ProjectMember
    {
        $id = $member->id > 0 ? $member->id : $this->nextId++;
        $stored = new ProjectMember(
            id: $id,
            projectId: $member->projectId,
            userId: $member->userId,
            createdAt: $member->createdAt,
        );
        $this->members[$id] = $stored;

        return $stored;
    }
}
