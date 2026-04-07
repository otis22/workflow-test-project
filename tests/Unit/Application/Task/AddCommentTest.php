<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Task\AddComment;
use App\Application\Task\CreateTask;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\TaskNotFoundException;
use App\Application\User\RegisterUser;
use App\Domain\Project\ProjectMember;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryCommentRepository;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;
use Tests\Support\Fakes\InMemoryTaskRepository;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeAddCommentFixture(): array
{
    $users = new InMemoryUserRepository;
    $projects = new InMemoryProjectRepository;
    $members = new InMemoryProjectMemberRepository;
    $tasks = new InMemoryTaskRepository;
    $comments = new InMemoryCommentRepository;
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
        $project->id, $alice->id, 'T', '', Status::Todo, Priority::Low, null, null,
    );

    $useCase = new AddComment($comments, $tasks, $members, $clock);

    return ['useCase' => $useCase, 'comments' => $comments, 'task' => $task, 'alice' => $alice, 'bob' => $bob, 'eve' => $eve];
}

it('adds a comment from a project member', function (): void {
    $ctx = makeAddCommentFixture();

    $comment = $ctx['useCase']->execute($ctx['task']->id, $ctx['bob']->id, 'looks good');

    expect($comment->id)->toBe(1)
        ->and($comment->taskId)->toBe($ctx['task']->id)
        ->and($comment->authorId)->toBe($ctx['bob']->id)
        ->and($comment->body)->toBe('looks good');

    $persisted = $ctx['comments']->listByTask($ctx['task']->id);
    expect($persisted)->toHaveCount(1);
});

it('rejects unknown task', function (): void {
    $ctx = makeAddCommentFixture();

    $ctx['useCase']->execute(999, $ctx['alice']->id, 'x');
})->throws(TaskNotFoundException::class);

it('rejects author who is not a project member', function (): void {
    $ctx = makeAddCommentFixture();

    $ctx['useCase']->execute($ctx['task']->id, $ctx['eve']->id, 'x');
})->throws(NotAProjectMemberException::class);

it('rejects empty body via domain validation', function (): void {
    $ctx = makeAddCommentFixture();

    $ctx['useCase']->execute($ctx['task']->id, $ctx['alice']->id, '   ');
})->throws(InvalidArgumentException::class, 'Comment body must not be empty');
