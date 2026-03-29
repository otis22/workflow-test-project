<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_the_project_creation_form(): void
    {
        $response = $this->get(route('projects.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_the_project_creation_form(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('projects.create'));

        $response
            ->assertOk()
            ->assertSee('Create a project')
            ->assertSee('Project name');
    }

    public function test_authenticated_user_can_view_the_projects_index(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
        ]);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Platform refresh',
        ]);

        $project->memberLinks()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('projects.index'));

        $response
            ->assertOk()
            ->assertSee('Your projects')
            ->assertSee('Platform refresh')
            ->assertSee('Ada Lovelace');
    }

    public function test_authenticated_user_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['_token' => 'project-token'])
            ->post(route('projects.store'), [
                '_token' => 'project-token',
                'name' => 'Platform refresh',
                'description' => 'Delivery scope for the next release',
            ]);

        $response->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'owner_id' => $user->id,
            'name' => 'Platform refresh',
        ]);

        $this->assertDatabaseHas('project_members', [
            'user_id' => $user->id,
        ]);
    }
}
