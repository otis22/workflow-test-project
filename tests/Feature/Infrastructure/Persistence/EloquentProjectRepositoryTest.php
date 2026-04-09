<?php

declare(strict_types=1);

use App\Domain\Project\Project;
use App\Domain\Project\ProjectRepository;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeDomainProject(int $id = 0, ?int $ownerId = null): Project
{
    $now = new DateTimeImmutable('2026-04-09T10:00:00Z');

    return new Project(
        id: $id,
        ownerId: $ownerId ?? seedProjectOwner(),
        name: 'TaskFlow',
        description: 'MVP project',
        createdAt: $now,
        updatedAt: $now,
    );
}

function seedProjectOwner(string $email = 'owner@example.com'): int
{
    $model = UserModel::query()->create([
        'name' => 'Owner',
        'email' => $email,
        'password_hash' => 'hash',
    ]);

    return (int) $model->id;
}

it('saves a new domain project and returns entity with assigned id', function (): void {
    /** @var ProjectRepository $repo */
    $repo = app(ProjectRepository::class);

    $saved = $repo->save(makeDomainProject());

    expect($saved)->toBeInstanceOf(Project::class)
        ->and($saved->id)->toBeGreaterThan(0)
        ->and($saved->name)->toBe('TaskFlow')
        ->and($saved->description)->toBe('MVP project');
});

it('finds a project by id, returning a domain entity', function (): void {
    /** @var ProjectRepository $repo */
    $repo = app(ProjectRepository::class);
    $saved = $repo->save(makeDomainProject());

    $found = $repo->findById($saved->id);

    expect($found)->toBeInstanceOf(Project::class)
        ->and($found->id)->toBe($saved->id)
        ->and($found->name)->toBe('TaskFlow');
});

it('returns null from findById when project does not exist', function (): void {
    /** @var ProjectRepository $repo */
    $repo = app(ProjectRepository::class);

    expect($repo->findById(999))->toBeNull();
});

it('updates an existing project and preserves createdAt while bumping updatedAt', function (): void {
    /** @var ProjectRepository $repo */
    $repo = app(ProjectRepository::class);
    $saved = $repo->save(makeDomainProject());
    $originalCreatedAt = $saved->createdAt;

    $newUpdatedAt = new DateTimeImmutable('2026-04-09T11:00:00Z');
    $updated = $saved->withName('TaskFlow Renamed', $newUpdatedAt);
    $result = $repo->save($updated);

    expect($result->id)->toBe($saved->id)
        ->and($result->name)->toBe('TaskFlow Renamed')
        ->and($result->createdAt->format('U'))->toBe($originalCreatedAt->format('U'))
        ->and($result->updatedAt->format('U'))->toBe($newUpdatedAt->format('U'));

    $reloaded = $repo->findById($saved->id);
    expect($reloaded->name)->toBe('TaskFlow Renamed')
        ->and($reloaded->updatedAt->format('U'))->toBe($newUpdatedAt->format('U'));
});

it('throws when saving a project with a positive id that does not exist', function (): void {
    /** @var ProjectRepository $repo */
    $repo = app(ProjectRepository::class);

    $stale = makeDomainProject(id: 999);

    $repo->save($stale);
})->throws(RuntimeException::class, 'Project with id 999 not found for update');

it('preserves createdAt and updatedAt as DateTimeImmutable on round-trip', function (): void {
    /** @var ProjectRepository $repo */
    $repo = app(ProjectRepository::class);
    $saved = $repo->save(makeDomainProject());

    $loaded = $repo->findById($saved->id);

    expect($loaded->createdAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($loaded->updatedAt)->toBeInstanceOf(DateTimeImmutable::class);
});
