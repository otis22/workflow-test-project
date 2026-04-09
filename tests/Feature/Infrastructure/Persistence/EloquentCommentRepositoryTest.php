<?php

declare(strict_types=1);

use App\Domain\Task\Comment;
use App\Domain\Task\CommentRepository;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedCommentFixture(string $ownerEmail = 'owner@example.com'): array
{
    $user = UserModel::query()->create([
        'name' => 'Owner',
        'email' => $ownerEmail,
        'password_hash' => 'hash',
    ]);
    $project = ProjectModel::query()->create([
        'owner_id' => $user->id,
        'name' => 'P',
        'description' => '',
    ]);
    $task = TaskModel::query()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'assignee_id' => null,
        'title' => 'T',
        'description' => '',
        'status' => 'todo',
        'priority' => 'medium',
        'due_date' => null,
    ]);

    return ['userId' => (int) $user->id, 'taskId' => (int) $task->id];
}

function makeDomainComment(int $taskId, int $authorId, string $body = 'looks good', int $id = 0): Comment
{
    return new Comment(
        id: $id,
        taskId: $taskId,
        authorId: $authorId,
        body: $body,
        createdAt: new DateTimeImmutable('2026-04-09T10:00:00Z'),
    );
}

it('saves a new comment and returns entity with assigned id', function (): void {
    /** @var CommentRepository $repo */
    $repo = app(CommentRepository::class);
    ['userId' => $userId, 'taskId' => $taskId] = seedCommentFixture();

    $saved = $repo->save(makeDomainComment($taskId, $userId, 'first'));

    expect($saved)->toBeInstanceOf(Comment::class)
        ->and($saved->id)->toBeGreaterThan(0)
        ->and($saved->taskId)->toBe($taskId)
        ->and($saved->authorId)->toBe($userId)
        ->and($saved->body)->toBe('first');
});

it('preserves all mapped fields on round-trip', function (): void {
    /** @var CommentRepository $repo */
    $repo = app(CommentRepository::class);
    ['userId' => $userId, 'taskId' => $taskId] = seedCommentFixture();
    $saved = $repo->save(makeDomainComment($taskId, $userId, 'hello'));

    $loaded = $repo->listByTask($taskId)[0];

    expect($loaded->id)->toBe($saved->id)
        ->and($loaded->taskId)->toBe($taskId)
        ->and($loaded->authorId)->toBe($userId)
        ->and($loaded->body)->toBe('hello')
        ->and($loaded->createdAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($loaded->createdAt->format('U'))->toBe($saved->createdAt->format('U'));
});

it('listByTask returns comments for the task ordered by id', function (): void {
    /** @var CommentRepository $repo */
    $repo = app(CommentRepository::class);
    ['userId' => $userId, 'taskId' => $taskId] = seedCommentFixture();

    $first = $repo->save(makeDomainComment($taskId, $userId, 'first'));
    $second = $repo->save(makeDomainComment($taskId, $userId, 'second'));

    $result = $repo->listByTask($taskId);
    $ids = array_map(fn (Comment $c): int => $c->id, $result);

    expect($ids)->toBe([$first->id, $second->id]);
});

it('listByTask returns empty list for unknown task', function (): void {
    /** @var CommentRepository $repo */
    $repo = app(CommentRepository::class);

    expect($repo->listByTask(9999))->toBe([]);
});

it('listByTask does not return comments from other tasks', function (): void {
    /** @var CommentRepository $repo */
    $repo = app(CommentRepository::class);
    ['userId' => $userId, 'taskId' => $t1] = seedCommentFixture(ownerEmail: 'a@example.com');
    ['taskId' => $t2] = seedCommentFixture(ownerEmail: 'b@example.com');

    $repo->save(makeDomainComment($t1, $userId, 'on t1'));
    $repo->save(makeDomainComment($t2, $userId, 'on t2'));

    $result = $repo->listByTask($t1);

    expect($result)->toHaveCount(1)
        ->and($result[0]->body)->toBe('on t1');
});

it('throws when saving a comment with a positive id that does not exist', function (): void {
    /** @var CommentRepository $repo */
    $repo = app(CommentRepository::class);
    ['userId' => $userId, 'taskId' => $taskId] = seedCommentFixture();

    $stale = makeDomainComment($taskId, $userId, id: 999);
    $repo->save($stale);
})->throws(RuntimeException::class, 'Comment with id 999 not found for update');
