<?php

declare(strict_types=1);

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\User\RegisterUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function loginUser(string $name = 'Alice', string $email = 'alice@example.com'): int
{
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $user = $register->execute($name, $email, 'super-secret');

    /** @var SessionGuard $guard */
    $guard = app(SessionGuard::class);
    $guard->login($user->id);

    return $user->id;
}

it('redirects guests from /projects to login', function (): void {
    $response = $this->get('/projects');

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('shows an empty state when the user has no projects', function (): void {
    loginUser();

    $response = $this->get('/projects');

    $response->assertStatus(200)
        ->assertSee('Your projects')
        ->assertSee('no projects');
});

it('shows only the projects the user is a member of', function (): void {
    $aliceId = loginUser('Alice', 'alice@example.com');

    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $createProject->execute($aliceId, 'Alices Project', 'only visible to alice');

    // Bob's project — Alice is not a member.
    /** @var RegisterUser $register */
    $register = app(RegisterUser::class);
    $bob = $register->execute('Bob', 'bob@example.com', 'super-secret');
    $createProject->execute($bob->id, 'Bobs Project', 'hidden from alice');

    $response = $this->get('/projects');

    $response->assertStatus(200)
        ->assertSee('Alices Project')
        ->assertDontSee('Bobs Project');
});

it('shows projects in stable id order', function (): void {
    $aliceId = loginUser();

    /** @var CreateProject $createProject */
    $createProject = app(CreateProject::class);
    $createProject->execute($aliceId, 'First project', '');
    $createProject->execute($aliceId, 'Second project', '');
    $createProject->execute($aliceId, 'Third project', '');

    $content = (string) $this->get('/projects')->getContent();

    $posFirst = strpos($content, 'First project');
    $posSecond = strpos($content, 'Second project');
    $posThird = strpos($content, 'Third project');

    expect($posFirst)->toBeInt()
        ->and($posSecond)->toBeGreaterThan($posFirst)
        ->and($posThird)->toBeGreaterThan($posSecond);
});

it('nav Projects link points to the projects index route when signed in', function (): void {
    loginUser();

    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('href="'.route('projects.index').'"', false);
});
