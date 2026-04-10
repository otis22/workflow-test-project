<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\Task\ListProjectTasks;
use App\Application\User\RegisterUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginAndCreateProject(): array
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

    return ['userId' => $user->id, 'projectId' => $project->id];
}

it('redirects guests from GET /projects/{id}/tasks/create to login', function (): void {
    $response = $this->get('/projects/1/tasks/create');
    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('returns 404 for non-member on GET /projects/{id}/tasks/create', function (): void {
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $bob = $register->execute('Bob', 'bob@example.com', 'super-secret');
    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $bobsProject = $createProject->execute($bob->id, 'Bobs project', '');

    // Login as Alice (not a member of Bob's project).
    $alice = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($alice->id);

    $response = $this->get('/projects/'.$bobsProject->id.'/tasks/create');
    $response->assertStatus(404);
});

it('shows the create task form for members', function (): void {
    ['projectId' => $projectId] = loginAndCreateProject();

    $response = $this->get('/projects/'.$projectId.'/tasks/create');

    $response->assertStatus(200)
        ->assertSee('Title')
        ->assertSee('Status')
        ->assertSee('Priority')
        ->assertSee('method="POST"', false);
});

it('creates a task and redirects to the project show page', function (): void {
    ['userId' => $userId, 'projectId' => $projectId] = loginAndCreateProject();

    $response = $this->post('/projects/'.$projectId.'/tasks', [
        'title' => 'My first task',
        'description' => 'Something to do',
        'status' => 'todo',
        'priority' => 'medium',
        'due_date' => '',
    ]);

    $response->assertStatus(302)->assertRedirect(route('projects.show', $projectId));

    /** @var ListProjectTasks $list */
    $list = app(ListProjectTasks::class);
    $tasks = $list->execute($userId, $projectId);
    expect($tasks)->toHaveCount(1)
        ->and($tasks[0]->title)->toBe('My first task');
});

it('rejects an empty title', function (): void {
    ['projectId' => $projectId] = loginAndCreateProject();

    $response = $this->from('/projects/'.$projectId.'/tasks/create')
        ->post('/projects/'.$projectId.'/tasks', [
            'title' => '',
            'description' => '',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => '',
        ]);

    $response->assertRedirect('/projects/'.$projectId.'/tasks/create')
        ->assertSessionHasErrors('title');
});

it('rejects a whitespace-only title', function (): void {
    ['projectId' => $projectId] = loginAndCreateProject();

    $response = $this->from('/projects/'.$projectId.'/tasks/create')
        ->post('/projects/'.$projectId.'/tasks', [
            'title' => '   ',
            'description' => '',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => '',
        ]);

    $response->assertRedirect('/projects/'.$projectId.'/tasks/create')
        ->assertSessionHasErrors('title');
});

it('rejects an invalid status value', function (): void {
    ['projectId' => $projectId] = loginAndCreateProject();

    $response = $this->from('/projects/'.$projectId.'/tasks/create')
        ->post('/projects/'.$projectId.'/tasks', [
            'title' => 'Valid title',
            'description' => '',
            'status' => 'garbage',
            'priority' => 'medium',
            'due_date' => '',
        ]);

    $response->assertRedirect('/projects/'.$projectId.'/tasks/create')
        ->assertSessionHasErrors('status');
});

it('rejects an invalid priority value', function (): void {
    ['projectId' => $projectId] = loginAndCreateProject();

    $response = $this->from('/projects/'.$projectId.'/tasks/create')
        ->post('/projects/'.$projectId.'/tasks', [
            'title' => 'Valid title',
            'description' => '',
            'status' => 'todo',
            'priority' => 'garbage',
            'due_date' => '',
        ]);

    $response->assertRedirect('/projects/'.$projectId.'/tasks/create')
        ->assertSessionHasErrors('priority');
});

it('accepts a null due date', function (): void {
    ['projectId' => $projectId] = loginAndCreateProject();

    $response = $this->post('/projects/'.$projectId.'/tasks', [
        'title' => 'No deadline',
        'description' => '',
        'status' => 'todo',
        'priority' => 'low',
        'due_date' => '',
    ]);

    $response->assertStatus(302)->assertRedirect(route('projects.show', $projectId));
});
