<?php

namespace Tests\Feature\Dashboard;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardShellTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login_when_opening_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_the_dashboard_shell(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Projects')
            ->assertSee('My work')
            ->assertSee('Upcoming deadlines')
            ->assertSee('Ada Lovelace')
            ->assertSee('ada@example.com');
    }

    public function test_dashboard_shows_the_current_users_assigned_work_and_project_links(): void
    {
        Carbon::setTestNow(CarbonImmutable::parse('2026-03-30 09:00:00'));

        $user = User::factory()->create();
        $projectAlpha = $this->createProjectForMember($user, 'Alpha Launch');
        $projectBeta = $this->createProjectForMember($user, 'Beta Rollout');
        $otherProject = $this->createProjectForMember(User::factory()->create(), 'Hidden Workspace');

        $this->createProjectTask($projectAlpha, $projectAlpha->owner, [
            'title' => 'Prepare launch checklist',
            'assignee_id' => $user,
            'status' => Task::STATUS_TODO,
            'due_date' => '2026-04-02',
        ]);

        $this->createProjectTask($projectBeta, $projectBeta->owner, [
            'title' => 'Confirm rollout owners',
            'assignee_id' => $user,
            'status' => Task::STATUS_IN_PROGRESS,
            'due_date' => null,
        ]);

        $this->createProjectTask($projectAlpha, $projectAlpha->owner, [
            'title' => 'Archived launch note',
            'assignee_id' => $user,
            'status' => Task::STATUS_DONE,
            'due_date' => '2026-03-31',
        ]);

        $this->createProjectTask($projectAlpha, $projectAlpha->owner, [
            'title' => 'Unassigned audit task',
            'assignee_id' => null,
            'status' => Task::STATUS_TODO,
            'due_date' => '2026-04-01',
        ]);

        $this->createProjectTask($otherProject, $otherProject->owner, [
            'title' => 'Hidden foreign task',
            'assignee_id' => null,
            'status' => Task::STATUS_TODO,
            'due_date' => '2026-04-01',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Prepare launch checklist')
            ->assertSee('Confirm rollout owners')
            ->assertSee('Alpha Launch')
            ->assertSee('Beta Rollout')
            ->assertDontSee('Archived launch note')
            ->assertDontSee('Unassigned audit task')
            ->assertDontSee('Hidden foreign task')
            ->assertDontSee('Hidden Workspace');
    }

    public function test_dashboard_shows_only_near_term_deadlines_ordered_by_due_date(): void
    {
        Carbon::setTestNow(CarbonImmutable::parse('2026-03-30 09:00:00'));

        $user = User::factory()->create();
        $project = $this->createProjectForMember($user, 'Launch Program');

        $this->createProjectTask($project, $project->owner, [
            'title' => 'Fix release blocker',
            'assignee_id' => $user,
            'status' => Task::STATUS_IN_PROGRESS,
            'due_date' => '2026-03-30',
        ]);

        $this->createProjectTask($project, $project->owner, [
            'title' => 'Confirm launch date',
            'assignee_id' => $user,
            'status' => Task::STATUS_TODO,
            'due_date' => '2026-04-01',
        ]);

        $this->createProjectTask($project, $project->owner, [
            'title' => 'Late backlog cleanup',
            'assignee_id' => null,
            'status' => Task::STATUS_TODO,
            'due_date' => '2026-04-15',
        ]);

        $this->createProjectTask($project, $project->owner, [
            'title' => 'Closed retrospective',
            'assignee_id' => $user,
            'status' => Task::STATUS_DONE,
            'due_date' => '2026-03-31',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSeeInOrder([
                'Fix release blocker',
                'Confirm launch date',
            ])
            ->assertDontSee('Late backlog cleanup')
            ->assertDontSee('Closed retrospective');
    }
}
