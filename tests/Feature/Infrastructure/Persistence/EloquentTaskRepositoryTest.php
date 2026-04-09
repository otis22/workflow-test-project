<?php

declare(strict_types=1);

use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedTaskFixture(string $ownerEmail = 'owner@example.com'): array
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

    return ['userId' => (int) $user->id, 'projectId' => (int) $project->id];
}

function makeDomainTask(
    int $projectId,
    int $creatorId,
    int $id = 0,
    ?int $assigneeId = null,
    Status $status = Status::Todo,
    Priority $priority = Priority::Medium,
    ?DueDate $dueDate = null,
): Task {
    $now = new DateTimeImmutable('2026-04-09T10:00:00Z');

    return new Task(
        id: $id,
        projectId: $projectId,
        creatorId: $creatorId,
        assigneeId: $assigneeId,
        title: 'Do the thing',
        description: 'desc',
        status: $status,
        priority: $priority,
        dueDate: $dueDate,
        createdAt: $now,
        updatedAt: $now,
    );
}

it('saves a new task and returns entity with assigned id', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $saved = $repo->save(makeDomainTask($projectId, $userId));

    expect($saved)->toBeInstanceOf(Task::class)
        ->and($saved->id)->toBeGreaterThan(0)
        ->and($saved->projectId)->toBe($projectId)
        ->and($saved->creatorId)->toBe($userId)
        ->and($saved->status)->toBe(Status::Todo)
        ->and($saved->priority)->toBe(Priority::Medium);
});

it('preserves all mapped fields on round-trip including enums, nullable assignee and due date', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();
    $due = new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'));

    $saved = $repo->save(makeDomainTask(
        projectId: $projectId,
        creatorId: $userId,
        assigneeId: $userId,
        status: Status::InProgress,
        priority: Priority::High,
        dueDate: $due,
    ));

    $loaded = $repo->findById($saved->id);

    expect($loaded->id)->toBe($saved->id)
        ->and($loaded->projectId)->toBe($projectId)
        ->and($loaded->creatorId)->toBe($userId)
        ->and($loaded->assigneeId)->toBe($userId)
        ->and($loaded->status)->toBe(Status::InProgress)
        ->and($loaded->priority)->toBe(Priority::High)
        ->and($loaded->dueDate)->toBeInstanceOf(DueDate::class)
        ->and($loaded->dueDate->value->format('U'))->toBe($due->value->format('U'))
        ->and($loaded->createdAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($loaded->updatedAt)->toBeInstanceOf(DateTimeImmutable::class);
});

it('stores null assignee and null due date', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $saved = $repo->save(makeDomainTask($projectId, $userId));
    $loaded = $repo->findById($saved->id);

    expect($loaded->assigneeId)->toBeNull()
        ->and($loaded->dueDate)->toBeNull();
});

it('returns null from findById when task does not exist', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);

    expect($repo->findById(999))->toBeNull();
});

it('updates an existing task and preserves createdAt while bumping updatedAt', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();
    $saved = $repo->save(makeDomainTask($projectId, $userId));

    $newUpdatedAt = new DateTimeImmutable('2026-04-10T10:00:00Z');
    $result = $repo->save($saved->withStatus(Status::Done, $newUpdatedAt));

    expect($result->id)->toBe($saved->id)
        ->and($result->status)->toBe(Status::Done)
        ->and($result->createdAt->format('U'))->toBe($saved->createdAt->format('U'))
        ->and($result->updatedAt->format('U'))->toBe($newUpdatedAt->format('U'));
});

it('throws when saving a task with a positive id that does not exist', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $stale = makeDomainTask($projectId, $userId, id: 999);
    $repo->save($stale);
})->throws(RuntimeException::class, 'Task with id 999 not found for update');

it('listByProject returns tasks ordered by id and only for the given project', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $p1] = seedTaskFixture(ownerEmail: 'a@example.com');
    ['projectId' => $p2] = seedTaskFixture(ownerEmail: 'b@example.com');

    $t1 = $repo->save(makeDomainTask($p1, $userId));
    $t2 = $repo->save(makeDomainTask($p1, $userId));
    $repo->save(makeDomainTask($p2, $userId));

    $result = $repo->listByProject($p1);
    $ids = array_map(fn (Task $t): int => $t->id, $result);

    expect($ids)->toBe([$t1->id, $t2->id]);
});

it('listByProject filters by status', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $todo = $repo->save(makeDomainTask($projectId, $userId, status: Status::Todo));
    $repo->save(makeDomainTask($projectId, $userId, status: Status::Done));

    $result = $repo->listByProject($projectId, status: Status::Todo);

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($todo->id);
});

it('listByProject filters by dueBefore and excludes tasks without dueDate', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $early = $repo->save(makeDomainTask($projectId, $userId, dueDate: new DueDate(new DateTimeImmutable('2026-05-01T00:00:00Z'))));
    $repo->save(makeDomainTask($projectId, $userId, dueDate: new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'))));
    $repo->save(makeDomainTask($projectId, $userId)); // no due date

    $result = $repo->listByProject($projectId, dueBefore: new DateTimeImmutable('2026-05-15T00:00:00Z'));

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($early->id);
});

it('listByProject dueBefore uses strict less-than semantics at the boundary', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $boundary = new DateTimeImmutable('2026-05-15T00:00:00Z');
    $repo->save(makeDomainTask($projectId, $userId, dueDate: new DueDate($boundary)));

    // Strict < excludes the task whose due date equals the boundary.
    $result = $repo->listByProject($projectId, dueBefore: $boundary);

    expect($result)->toBe([]);
});

it('listByProject combines status and dueBefore filters', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $matching = $repo->save(makeDomainTask(
        $projectId,
        $userId,
        status: Status::Todo,
        dueDate: new DueDate(new DateTimeImmutable('2026-05-01T00:00:00Z')),
    ));
    // same status, but due after the boundary
    $repo->save(makeDomainTask(
        $projectId,
        $userId,
        status: Status::Todo,
        dueDate: new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z')),
    ));
    // due before boundary but different status
    $repo->save(makeDomainTask(
        $projectId,
        $userId,
        status: Status::Done,
        dueDate: new DueDate(new DateTimeImmutable('2026-05-01T00:00:00Z')),
    ));

    $result = $repo->listByProject(
        $projectId,
        status: Status::Todo,
        dueBefore: new DateTimeImmutable('2026-05-15T00:00:00Z'),
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($matching->id);
});

it('listByAssignee returns only tasks assigned to the user, ordered by id', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedTaskFixture();

    $a = $repo->save(makeDomainTask($projectId, $userId, assigneeId: $userId));
    $repo->save(makeDomainTask($projectId, $userId, assigneeId: null));
    $b = $repo->save(makeDomainTask($projectId, $userId, assigneeId: $userId));

    $result = $repo->listByAssignee($userId);
    $ids = array_map(fn (Task $t): int => $t->id, $result);

    expect($ids)->toBe([$a->id, $b->id]);
});

it('listByAssignee returns empty list when user has no assigned tasks', function (): void {
    /** @var TaskRepository $repo */
    $repo = app(TaskRepository::class);

    expect($repo->listByAssignee(9999))->toBe([]);
});
