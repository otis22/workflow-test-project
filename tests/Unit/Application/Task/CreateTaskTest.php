<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Task\CreateTask;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\ProjectNotFoundException;
use App\Application\User\RegisterUser;
use App\Domain\Project\ProjectMember;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;
use Tests\Support\Fakes\InMemoryTaskRepository;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeCreateTaskFixture(): array
{
    $users = new InMemoryUserRepository;
    $projects = new InMemoryProjectRepository;
    $members = new InMemoryProjectMemberRepository;
    $tasks = new InMemoryTaskRepository;
    $clock = new FakeClock(new DateTimeImmutable('2026-04-07T12:00:00Z'));

    $register = new RegisterUser($users, new FakePasswordHasher, $clock);
    $createProject = new CreateProject($projects, $members, $users, $clock);

    $alice = $register->execute('Alice', 'a@example.com', 'super-secret');
    $bob = $register->execute('Bob', 'b@example.com', 'super-secret');
    $eve = $register->execute('Eve', 'e@example.com', 'super-secret');

    $project = $createProject->execute($alice->id, 'P', '');

    // add Bob as a member of Alice's project (Eve stays outside)
    $members->save(new ProjectMember(0, $project->id, $bob->id, $clock->now()));

    $useCase = new CreateTask($tasks, $projects, $members, $clock);

    return ['useCase' => $useCase, 'tasks' => $tasks, 'alice' => $alice, 'bob' => $bob, 'eve' => $eve, 'project' => $project];
}

it('creates a task for a member creator', function (): void {
    $ctx = makeCreateTaskFixture();

    $task = $ctx['useCase']->execute(
        projectId: $ctx['project']->id,
        creatorId: $ctx['alice']->id,
        title: 'Do the thing',
        description: 'details',
        status: Status::Todo,
        priority: Priority::High,
        assigneeId: $ctx['bob']->id,
        dueDate: new DueDate(new DateTimeImmutable('2026-05-01T00:00:00Z')),
    );

    expect($task->id)->toBe(1)
        ->and($task->projectId)->toBe($ctx['project']->id)
        ->and($task->creatorId)->toBe($ctx['alice']->id)
        ->and($task->assigneeId)->toBe($ctx['bob']->id)
        ->and($task->status)->toBe(Status::Todo)
        ->and($task->priority)->toBe(Priority::High);

    expect($ctx['tasks']->findById($task->id))->not->toBeNull();
});

it('creates a task without assignee', function (): void {
    $ctx = makeCreateTaskFixture();

    $task = $ctx['useCase']->execute(
        projectId: $ctx['project']->id,
        creatorId: $ctx['alice']->id,
        title: 'Solo',
        description: '',
        status: Status::Todo,
        priority: Priority::Medium,
        assigneeId: null,
        dueDate: null,
    );

    expect($task->assigneeId)->toBeNull()
        ->and($task->dueDate)->toBeNull();
});

it('rejects unknown project', function (): void {
    $ctx = makeCreateTaskFixture();

    $ctx['useCase']->execute(
        projectId: 999,
        creatorId: $ctx['alice']->id,
        title: 't',
        description: '',
        status: Status::Todo,
        priority: Priority::Low,
        assigneeId: null,
        dueDate: null,
    );
})->throws(ProjectNotFoundException::class);

it('rejects creator who is not a project member', function (): void {
    $ctx = makeCreateTaskFixture();

    $ctx['useCase']->execute(
        projectId: $ctx['project']->id,
        creatorId: $ctx['eve']->id,
        title: 't',
        description: '',
        status: Status::Todo,
        priority: Priority::Low,
        assigneeId: null,
        dueDate: null,
    );
})->throws(NotAProjectMemberException::class);

it('rejects assignee who is not a project member', function (): void {
    $ctx = makeCreateTaskFixture();

    $ctx['useCase']->execute(
        projectId: $ctx['project']->id,
        creatorId: $ctx['alice']->id,
        title: 't',
        description: '',
        status: Status::Todo,
        priority: Priority::Low,
        assigneeId: $ctx['eve']->id,
        dueDate: null,
    );
})->throws(NotAProjectMemberException::class);
