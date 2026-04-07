<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Task\AddComment;
use App\Application\Task\CreateTask;
use App\Application\Task\ListTaskComments;
use App\Application\User\RegisterUser;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryCommentRepository;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;
use Tests\Support\Fakes\InMemoryTaskRepository;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeListTaskCommentsFixture(): array
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
    $addComment = new AddComment($comments, $tasks, $members, $clock);

    $alice = $register->execute('Alice', 'a@example.com', 'super-secret');
    $project = $createProject->execute($alice->id, 'P', '');
    $t1 = $createTask->execute($project->id, $alice->id, 'T1', '', Status::Todo, Priority::Low, null, null);
    $t2 = $createTask->execute($project->id, $alice->id, 'T2', '', Status::Todo, Priority::Low, null, null);

    $addComment->execute($t1->id, $alice->id, 'first');
    $addComment->execute($t1->id, $alice->id, 'second');
    $addComment->execute($t2->id, $alice->id, 'on t2');

    $useCase = new ListTaskComments($comments);

    return ['useCase' => $useCase, 't1' => $t1, 't2' => $t2];
}

it('returns comments for a task in id order', function (): void {
    $ctx = makeListTaskCommentsFixture();

    $result = $ctx['useCase']->execute($ctx['t1']->id);
    $bodies = array_map(fn ($c): string => $c->body, $result);

    expect($bodies)->toBe(['first', 'second']);
});

it('returns empty list when task has no comments', function (): void {
    $ctx = makeListTaskCommentsFixture();

    expect($ctx['useCase']->execute(999))->toBe([]);
});

it('does not return comments from other tasks', function (): void {
    $ctx = makeListTaskCommentsFixture();

    $t2Comments = $ctx['useCase']->execute($ctx['t2']->id);
    expect($t2Comments)->toHaveCount(1)
        ->and($t2Comments[0]->body)->toBe('on t2');
});
