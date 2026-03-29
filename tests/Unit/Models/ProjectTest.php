<?php

namespace Tests\Unit\Models;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exposes_owner_and_member_relationships(): void
    {
        $owner = User::factory()->create();

        $project = Project::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Client portal',
            'description' => 'Project workspace',
        ]);

        $project->memberLinks()->create([
            'user_id' => $owner->id,
        ]);

        $this->assertTrue($project->owner->is($owner));
        $this->assertTrue($project->memberLinks->first()->user->is($owner));
        $this->assertTrue($project->members->first()->is($owner));
    }
}
