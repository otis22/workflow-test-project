<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\Task\CreateTask;
use App\Application\User\RegisterUser;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginAndCreateTaskForEdit(): array
{
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $user = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);
    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $project = $createProject->execute($user->id, 'TestProject', '');
    /** @var CreateTask $createTask */
    $createTask = app(CreateTask::class);
    $task = $createTask->execute(
        $project->id, $user->id, 'Original title', 'Original description',
        Status::Todo, Priority::Medium, null, new DueDate(new DateTimeImmutable('2026-06-01')),
    );

    return ['userId' => $user->id, 'projectId' => $project->id, 'taskId' => $task->id];
}

it('redirects guests from GET /tasks/{id}/edit to login', function (): void {
    $response = $this->get('/tasks/1/edit');
    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('returns 404 when actor is not a project member', function (): void {
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $bob = $register->execute('Bob', 'bob@example.com', 'super-secret');
    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $project = $createProject->execute($bob->id, 'Bobs', '');
    /** @var CreateTask $createTask */
    $createTask = app(CreateTask::class);
    $task = $createTask->execute(
        $project->id, $bob->id, 'Secret task', '', Status::Todo, Priority::Low, null, null,
    );

    $alice = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($alice->id);

    $response = $this->get('/tasks/'.$task->id.'/edit');
    $response->assertStatus(404);
});

it('shows edit form pre-filled with current task values', function (): void {
    ['taskId' => $taskId] = loginAndCreateTaskForEdit();

    $response = $this->get('/tasks/'.$taskId.'/edit');

    $response->assertStatus(200)
        ->assertSee('Original title')
        ->assertSee('Original description')
        ->assertSee('method="POST"', false)
        ->assertSee('name="_method"', false);
});

it('updates the task and redirects to show', function (): void {
    ['taskId' => $taskId] = loginAndCreateTaskForEdit();

    $response = $this->put('/tasks/'.$taskId, [
        'title' => 'Updated title',
        'description' => 'Updated description',
        'status' => 'done',
        'priority' => 'high',
        'due_date' => '2026-12-31',
    ]);

    $response->assertStatus(302)->assertRedirect(route('tasks.show', $taskId));

    /** @var TaskRepository $tasks */
    $tasks = app(TaskRepository::class);
    $updated = $tasks->findById($taskId);
    expect($updated->title)->toBe('Updated title')
        ->and($updated->status)->toBe(Status::Done)
        ->and($updated->priority)->toBe(Priority::High);
});

it('rejects an empty title', function (): void {
    ['taskId' => $taskId] = loginAndCreateTaskForEdit();

    $response = $this->from('/tasks/'.$taskId.'/edit')
        ->put('/tasks/'.$taskId, [
            'title' => '',
            'description' => '',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => '',
        ]);

    $response->assertRedirect('/tasks/'.$taskId.'/edit')
        ->assertSessionHasErrors('title');
});

it('changes status from todo to done', function (): void {
    ['taskId' => $taskId] = loginAndCreateTaskForEdit();

    $this->put('/tasks/'.$taskId, [
        'title' => 'Original title',
        'description' => '',
        'status' => 'done',
        'priority' => 'medium',
        'due_date' => '',
    ]);

    /** @var TaskRepository $tasks */
    $tasks = app(TaskRepository::class);
    expect($tasks->findById($taskId)->status)->toBe(Status::Done);
});

it('clears the due date when submitted empty', function (): void {
    ['taskId' => $taskId] = loginAndCreateTaskForEdit();

    $this->put('/tasks/'.$taskId, [
        'title' => 'Original title',
        'description' => '',
        'status' => 'todo',
        'priority' => 'medium',
        'due_date' => '',
    ]);

    /** @var TaskRepository $tasks */
    $tasks = app(TaskRepository::class);
    expect($tasks->findById($taskId)->dueDate)->toBeNull();
});
