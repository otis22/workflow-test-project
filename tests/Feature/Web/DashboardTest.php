<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\Task\CreateTask;
use App\Application\User\RegisterUser;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginForDashboard(): int
{
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $user = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);

    return $user->id;
}

it('redirects guests from /dashboard to login', function (): void {
    $response = $this->get('/dashboard');
    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('shows assigned tasks on the dashboard', function (): void {
    $aliceId = loginForDashboard();
    /** @var CreateProject $cp */
    $cp = app(CreateProject::class);
    $project = $cp->execute($aliceId, 'P', '');
    /** @var CreateTask $ct */
    $ct = app(CreateTask::class);
    $ct->execute($project->id, $aliceId, 'Assigned to me', '', Status::Todo, Priority::Medium, $aliceId, null);
    $ct->execute($project->id, $aliceId, 'Unassigned', '', Status::Todo, Priority::Medium, null, null);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('Assigned to me')
        ->assertDontSee('Unassigned');
});

it('shows upcoming deadlines sorted by date', function (): void {
    $aliceId = loginForDashboard();
    /** @var CreateProject $cp */
    $cp = app(CreateProject::class);
    $project = $cp->execute($aliceId, 'P', '');
    /** @var CreateTask $ct */
    $ct = app(CreateTask::class);
    $ct->execute($project->id, $aliceId, 'Sooner task', '', Status::Todo, Priority::Medium, $aliceId, new DueDate(new DateTimeImmutable('2026-06-01')));
    $ct->execute($project->id, $aliceId, 'Later task', '', Status::Todo, Priority::Medium, $aliceId, new DueDate(new DateTimeImmutable('2026-12-01')));

    $content = (string) $this->get('/dashboard')->getContent();

    // In the "Upcoming deadlines" section, Sooner should appear before Later.
    // Both also appear in "My tasks" section (by id order = same order),
    // so we use the last occurrence of each name, which falls in the deadlines section.
    $posSooner = strrpos($content, 'Sooner task');
    $posLater = strrpos($content, 'Later task');
    expect($posSooner)->toBeInt()
        ->and($posLater)->toBeGreaterThan($posSooner);
});

it('shows project links on the dashboard', function (): void {
    $aliceId = loginForDashboard();
    /** @var CreateProject $cp */
    $cp = app(CreateProject::class);
    $project = $cp->execute($aliceId, 'My Project', '');

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('My Project')
        ->assertSee(route('projects.show', $project->id));
});

it('shows empty state when no tasks or projects', function (): void {
    loginForDashboard();

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('No tasks assigned')
        ->assertSee('No upcoming deadlines')
        ->assertSee('No projects yet');
});

it('redirects to dashboard after login', function (): void {
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $register->execute('Alice', 'alice@example.com', 'super-secret');

    $response = $this->post('/login', [
        'email' => 'alice@example.com',
        'password' => 'super-secret',
    ]);

    $response->assertStatus(302)->assertRedirect(route('dashboard'));
});
