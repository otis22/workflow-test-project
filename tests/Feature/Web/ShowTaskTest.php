<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\Task\AddComment;
use App\Application\Task\CreateTask;
use App\Application\User\RegisterUser;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginAndCreateTask(): array
{
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $user = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);
    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $project = $createProject->execute($user->id, 'TestProject', 'desc');
    /** @var CreateTask $createTask */
    $createTask = app(CreateTask::class);
    $task = $createTask->execute(
        $project->id, $user->id, 'My task', 'Task description',
        Status::Todo, Priority::High, null, null,
    );

    return ['userId' => $user->id, 'projectId' => $project->id, 'taskId' => $task->id];
}

it('redirects guests from GET /tasks/{id} to login', function (): void {
    $response = $this->get('/tasks/1');
    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('returns 404 for an unknown task id', function (): void {
    ['userId' => $userId] = loginAndCreateTask();
    $response = $this->get('/tasks/9999');
    $response->assertStatus(404);
});

it('returns 404 when actor is not a project member', function (): void {
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $bob = $register->execute('Bob', 'bob@example.com', 'super-secret');
    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $bobsProject = $createProject->execute($bob->id, 'Bobs project', '');
    /** @var CreateTask $createTask */
    $createTask = app(CreateTask::class);
    $task = $createTask->execute(
        $bobsProject->id, $bob->id, 'Secret task', '',
        Status::Todo, Priority::Low, null, null,
    );

    // Login as Alice — not a member of Bob's project.
    $alice = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($alice->id);

    $response = $this->get('/tasks/'.$task->id);
    $response->assertStatus(404);
});

it('shows the task fields for a project member', function (): void {
    ['taskId' => $taskId] = loginAndCreateTask();

    $response = $this->get('/tasks/'.$taskId);

    $response->assertStatus(200)
        ->assertSee('My task')
        ->assertSee('Task description')
        ->assertSee('todo')
        ->assertSee('high');
});

it('shows existing comments on the task', function (): void {
    ['userId' => $userId, 'taskId' => $taskId] = loginAndCreateTask();

    /** @var AddComment $addComment */
    $addComment = app(AddComment::class);
    $addComment->execute($taskId, $userId, 'First comment');
    $addComment->execute($taskId, $userId, 'Second comment');

    $response = $this->get('/tasks/'.$taskId);

    $response->assertStatus(200)
        ->assertSee('First comment')
        ->assertSee('Second comment');
});

it('adds a comment and redirects back to the task', function (): void {
    ['taskId' => $taskId] = loginAndCreateTask();

    $response = $this->post('/tasks/'.$taskId.'/comments', [
        'body' => 'New comment from web',
    ]);

    $response->assertStatus(302)->assertRedirect(route('tasks.show', $taskId));

    // Verify it shows up on the page.
    $response = $this->get('/tasks/'.$taskId);
    $response->assertSee('New comment from web');
});

it('rejects an empty comment body', function (): void {
    ['taskId' => $taskId] = loginAndCreateTask();

    $response = $this->from('/tasks/'.$taskId)
        ->post('/tasks/'.$taskId.'/comments', ['body' => '']);

    $response->assertRedirect('/tasks/'.$taskId)
        ->assertSessionHasErrors('body');
});

it('task titles in project show link to the task detail page', function (): void {
    ['projectId' => $projectId, 'taskId' => $taskId] = loginAndCreateTask();

    $response = $this->get('/projects/'.$projectId);

    $response->assertStatus(200)
        ->assertSee('href="'.route('tasks.show', $taskId).'"', false);
});
