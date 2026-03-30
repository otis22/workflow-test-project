<?php

namespace Tests\Feature\Projects;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTaskListTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_workspace_shows_task_filters(): void
    {
        ['project' => $project, 'creator' => $creator] = $this->createTaskParticipantContext(false);

        $this->createProjectTask($project, $creator, [
            'title' => 'Prepare launch checklist',
        ]);

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.show', $project));

        $response
            ->assertOk()
            ->assertSee('Filter tasks')
            ->assertSee('All statuses')
            ->assertSee('All deadlines')
            ->assertSee('Prepare launch checklist');
    }

    public function test_project_member_can_filter_tasks_by_status(): void
    {
        ['project' => $project, 'creator' => $creator] = $this->createTaskParticipantContext(false);

        $this->createProjectTask($project, $creator, [
            'title' => 'Prepare launch checklist',
            'status' => Task::STATUS_TODO,
        ]);

        $this->createProjectTask($project, $creator, [
            'title' => 'Confirm rollout schedule',
            'status' => Task::STATUS_DONE,
        ]);

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.show', [
                'project' => $project,
                'status' => Task::STATUS_DONE,
            ]));

        $response
            ->assertOk()
            ->assertSee('Confirm rollout schedule')
            ->assertDontSee('Prepare launch checklist');
    }

    public function test_project_member_can_filter_tasks_by_deadline_state(): void
    {
        ['project' => $project, 'creator' => $creator] = $this->createTaskParticipantContext(false);

        $this->createProjectTask($project, $creator, [
            'title' => 'Overdue dependency audit',
            'due_date' => '2026-03-29',
        ]);

        $this->createProjectTask($project, $creator, [
            'title' => 'Upcoming release checklist',
            'due_date' => '2026-04-03',
        ]);

        $this->createProjectTask($project, $creator, [
            'title' => 'Backlog triage',
            'due_date' => null,
        ]);

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.show', [
                'project' => $project,
                'deadline' => 'overdue',
            ]));

        $response
            ->assertOk()
            ->assertSee('Overdue dependency audit')
            ->assertDontSee('Upcoming release checklist')
            ->assertDontSee('Backlog triage');
    }
}
