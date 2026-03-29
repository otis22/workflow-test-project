<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Application\Contracts\ProjectRepository;
use App\Domain\Project\Project;

final readonly class CreateProjectService
{
    public function __construct(
        private ProjectRepository $projects,
    ) {}

    public function execute(
        string $projectId,
        string $ownerId,
        string $name,
        ?string $description = null,
    ): Project {
        $project = Project::create(
            id: $projectId,
            ownerId: $ownerId,
            name: $name,
            description: $description,
        );

        $this->projects->save($project);

        return $project;
    }
}
