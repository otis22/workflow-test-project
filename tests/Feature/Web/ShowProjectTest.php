<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\Task\CreateTask;
use App\Application\User\RegisterUser;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginAliceForShow(): int
{
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $user = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);

    return $user->id;
}

function createProjectForAlice(int $aliceId, string $name = 'TaskFlow', string $description = 'MVP'): int
{
    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $project = $createProject->execute($aliceId, $name, $description);

    return $project->id;
}

it('redirects guests from GET /projects/{id} to login', function (): void {
    $response = $this->get('/projects/1');

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('returns 404 when the project does not exist', function (): void {
    loginAliceForShow();

    $response = $this->get('/projects/9999');

    $response->assertStatus(404);
});

it('returns 404 when actor is not a member of the project', function (): void {
    // Bob owns the project; Alice (the logged-in actor) is not a member.
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $bob = $register->execute('Bob', 'bob@example.com', 'super-secret');
    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $bobsProject = $createProject->execute($bob->id, 'Bobs Secret Project', 'sensitive stuff');

    loginAliceForShow();

    $response = $this->get('/projects/'.$bobsProject->id);

    $response->assertStatus(404);
    // Must not leak the name or description of a project the user can't access.
    $response->assertDontSee('Bobs Secret Project');
    $response->assertDontSee('sensitive stuff');
});

it('shows the project name and description for members', function (): void {
    $aliceId = loginAliceForShow();
    $projectId = createProjectForAlice($aliceId, 'TaskFlow MVP', 'Task tracker for teams');

    $response = $this->get('/projects/'.$projectId);

    $response->assertStatus(200)
        ->assertSee('TaskFlow MVP')
        ->assertSee('Task tracker for teams');
});

it('shows the project tasks in stable id order', function (): void {
    $aliceId = loginAliceForShow();
    $projectId = createProjectForAlice($aliceId);

    /** @var CreateTask $createTask */
    $createTask = app(CreateTask::class);
    $createTask->execute($projectId, $aliceId, 'First task', '', Status::Todo, Priority::Medium, null, null);
    $createTask->execute($projectId, $aliceId, 'Second task', '', Status::Todo, Priority::Medium, null, null);
    $createTask->execute($projectId, $aliceId, 'Third task', '', Status::Todo, Priority::Medium, null, null);

    $content = (string) $this->get('/projects/'.$projectId)->getContent();

    $posFirst = strpos($content, 'First task');
    $posSecond = strpos($content, 'Second task');
    $posThird = strpos($content, 'Third task');

    expect($posFirst)->toBeInt()
        ->and($posSecond)->toBeGreaterThan($posFirst)
        ->and($posThird)->toBeGreaterThan($posSecond);
});

it('shows an empty state when the project has no tasks', function (): void {
    $aliceId = loginAliceForShow();
    $projectId = createProjectForAlice($aliceId);

    $response = $this->get('/projects/'.$projectId);

    $response->assertStatus(200)
        ->assertSee('No tasks');
});

it('filters tasks by status via query parameter', function (): void {
    $aliceId = loginAliceForShow();
    $projectId = createProjectForAlice($aliceId);

    /** @var CreateTask $createTask */
    $createTask = app(CreateTask::class);
    $createTask->execute($projectId, $aliceId, 'Todo task', '', Status::Todo, Priority::Medium, null, null);
    $createTask->execute($projectId, $aliceId, 'Done task', '', Status::Done, Priority::Medium, null, null);

    $response = $this->get('/projects/'.$projectId.'?status=todo');

    $response->assertStatus(200)
        ->assertSee('Todo task')
        ->assertDontSee('Done task');
});

it('ignores an invalid status query parameter', function (): void {
    $aliceId = loginAliceForShow();
    $projectId = createProjectForAlice($aliceId);

    /** @var CreateTask $createTask */
    $createTask = app(CreateTask::class);
    $createTask->execute($projectId, $aliceId, 'Visible task', '', Status::Todo, Priority::Medium, null, null);

    $response = $this->get('/projects/'.$projectId.'?status=garbage');

    $response->assertStatus(200)
        ->assertSee('Visible task');
});

it('projects index links to the show page for each project', function (): void {
    $aliceId = loginAliceForShow();
    $projectId = createProjectForAlice($aliceId);

    $response = $this->get('/projects');

    $response->assertStatus(200)
        ->assertSee('href="'.route('projects.show', $projectId).'"', false);
});

it('routes /projects/create before /projects/{project}', function (): void {
    loginAliceForShow();

    // The literal "create" segment must route to the create form, not be
    // interpreted as a numeric project id (would 404 due to whereNumber).
    $response = $this->get('/projects/create');

    $response->assertStatus(200)->assertSee('Create a new project');
});
