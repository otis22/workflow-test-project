<?php

declare(strict_types=1);

namespace Tests\Feature\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TaskTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithMember(): array
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->members()->attach($user->id);

        return [$user, $project];
    }

    #[Test]
    public function member_can_create_task(): void
    {
        [$user, $project] = $this->createProjectWithMember();

        $response = $this->actingAs($user)->post(route('tasks.store', $project), [
            'title' => 'Fix login bug',
            'description' => 'The login form breaks',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('tasks', [
            'title' => 'Fix login bug',
            'project_id' => $project->id,
            'creator_id' => $user->id,
            'status' => 'todo',
            'priority' => 'high',
        ]);
    }

    #[Test]
    public function non_member_cannot_create_task(): void
    {
        [$owner, $project] = $this->createProjectWithMember();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->post(route('tasks.store', $project), ['title' => 'Hack', 'priority' => 'low'])
            ->assertStatus(403);
    }

    #[Test]
    public function task_requires_title(): void
    {
        [$user, $project] = $this->createProjectWithMember();

        $this->actingAs($user)
            ->post(route('tasks.store', $project), ['title' => '', 'priority' => 'medium'])
            ->assertSessionHasErrors('title');
    }

    #[Test]
    public function member_can_view_task(): void
    {
        [$user, $project] = $this->createProjectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('tasks.show', [$project, $task]))
            ->assertStatus(200)
            ->assertSee($task->title);
    }

    #[Test]
    public function member_can_update_task_status(): void
    {
        [$user, $project] = $this->createProjectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id]);

        $this->actingAs($user)->put(route('tasks.update', [$project, $task]), [
            'title' => $task->title,
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress']);
    }

    #[Test]
    public function member_can_assign_task_to_another_member(): void
    {
        [$user, $project] = $this->createProjectWithMember();
        $member2 = User::factory()->create();
        $project->members()->attach($member2->id);
        $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id]);

        $this->actingAs($user)->put(route('tasks.update', [$project, $task]), [
            'title' => $task->title,
            'status' => 'todo',
            'priority' => 'medium',
            'assignee_id' => $member2->id,
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'assignee_id' => $member2->id]);
    }

    #[Test]
    public function cannot_assign_task_to_non_member(): void
    {
        [$user, $project] = $this->createProjectWithMember();
        $stranger = User::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('tasks.update', [$project, $task]), [
            'title' => $task->title,
            'status' => 'todo',
            'priority' => 'medium',
            'assignee_id' => $stranger->id,
        ]);

        $response->assertSessionHasErrors('assignee_id');
    }

    #[Test]
    public function member_can_set_due_date(): void
    {
        [$user, $project] = $this->createProjectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id]);

        $this->actingAs($user)->put(route('tasks.update', [$project, $task]), [
            'title' => $task->title,
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => '2026-12-31',
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'due_date' => '2026-12-31']);
    }

    #[Test]
    public function project_page_shows_tasks(): void
    {
        [$user, $project] = $this->createProjectWithMember();
        Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id, 'title' => 'My Task']);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertSee('My Task');
    }

    #[Test]
    public function project_page_filters_by_status(): void
    {
        [$user, $project] = $this->createProjectWithMember();
        Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id, 'title' => 'Todo Task', 'status' => 'todo']);
        Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id, 'title' => 'Done Task', 'status' => 'done']);

        $response = $this->actingAs($user)->get(route('projects.show', ['project' => $project, 'status' => 'done']));
        $response->assertSee('Done Task');
        $response->assertDontSee('Todo Task');
    }
}
