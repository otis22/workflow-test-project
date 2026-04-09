<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Task\CreateTask;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\TaskNotFoundException;
use App\Application\Task\UpdateTask;
use App\Application\User\RegisterUser;
use App\Domain\Project\ProjectMember;
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

function makeUpdateTaskFixture(): array
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
    $eve = $register->execute('Eve', 'e@example.com', 'super-secret');

    $project = $createProject->execute($alice->id, 'P', '');
    $members->save(new ProjectMember(0, $project->id, $bob->id, $clock->now()));

    $task = $createTask->execute(
        projectId: $project->id,
        creatorId: $alice->id,
        title: 'Original',
        description: 'desc',
        status: Status::Todo,
        priority: Priority::Medium,
        assigneeId: null,
        dueDate: null,
    );

    $useCase = new UpdateTask($tasks, $members, $clock);

    return ['useCase' => $useCase, 'tasks' => $tasks, 'task' => $task, 'clock' => $clock, 'alice' => $alice, 'bob' => $bob, 'eve' => $eve, 'project' => $project];
}

it('updates title and bumps updatedAt', function (): void {
    $ctx = makeUpdateTaskFixture();
    $ctx['clock']->set(new DateTimeImmutable('2026-04-08T00:00:00Z'));

    $updated = $ctx['useCase']->execute($ctx['alice']->id, $ctx['task']->id, title: 'New');

    expect($updated->title)->toBe('New')
        ->and($updated->updatedAt)->toEqual($ctx['clock']->now());
});

it('updates status and priority', function (): void {
    $ctx = makeUpdateTaskFixture();

    $updated = $ctx['useCase']->execute(
        $ctx['alice']->id,
        $ctx['task']->id,
        status: Status::Done,
        priority: Priority::High,
    );

    expect($updated->status)->toBe(Status::Done)
        ->and($updated->priority)->toBe(Priority::High);
});

it('sets due date via changeDueDate flag', function (): void {
    $ctx = makeUpdateTaskFixture();
    $due = new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'));

    $updated = $ctx['useCase']->execute(
        $ctx['alice']->id,
        $ctx['task']->id,
        changeDueDate: true,
        dueDate: $due,
    );

    expect($updated->dueDate)->toBe($due);
});

it('clears due date when changeDueDate is true and value is null', function (): void {
    $ctx = makeUpdateTaskFixture();
    $ctx['useCase']->execute($ctx['alice']->id, $ctx['task']->id, changeDueDate: true, dueDate: new DueDate(new DateTimeImmutable));

    $cleared = $ctx['useCase']->execute($ctx['alice']->id, $ctx['task']->id, changeDueDate: true, dueDate: null);
    expect($cleared->dueDate)->toBeNull();
});

it('assigns a member', function (): void {
    $ctx = makeUpdateTaskFixture();

    $updated = $ctx['useCase']->execute(
        $ctx['alice']->id,
        $ctx['task']->id,
        changeAssignee: true,
        assigneeId: $ctx['bob']->id,
    );

    expect($updated->assigneeId)->toBe($ctx['bob']->id);
});

it('clears assignee with changeAssignee=true and null', function (): void {
    $ctx = makeUpdateTaskFixture();
    $ctx['useCase']->execute($ctx['alice']->id, $ctx['task']->id, changeAssignee: true, assigneeId: $ctx['bob']->id);

    $updated = $ctx['useCase']->execute($ctx['alice']->id, $ctx['task']->id, changeAssignee: true, assigneeId: null);

    expect($updated->assigneeId)->toBeNull();
});

it('rejects assignee who is not a member', function (): void {
    $ctx = makeUpdateTaskFixture();

    $ctx['useCase']->execute(
        $ctx['alice']->id,
        $ctx['task']->id,
        changeAssignee: true,
        assigneeId: $ctx['eve']->id,
    );
})->throws(NotAProjectMemberException::class);

it('rejects update when actor is not a project member', function (): void {
    $ctx = makeUpdateTaskFixture();

    $ctx['useCase']->execute($ctx['eve']->id, $ctx['task']->id, title: 'Hijacked');
})->throws(NotAProjectMemberException::class);

it('rejects unknown task', function (): void {
    $ctx = makeUpdateTaskFixture();

    $ctx['useCase']->execute($ctx['alice']->id, 999, title: 'x');
})->throws(TaskNotFoundException::class);

it('persists updates through the repository', function (): void {
    $ctx = makeUpdateTaskFixture();

    $ctx['useCase']->execute($ctx['alice']->id, $ctx['task']->id, title: 'Updated');

    $reloaded = $ctx['tasks']->findById($ctx['task']->id);
    expect($reloaded)->toBeInstanceOf(Task::class)
        ->and($reloaded->title)->toBe('Updated');
});
