<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Project\ListUserProjects;
use App\Application\User\RegisterUser;
use App\Domain\Project\Project;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeListUserProjectsFixture(): array
{
    $users = new InMemoryUserRepository;
    $projects = new InMemoryProjectRepository;
    $members = new InMemoryProjectMemberRepository;
    $clock = new FakeClock;

    $register = new RegisterUser($users, new FakePasswordHasher, $clock);
    $create = new CreateProject($projects, $members, $users, $clock);

    return [$users, $projects, $members, $clock, $register, $create];
}

it('returns projects where the user is a member', function (): void {
    [$users, $projects, $members, $clock, $register, $create] = makeListUserProjectsFixture();

    $alice = $register->execute('Alice', 'a@example.com', 'super-secret');
    $bob = $register->execute('Bob', 'b@example.com', 'super-secret');

    $p1 = $create->execute($alice->id, 'Alice-A', '');
    $p2 = $create->execute($alice->id, 'Alice-B', '');
    $p3 = $create->execute($bob->id, 'Bob-C', '');

    $useCase = new ListUserProjects($projects, $members);

    $result = $useCase->execute($alice->id);

    expect($result)->toHaveCount(2)
        ->and($result[0]->id)->toBe($p1->id)
        ->and($result[1]->id)->toBe($p2->id);

    $bobResult = $useCase->execute($bob->id);
    expect($bobResult)->toHaveCount(1)
        ->and($bobResult[0]->id)->toBe($p3->id);
});

it('returns empty list when user has no memberships', function (): void {
    [, $projects, $members] = makeListUserProjectsFixture();

    $useCase = new ListUserProjects($projects, $members);

    expect($useCase->execute(999))->toBe([]);
});

it('returns projects in stable id order', function (): void {
    [, $projects, $members, , $register, $create] = makeListUserProjectsFixture();

    $alice = $register->execute('Alice', 'a@example.com', 'super-secret');
    $create->execute($alice->id, 'A', '');
    $create->execute($alice->id, 'B', '');
    $create->execute($alice->id, 'C', '');

    $useCase = new ListUserProjects($projects, $members);
    $ids = array_map(fn (Project $p): int => $p->id, $useCase->execute($alice->id));

    expect($ids)->toBe([1, 2, 3]);
});
