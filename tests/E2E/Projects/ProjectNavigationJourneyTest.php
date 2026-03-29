<?php

namespace Tests\E2E\Projects;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectNavigationJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_navigate_from_login_to_project_workspace(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->loginUser(
            email: 'ada@example.com',
            password: 'password123',
        );

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $projectsResponse = $this->get(route('projects.index'));

        $projectsResponse
            ->assertOk()
            ->assertSee('Your projects')
            ->assertSee('New project');

        $createResponse = $this->createProject(
            name: 'Platform refresh',
            description: 'Delivery scope for the next release',
        );

        $createResponse->assertRedirect(route('projects.index'));

        $listAfterCreate = $this->get(route('projects.index'));

        $listAfterCreate
            ->assertOk()
            ->assertSee('Platform refresh')
            ->assertSee('Open project');

        /** @var int $projectId */
        $projectId = $user->projects()->value('projects.id');

        $showResponse = $this->get(route('projects.show', $projectId));

        $showResponse
            ->assertOk()
            ->assertSee('Platform refresh')
            ->assertSee('Project workspace');
    }
}
