<?php

declare(strict_types=1);

namespace Tests\Support\Fakes;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectRepository;

final class InMemoryProjectRepository implements ProjectRepository
{
    /** @var array<int, Project> */
    private array $projects = [];

    private int $nextId = 1;

    public function findById(int $id): ?Project
    {
        return $this->projects[$id] ?? null;
    }

    public function save(Project $project): Project
    {
        $id = $project->id > 0 ? $project->id : $this->nextId++;
        $stored = new Project(
            id: $id,
            ownerId: $project->ownerId,
            name: $project->name,
            description: $project->description,
            createdAt: $project->createdAt,
            updatedAt: $project->updatedAt,
        );
        $this->projects[$id] = $stored;

        return $stored;
    }

    /** @return list<Project> */
    public function all(): array
    {
        return array_values($this->projects);
    }
}
