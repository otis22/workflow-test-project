<?php

namespace Tests\E2E\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TaskWorkflowJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_the_task_workflow_journey(): void
    {
        $registrationResponse = $this->registerUser(
            name: 'Ada Lovelace',
            email: 'ada@example.com',
            password: 'password123',
        );

        $registrationResponse->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'ada@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        $createProjectResponse = $this->createProject(
            name: 'Platform refresh',
            description: 'Delivery scope for the next release',
        );

        $createProjectResponse->assertRedirect(route('projects.index'));

        /** @var int $projectId */
        $projectId = $user->projects()->value('projects.id');
        $project = Project::query()->findOrFail($projectId);

        $createTaskResponse = $this->postTask($user, $project->id, [
            'title' => 'Prepare release notes',
            'description' => 'Summarize the MVP milestones.',
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
            'due_date' => '2026-04-04',
            'assignee_id' => $user->id,
        ]);

        $createTaskResponse->assertRedirect(route('projects.show', $project));

        /** @var int $taskId */
        $taskId = $project->tasks()->value('id');
        $task = Task::query()->findOrFail($taskId);

        $this
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Prepare release notes');

        $updateTaskResponse = $this->updateTask($user, $task, [
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $updateTaskResponse->assertRedirect(route('projects.show', $project));

        $task->refresh();

        $this
            ->actingAs($user)
            ->get(route('projects.tasks.show', [$project, $task]))
            ->assertOk()
            ->assertSee('In Progress');

        $commentResponse = $this->postComment($user, $project->id, $task->id, 'Draft release notes are ready.');

        $commentResponse->assertRedirect(route('projects.tasks.show', [$project, $task]));

        $this
            ->actingAs($user)
            ->get(route('projects.tasks.show', [$project, $task]))
            ->assertOk()
            ->assertSee('Draft release notes are ready.');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function postTask(User $user, int $projectId, array $overrides): TestResponse
    {
        return $this
            ->actingAs($user)
            ->withSession(['_token' => 'task-token'])
            ->post(
                route('projects.tasks.store', $projectId),
                $this->makeTaskPayload($overrides),
            );
    }

    private function postComment(User $user, int $projectId, int $taskId, string $body): TestResponse
    {
        return $this
            ->actingAs($user)
            ->from(route('projects.tasks.show', [$projectId, $taskId]))
            ->withSession(['_token' => 'comment-token'])
            ->post(route('projects.tasks.comments.store', [$projectId, $taskId]), [
                '_token' => 'comment-token',
                'body' => $body,
            ]);
    }
}
