<?php

namespace Tests\Feature\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_can_view_the_task_edit_form(): void
    {
        ['task' => $task, 'creator' => $creator, 'assignee' => $assignee] = $this->createExistingProjectTask();

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.tasks.edit', [$task->project, $task]));

        $response
            ->assertOk()
            ->assertSee('Edit task')
            ->assertSee('Prepare launch checklist')
            ->assertSee($assignee->name);
    }

    public function test_non_member_cannot_view_the_task_edit_form(): void
    {
        ['task' => $task] = $this->createExistingProjectTask();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('projects.tasks.edit', [$task->project, $task]))
            ->assertForbidden();
    }

    public function test_project_member_can_update_a_task(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);
        $newAssignee = User::factory()->create([
            'name' => 'Grace Hopper',
        ]);

        $this->addProjectMembers($task->project, $newAssignee);

        $response = $this->updateTask($creator, $task, [
            'title' => 'Finalize launch checklist',
            'status' => Task::STATUS_IN_PROGRESS,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => '2026-04-12',
            'assignee_id' => $newAssignee->id,
        ]);

        $response
            ->assertRedirect(route('projects.show', $task->project))
            ->assertSessionHas('status', 'Task "Finalize launch checklist" updated.');

        $task->refresh();

        $this->assertSame('Finalize launch checklist', $task->title);
        $this->assertSame(Task::STATUS_IN_PROGRESS, $task->status);
        $this->assertSame(Task::PRIORITY_MEDIUM, $task->priority);
        $this->assertSame($newAssignee->id, $task->assignee_id);
        $this->assertTaskDueDate($task, '2026-04-12');
    }

    public function test_task_assignee_must_belong_to_the_project_when_updating(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);
        $outsider = User::factory()->create();

        $response = $this->updateTask($creator, $task, [
            'assignee_id' => $outsider->id,
        ]);

        $response
            ->assertRedirect(route('projects.tasks.edit', [$task->project, $task]))
            ->assertSessionHasErrors('assignee_id');
    }
}
