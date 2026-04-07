<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Application\Clock\Clock;
use App\Application\Project\Exception\OwnerNotFoundException;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Project\ProjectRepository;
use App\Domain\User\User;
use App\Domain\User\UserRepository;

final readonly class CreateProject
{
    public function __construct(
        private ProjectRepository $projects,
        private ProjectMemberRepository $members,
        private UserRepository $users,
        private Clock $clock,
    ) {}

    public function execute(int $ownerId, string $name, string $description): Project
    {
        if (! $this->users->findById($ownerId) instanceof User) {
            throw OwnerNotFoundException::forId($ownerId);
        }

        $now = $this->clock->now();

        $project = $this->projects->save(new Project(
            id: 0,
            ownerId: $ownerId,
            name: $name,
            description: $description,
            createdAt: $now,
            updatedAt: $now,
        ));

        $this->members->save(new ProjectMember(
            id: 0,
            projectId: $project->id,
            userId: $ownerId,
            createdAt: $now,
        ));

        return $project;
    }
}
