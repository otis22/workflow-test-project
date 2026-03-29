<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Project\Project;
use App\Domain\Shared\DomainRuleViolation;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function test_project_requires_owner(): void
    {
        $this->expectException(DomainRuleViolation::class);

        Project::create(
            id: 'project-1',
            ownerId: '',
            name: 'Roadmap',
        );
    }

    public function test_project_owner_is_added_as_member_automatically(): void
    {
        $project = Project::create(
            id: 'project-1',
            ownerId: 'user-1',
            name: 'Roadmap',
        );

        $this->assertTrue($project->hasMember('user-1'));
        $this->assertCount(1, $project->members());
    }

    public function test_project_can_add_members(): void
    {
        $project = Project::create(
            id: 'project-1',
            ownerId: 'user-1',
            name: 'Roadmap',
        );

        $project->addMember('user-2');

        $this->assertTrue($project->hasMember('user-2'));
        $this->assertCount(2, $project->members());
    }
}
