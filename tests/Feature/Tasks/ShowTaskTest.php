<?php

namespace Tests\Feature\Tasks;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_can_open_the_task_detail_page(): void
    {
        ['task' => $task, 'creator' => $creator, 'assignee' => $assignee] = $this->createExistingProjectTask();

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.tasks.show', [$task->project, $task]));

        $response
            ->assertOk()
            ->assertSee('Task details')
            ->assertSee($task->title)
            ->assertSee($task->description)
            ->assertSee($task->project->name)
            ->assertSee($creator->name)
            ->assertSee($assignee->name)
            ->assertSee('Comments')
            ->assertSee(route('projects.tasks.edit', [$task->project, $task]))
            ->assertSee(route('projects.show', $task->project));
    }

    public function test_non_member_cannot_open_the_task_detail_page(): void
    {
        ['task' => $task] = $this->createExistingProjectTask();

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('projects.tasks.show', [$task->project, $task]));

        $response->assertForbidden();
    }

    public function test_project_workspace_links_tasks_to_the_detail_page(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.show', $task->project));

        $response
            ->assertOk()
            ->assertSee(route('projects.tasks.show', [$task->project, $task]))
            ->assertSee('View task');
    }
}
