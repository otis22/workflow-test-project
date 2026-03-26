<?php

declare(strict_types=1);

namespace Tests\Feature\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProjectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_see_projects(): void
    {
        $this->get('/projects')->assertRedirect('/login');
    }

    #[Test]
    public function user_can_see_project_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/projects')->assertStatus(200);
    }

    #[Test]
    public function user_can_create_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'name' => 'My Project',
            'description' => 'A test project',
        ]);

        $response->assertRedirect('/projects');
        $this->assertDatabaseHas('projects', ['name' => 'My Project', 'owner_id' => $user->id]);
        $this->assertDatabaseHas('project_members', ['project_id' => 1, 'user_id' => $user->id]);
    }

    #[Test]
    public function project_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function user_sees_only_their_projects(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1)->post('/projects', ['name' => 'User1 Project']);
        $this->actingAs($user2)->post('/projects', ['name' => 'User2 Project']);

        $response = $this->actingAs($user1)->get('/projects');
        $response->assertSee('User1 Project');
        $response->assertDontSee('User2 Project');
    }

    #[Test]
    public function user_can_view_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->members()->attach($user->id);

        $this->actingAs($user)->get("/projects/{$project->id}")->assertStatus(200)->assertSee($project->name);
    }
}
