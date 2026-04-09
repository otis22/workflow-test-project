<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Application\Project\Exception\ProjectNotFoundException;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Project\ProjectRepository;

final readonly class ShowProject
{
    public function __construct(
        private ProjectRepository $projects,
        private ProjectMemberRepository $members,
    ) {}

    public function execute(int $actorId, int $projectId): Project
    {
        $project = $this->projects->findById($projectId);
        if (! $project instanceof Project) {
            throw ProjectNotFoundException::forId($projectId);
        }

        if (! $this->members->findByProjectAndUser($projectId, $actorId) instanceof ProjectMember) {
            throw NotAProjectMemberException::forUserAndProject($actorId, $projectId);
        }

        return $project;
    }
}
