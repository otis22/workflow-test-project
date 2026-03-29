<?php

namespace Tests\Unit\Application\Projects;

use App\Application\Projects\CreateProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_project_and_adds_the_owner_as_a_member(): void
    {
        $owner = User::factory()->create();

        $project = app(CreateProject::class)(
            owner: $owner,
            name: 'Launch TaskFlow MVP',
            description: 'Initial roadmap delivery project',
        );

        $this->assertSame('Launch TaskFlow MVP', $project->name);
        $this->assertSame('Initial roadmap delivery project', $project->description);
        $this->assertTrue($project->owner->is($owner));
        $this->assertCount(1, $project->members);
        $this->assertTrue($project->members->first()->is($owner));

        $this->assertDatabaseHas('projects', [
            'owner_id' => $owner->id,
            'name' => 'Launch TaskFlow MVP',
        ]);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
        ]);
    }
}
