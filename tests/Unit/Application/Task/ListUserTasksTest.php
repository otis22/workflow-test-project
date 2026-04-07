<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Task\CreateTask;
use App\Application\Task\ListUserTasks;
use App\Application\User\RegisterUser;
use App\Domain\Project\ProjectMember;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;
use Tests\Support\Fakes\InMemoryTaskRepository;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeListUserTasksFixture(): array
{
    $users = new InMemoryUserRepository;
    $projects = new InMemoryProjectRepository;
    $members = new InMemoryProjectMemberRepository;
    $tasks = new InMemoryTaskRepository;
    $clock = new FakeClock(new DateTimeImmutable('2026-04-07T12:00:00Z'));

    $register = new RegisterUser($users, new FakePasswordHasher, $clock);
    $createProject = new CreateProject($projects, $members, $users, $clock);
    $createTask = new CreateTask($tasks, $projects, $members, $clock);

    $alice = $register->execute('Alice', 'a@example.com', 'super-secret');
    $bob = $register->execute('Bob', 'b@example.com', 'super-secret');
    $p = $createProject->execute($alice->id, 'P', '');
    $members->save(new ProjectMember(0, $p->id, $bob->id, $clock->now()));

    $t1 = $createTask->execute($p->id, $alice->id, 'Alice owns', '', Status::Todo, Priority::Low, $alice->id, null);
    $t2 = $createTask->execute($p->id, $alice->id, 'Bob owns', '', Status::Todo, Priority::Low, $bob->id, null);
    $t3 = $createTask->execute($p->id, $alice->id, 'No assignee', '', Status::Todo, Priority::Low, null, null);

    $useCase = new ListUserTasks($tasks);

    return ['useCase' => $useCase, 'alice' => $alice, 'bob' => $bob, 't1' => $t1, 't2' => $t2, 't3' => $t3];
}

it('returns tasks assigned to the user', function (): void {
    $ctx = makeListUserTasksFixture();

    $aliceTasks = $ctx['useCase']->execute($ctx['alice']->id);
    expect($aliceTasks)->toHaveCount(1)
        ->and($aliceTasks[0]->id)->toBe($ctx['t1']->id);

    $bobTasks = $ctx['useCase']->execute($ctx['bob']->id);
    expect($bobTasks)->toHaveCount(1)
        ->and($bobTasks[0]->id)->toBe($ctx['t2']->id);
});

it('returns empty list when user has no assigned tasks', function (): void {
    $ctx = makeListUserTasksFixture();

    expect($ctx['useCase']->execute(999))->toBe([]);
});

it('does not return tasks without an assignee', function (): void {
    $ctx = makeListUserTasksFixture();

    $alice = $ctx['useCase']->execute($ctx['alice']->id);
    $ids = array_map(fn ($t): int => $t->id, $alice);
    expect($ids)->not->toContain($ctx['t3']->id);
});
