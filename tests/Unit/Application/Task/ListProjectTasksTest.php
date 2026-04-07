<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Task\CreateTask;
use App\Application\Task\ListProjectTasks;
use App\Application\User\RegisterUser;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\Task;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;
use Tests\Support\Fakes\InMemoryTaskRepository;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeListProjectTasksFixture(): array
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
    $p1 = $createProject->execute($alice->id, 'P1', '');
    $p2 = $createProject->execute($alice->id, 'P2', '');

    $t1 = $createTask->execute($p1->id, $alice->id, 'T1', '', Status::Todo, Priority::Low, null, new DueDate(new DateTimeImmutable('2026-05-01T00:00:00Z')));
    $t2 = $createTask->execute($p1->id, $alice->id, 'T2', '', Status::InProgress, Priority::Medium, null, new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z')));
    $t3 = $createTask->execute($p1->id, $alice->id, 'T3', '', Status::Done, Priority::High, null, null);
    $t4 = $createTask->execute($p2->id, $alice->id, 'T4-other-project', '', Status::Todo, Priority::Low, null, null);

    $useCase = new ListProjectTasks($tasks);

    return ['useCase' => $useCase, 'p1' => $p1, 'p2' => $p2, 't1' => $t1, 't2' => $t2, 't3' => $t3, 't4' => $t4];
}

it('returns all tasks of a project without filters', function (): void {
    $ctx = makeListProjectTasksFixture();

    $ids = array_map(fn (Task $t): int => $t->id, $ctx['useCase']->execute($ctx['p1']->id));
    expect($ids)->toBe([$ctx['t1']->id, $ctx['t2']->id, $ctx['t3']->id]);
});

it('filters by status', function (): void {
    $ctx = makeListProjectTasksFixture();

    $result = $ctx['useCase']->execute($ctx['p1']->id, status: Status::InProgress);
    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($ctx['t2']->id);
});

it('filters by dueBefore and excludes tasks without dueDate', function (): void {
    $ctx = makeListProjectTasksFixture();

    $result = $ctx['useCase']->execute($ctx['p1']->id, dueBefore: new DateTimeImmutable('2026-05-15T00:00:00Z'));
    // Only t1 (due 2026-05-01) qualifies; t2 is due 2026-06-01 (after), t3 has no due.
    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($ctx['t1']->id);
});

it('combines status and dueBefore filters', function (): void {
    $ctx = makeListProjectTasksFixture();

    $result = $ctx['useCase']->execute(
        $ctx['p1']->id,
        status: Status::Todo,
        dueBefore: new DateTimeImmutable('2026-05-15T00:00:00Z'),
    );
    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($ctx['t1']->id);
});

it('does not return tasks from other projects', function (): void {
    $ctx = makeListProjectTasksFixture();

    $result = $ctx['useCase']->execute($ctx['p2']->id);
    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($ctx['t4']->id);
});
