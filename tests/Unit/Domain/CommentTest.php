<?php

declare(strict_types=1);

use App\Domain\Task\Comment;

it('creates a comment with valid data', function (): void {
    $now = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $comment = new Comment(
        id: 1,
        taskId: 10,
        authorId: 5,
        body: 'looks good',
        createdAt: $now,
    );

    expect($comment->id)->toBe(1)
        ->and($comment->taskId)->toBe(10)
        ->and($comment->authorId)->toBe(5)
        ->and($comment->body)->toBe('looks good')
        ->and($comment->createdAt)->toEqual($now);
});

it('rejects zero task id', function (): void {
    new Comment(id: 1, taskId: 0, authorId: 1, body: 'x', createdAt: new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Comment taskId must be positive');

it('rejects negative task id', function (): void {
    new Comment(id: 1, taskId: -1, authorId: 1, body: 'x', createdAt: new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Comment taskId must be positive');

it('rejects zero author id', function (): void {
    new Comment(id: 1, taskId: 1, authorId: 0, body: 'x', createdAt: new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Comment authorId must be positive');

it('rejects negative author id', function (): void {
    new Comment(id: 1, taskId: 1, authorId: -2, body: 'x', createdAt: new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Comment authorId must be positive');

it('rejects empty body', function (): void {
    new Comment(id: 1, taskId: 1, authorId: 1, body: '', createdAt: new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Comment body must not be empty');

it('rejects whitespace-only body', function (): void {
    new Comment(id: 1, taskId: 1, authorId: 1, body: '   ', createdAt: new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Comment body must not be empty');
