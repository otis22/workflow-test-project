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

        $this->createProjectForMember($user, 'Platform refresh');

        $response = $this
            ->actingAs($user)
            ->get(route('projects.index'));

        $response
            ->assertOk()
            ->assertSee('Your projects')
            ->assertSee('Platform refresh')
            ->assertSee('Ada Lovelace');
    }

    public function test_projects_index_only_shows_projects_available_to_the_current_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
        ]);
        $teammate = User::factory()->create([
            'name' => 'Grace Hopper',
        ]);
        $outsider = User::factory()->create();

        $sharedProject = Project::factory()->create([
            'owner_id' => $teammate->id,
            'name' => 'Shared roadmap',
        ]);
        $sharedProject->memberLinks()->createMany([
            ['user_id' => $teammate->id],
            ['user_id' => $user->id],
        ]);

        $privateProject = Project::factory()->create([
            'owner_id' => $outsider->id,
            'name' => 'Hidden migration',
        ]);
        $privateProject->memberLinks()->create([
            'user_id' => $outsider->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('projects.index'));

        $response
            ->assertOk()
            ->assertSee('Shared roadmap')
            ->assertDontSee('Hidden migration');
    }

    public function test_project_member_can_open_a_project_from_the_list(): void
    {
        $user = User::factory()->create();
        $project = $this->createProjectForMember($user, 'Platform refresh');

        $response = $this
            ->actingAs($user)
            ->get(route('projects.show', $project));

        $response
            ->assertOk()
            ->assertSee('Platform refresh')
            ->assertSee('Project workspace');
    }

    public function test_non_member_cannot_open_another_users_project(): void
    {
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $project = $this->createProjectForMember($member, 'Restricted project');

        $this->actingAs($outsider)
            ->get(route('projects.show', $project))
            ->assertForbidden();
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

    private function createProjectForMember(User $user, string $name): Project
    {
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'name' => $name,
        ]);

        $project->memberLinks()->create([
            'user_id' => $user->id,
        ]);

        return $project;
    }
}
