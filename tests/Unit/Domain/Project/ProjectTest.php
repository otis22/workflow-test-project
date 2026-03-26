<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use App\Domain\Project\Project;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProjectTest extends TestCase
{
    #[Test]
    public function it_creates_project_with_valid_data(): void
    {
        $project = new Project(
            id: 1,
            ownerId: 10,
            name: 'My Project',
            description: 'A test project',
        );

        $this->assertSame(1, $project->id);
        $this->assertSame(10, $project->ownerId);
        $this->assertSame('My Project', $project->name);
        $this->assertSame('A test project', $project->description);
    }

    #[Test]
    public function it_rejects_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Project(id: 1, ownerId: 10, name: '', description: '');
    }

    #[Test]
    public function it_allows_empty_description(): void
    {
        $project = new Project(id: 1, ownerId: 10, name: 'Test', description: '');

        $this->assertSame('', $project->description);
    }

    #[Test]
    public function owner_is_member(): void
    {
        $project = new Project(id: 1, ownerId: 10, name: 'Test', description: '');

        $this->assertTrue($project->isMember(10));
    }

    #[Test]
    public function non_member_is_not_member(): void
    {
        $project = new Project(id: 1, ownerId: 10, name: 'Test', description: '');

        $this->assertFalse($project->isMember(99));
    }

    #[Test]
    public function it_adds_member(): void
    {
        $project = new Project(id: 1, ownerId: 10, name: 'Test', description: '');
        $project->addMember(20);

        $this->assertTrue($project->isMember(20));
    }

    #[Test]
    public function it_does_not_duplicate_member(): void
    {
        $project = new Project(id: 1, ownerId: 10, name: 'Test', description: '');
        $project->addMember(10);

        $this->assertTrue($project->isMember(10));
    }
}
