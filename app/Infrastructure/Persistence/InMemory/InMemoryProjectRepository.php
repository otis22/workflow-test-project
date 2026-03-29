<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Application\Contracts\ProjectRepository;
use App\Domain\Project\Project;

final class InMemoryProjectRepository implements ProjectRepository
{
    /**
     * @var array<string, Project>
     */
    private array $projects = [];

    #[\Override]
    public function save(Project $project): void
    {
        $this->projects[$project->id] = $project;
    }

    #[\Override]
    public function getById(string $projectId): ?Project
    {
        return $this->projects[$projectId] ?? null;
    }
}
