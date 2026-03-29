<?php

declare(strict_types=1);

namespace App\Domain\Project;

use App\Domain\Shared\DomainRuleViolation;

final class Project
{
    /**
     * @var array<string, ProjectMember>
     */
    private array $members = [];

    private function __construct(
        public readonly string $id,
        public readonly string $ownerId,
        public string $name,
        public ?string $description,
    ) {}

    public static function create(
        string $id,
        string $ownerId,
        string $name,
        ?string $description = null,
    ): self {
        if (trim($id) === '') {
            throw new DomainRuleViolation('Project id is required.');
        }

        if (trim($ownerId) === '') {
            throw new DomainRuleViolation('Project owner is required.');
        }

        if (trim($name) === '') {
            throw new DomainRuleViolation('Project name is required.');
        }

        $project = new self(
            id: $id,
            ownerId: $ownerId,
            name: trim($name),
            description: $description !== null ? trim($description) : null,
        );

        $project->addMember($ownerId);

        return $project;
    }

    public function addMember(string $userId): void
    {
        if (trim($userId) === '') {
            throw new DomainRuleViolation('Project member id is required.');
        }

        $this->members[$userId] = new ProjectMember(
            projectId: $this->id,
            userId: $userId,
        );
    }

    public function hasMember(string $userId): bool
    {
        return isset($this->members[$userId]);
    }

    /**
     * @return list<ProjectMember>
     */
    public function members(): array
    {
        return array_values($this->members);
    }
}
