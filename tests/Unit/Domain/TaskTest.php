<?php

declare(strict_types=1);

use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\Task;

function makeTask(array $overrides = []): Task
{
    $now = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $defaults = [
        'id' => 1,
        'projectId' => 10,
        'creatorId' => 5,
        'assigneeId' => null,
        'title' => 'Do the thing',
        'description' => '',
        'status' => Status::Todo,
        'priority' => Priority::Medium,
        'dueDate' => null,
        'createdAt' => $now,
        'updatedAt' => $now,
    ];
    $args = array_merge($defaults, $overrides);

    return new Task(
        id: $args['id'],
        projectId: $args['projectId'],
        creatorId: $args['creatorId'],
        assigneeId: $args['assigneeId'],
        title: $args['title'],
        description: $args['description'],
        status: $args['status'],
        priority: $args['priority'],
        dueDate: $args['dueDate'],
        createdAt: $args['createdAt'],
        updatedAt: $args['updatedAt'],
    );
}

it('creates a task with valid data', function (): void {
    $now = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $due = new DueDate(new DateTimeImmutable('2026-02-01T00:00:00Z'));
    $task = makeTask([
        'assigneeId' => 7,
        'description' => 'details',
        'status' => Status::InProgress,
        'priority' => Priority::High,
        'dueDate' => $due,
        'createdAt' => $now,
        'updatedAt' => $now,
    ]);

    expect($task->id)->toBe(1)
        ->and($task->projectId)->toBe(10)
        ->and($task->creatorId)->toBe(5)
        ->and($task->assigneeId)->toBe(7)
        ->and($task->title)->toBe('Do the thing')
        ->and($task->description)->toBe('details')
        ->and($task->status)->toBe(Status::InProgress)
        ->and($task->priority)->toBe(Priority::High)
        ->and($task->dueDate)->toBe($due)
        ->and($task->createdAt)->toEqual($now)
        ->and($task->updatedAt)->toEqual($now);
});

it('allows null assignee and null due date', function (): void {
    $task = makeTask();
    expect($task->assigneeId)->toBeNull()
        ->and($task->dueDate)->toBeNull();
});

it('rejects zero project id', fn (): Task => makeTask(['projectId' => 0]))
    ->throws(InvalidArgumentException::class, 'Task projectId must be positive');

it('rejects zero creator id', fn (): Task => makeTask(['creatorId' => 0]))
    ->throws(InvalidArgumentException::class, 'Task creatorId must be positive');

it('rejects updatedAt earlier than createdAt', fn (): Task => makeTask([
    'createdAt' => new DateTimeImmutable('2026-01-02T00:00:00Z'),
    'updatedAt' => new DateTimeImmutable('2026-01-01T23:59:59Z'),
]))->throws(InvalidArgumentException::class, 'Task updatedAt must not be earlier than createdAt');

it('rejects zero assignee id', fn (): Task => makeTask(['assigneeId' => 0]))
    ->throws(InvalidArgumentException::class, 'Task assigneeId must be positive when set');

it('rejects negative assignee id', fn (): Task => makeTask(['assigneeId' => -1]))
    ->throws(InvalidArgumentException::class, 'Task assigneeId must be positive when set');

it('rejects empty title', fn (): Task => makeTask(['title' => '']))
    ->throws(InvalidArgumentException::class, 'Task title must not be empty');

it('rejects whitespace-only title', fn (): Task => makeTask(['title' => '   ']))
    ->throws(InvalidArgumentException::class, 'Task title must not be empty');

it('returns a new instance with updated title and bumps updatedAt', function (): void {
    $created = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $updated = new DateTimeImmutable('2026-01-02T00:00:00Z');
    $task = makeTask(['createdAt' => $created, 'updatedAt' => $created]);

    $changed = $task->withTitle('New title', $updated);

    expect($changed)->not->toBe($task)
        ->and($changed->title)->toBe('New title')
        ->and($changed->createdAt)->toEqual($created)
        ->and($changed->updatedAt)->toEqual($updated)
        ->and($task->title)->toBe('Do the thing');
});

it('rejects empty title in withTitle', function (): void {
    $task = makeTask();
    $task->withTitle('  ', new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Task title must not be empty');

it('returns a new instance with updated description', function (): void {
    $now = new DateTimeImmutable;
    $task = makeTask();
    $changed = $task->withDescription('details', $now);
    expect($changed->description)->toBe('details')
        ->and($task->description)->toBe('');
});

it('returns a new instance with updated status', function (): void {
    $now = new DateTimeImmutable;
    $task = makeTask();
    $changed = $task->withStatus(Status::Done, $now);
    expect($changed->status)->toBe(Status::Done)
        ->and($task->status)->toBe(Status::Todo);
});

it('returns a new instance with updated priority', function (): void {
    $now = new DateTimeImmutable;
    $task = makeTask();
    $changed = $task->withPriority(Priority::High, $now);
    expect($changed->priority)->toBe(Priority::High);
});

it('returns a new instance with assignee set and unset', function (): void {
    $now = new DateTimeImmutable;
    $task = makeTask();
    $assigned = $task->withAssignee(42, $now);
    expect($assigned->assigneeId)->toBe(42);

    $unassigned = $assigned->withAssignee(null, $now);
    expect($unassigned->assigneeId)->toBeNull();
});

it('rejects zero assignee in withAssignee', function (): void {
    makeTask()->withAssignee(0, new DateTimeImmutable);
})->throws(InvalidArgumentException::class, 'Task assigneeId must be positive when set');

it('returns a new instance with due date set and unset', function (): void {
    $now = new DateTimeImmutable;
    $due = new DueDate(new DateTimeImmutable('2026-03-01T00:00:00Z'));
    $task = makeTask();

    $withDue = $task->withDueDate($due, $now);
    expect($withDue->dueDate)->toBe($due);

    $withoutDue = $withDue->withDueDate(null, $now);
    expect($withoutDue->dueDate)->toBeNull();
});
