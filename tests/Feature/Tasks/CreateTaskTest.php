<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_the_task_creation_form(): void
    {
        $project = Project::factory()->create();

        $this->get(route('projects.tasks.create', $project))
            ->assertRedirect(route('login'));
    }

    public function test_project_member_can_view_the_task_creation_form(): void
    {
        ['project' => $project, 'creator' => $creator, 'assignee' => $assignee] = $this->createTaskParticipantContext();

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.tasks.create', $project));

        $response
            ->assertOk()
            ->assertSee('Create a task')
            ->assertSee('Task title')
            ->assertSee($assignee->name);
    }

    public function test_non_member_cannot_open_the_task_creation_form(): void
    {
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $project = $this->createProjectForMember($member, 'Restricted delivery');

        $this->actingAs($outsider)
            ->get(route('projects.tasks.create', $project))
            ->assertForbidden();
    }

    public function test_project_member_can_create_a_task(): void
    {
        ['project' => $project, 'creator' => $creator, 'assignee' => $assignee] = $this->createTaskParticipantContext();

        $response = $this
            ->actingAs($creator)
            ->withSession(['_token' => 'task-token'])
            ->post(route('projects.tasks.store', $project), $this->makeTaskPayload([
                'assignee_id' => $assignee->id,
            ]));

        $response
            ->assertRedirect(route('projects.show', $project))
            ->assertSessionHas('status', 'Task "Prepare launch checklist" created.');

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'creator_id' => $creator->id,
            'assignee_id' => $assignee->id,
            'title' => 'Prepare launch checklist',
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        $task = Task::query()->where('title', 'Prepare launch checklist')->firstOrFail();

        $this->assertTaskDueDate($task, '2026-04-10');

        $this->actingAs($creator)
            ->get(route('projects.show', $project))
            ->assertSee('Prepare launch checklist')
            ->assertSee($assignee->name);
    }

    public function test_task_assignee_must_belong_to_the_project(): void
    {
        ['project' => $project, 'creator' => $creator] = $this->createTaskParticipantContext(false);
        $outsider = User::factory()->create();

        $response = $this
            ->actingAs($creator)
            ->from(route('projects.tasks.create', $project))
            ->withSession(['_token' => 'task-token'])
            ->post(route('projects.tasks.store', $project), $this->makeTaskPayload([
                'assignee_id' => $outsider->id,
            ]));

        $response
            ->assertRedirect(route('projects.tasks.create', $project))
            ->assertSessionHasErrors('assignee_id');

        $this->assertDatabaseCount('tasks', 0);
    }
}
