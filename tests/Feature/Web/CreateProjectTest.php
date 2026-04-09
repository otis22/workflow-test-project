<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\Project\ListUserProjects;
use App\Application\User\RegisterUser;
use App\Domain\Project\ProjectMemberRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginAliceForCreate(): int
{
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $user = $register->execute('Alice', 'alice@example.com', 'super-secret');
    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);

    return $user->id;
}

it('redirects guests from GET /projects/create to login', function (): void {
    $response = $this->get('/projects/create');

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('rejects guest POST /projects via the auth middleware', function (): void {
    $response = $this->post('/projects', ['name' => 'X', 'description' => '']);

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('shows the create project form to authenticated users', function (): void {
    loginAliceForCreate();

    $response = $this->get('/projects/create');

    $response->assertStatus(200)
        ->assertSee('Name')
        ->assertSee('Description')
        ->assertSee('method="POST"', false)
        ->assertSee('action="'.route('projects.store').'"', false);
});

it('creates a new project and redirects to the list', function (): void {
    $aliceId = loginAliceForCreate();

    $response = $this->post('/projects', [
        'name' => 'TaskFlow MVP',
        'description' => 'Minimal project tracker',
    ]);

    $response->assertStatus(302)->assertRedirect(route('projects.index'));

    /** @var ListUserProjects $list */
    $list = app(ListUserProjects::class);
    $aliceProjects = $list->execute($aliceId);
    expect($aliceProjects)->toHaveCount(1)
        ->and($aliceProjects[0]->ownerId)->toBe($aliceId)
        ->and($aliceProjects[0]->name)->toBe('TaskFlow MVP')
        ->and($aliceProjects[0]->description)->toBe('Minimal project tracker');
});

it('registers the creator as the first project member', function (): void {
    $aliceId = loginAliceForCreate();

    $this->post('/projects', ['name' => 'Solo project', 'description' => '']);

    /** @var ListUserProjects $list */
    $list = app(ListUserProjects::class);
    $aliceProjects = $list->execute($aliceId);
    expect($aliceProjects)->toHaveCount(1);

    /** @var ProjectMemberRepository $members */
    $members = app(ProjectMemberRepository::class);
    $member = $members->findByProjectAndUser($aliceProjects[0]->id, $aliceId);
    expect($member)->not->toBeNull()
        ->and($member->userId)->toBe($aliceId)
        ->and($member->projectId)->toBe($aliceProjects[0]->id);
});

it('rejects submission with an empty name', function (): void {
    loginAliceForCreate();

    $response = $this->from('/projects/create')->post('/projects', [
        'name' => '',
        'description' => 'description only',
    ]);

    $response->assertRedirect('/projects/create')->assertSessionHasErrors('name');
});

it('rejects submission with a whitespace-only name', function (): void {
    loginAliceForCreate();

    $response = $this->from('/projects/create')->post('/projects', [
        'name' => '   ',
        'description' => '',
    ]);

    $response->assertRedirect('/projects/create')->assertSessionHasErrors('name');
});

it('rejects submission with a non-string name (array injection)', function (): void {
    loginAliceForCreate();

    $response = $this->from('/projects/create')->post('/projects', [
        'name' => ['array', 'injected'],
        'description' => '',
    ]);

    $response->assertRedirect('/projects/create')->assertSessionHasErrors('name');
});

it('rejects submission when name is longer than 255 characters', function (): void {
    loginAliceForCreate();

    $response = $this->from('/projects/create')->post('/projects', [
        'name' => str_repeat('a', 256),
        'description' => '',
    ]);

    $response->assertRedirect('/projects/create')->assertSessionHasErrors('name');
});

it('accepts an empty description', function (): void {
    loginAliceForCreate();

    $response = $this->post('/projects', [
        'name' => 'No description',
        'description' => '',
    ]);

    $response->assertStatus(302)->assertRedirect(route('projects.index'));
});
