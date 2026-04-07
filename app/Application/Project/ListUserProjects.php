<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Project\ProjectRepository;

final readonly class ListUserProjects
{
    public function __construct(
        private ProjectRepository $projects,
        private ProjectMemberRepository $members,
    ) {}

    /** @return list<Project> */
    public function execute(int $userId): array
    {
        $ids = $this->members->projectIdsForUser($userId);
        sort($ids);

        $result = [];
        foreach ($ids as $projectId) {
            $project = $this->projects->findById($projectId);
            if ($project instanceof Project) {
                $result[] = $project;
            }
        }

        return $result;
    }
}
