<?php

declare(strict_types=1);

use App\Domain\Project\ProjectMember;
use App\Domain\Project\ProjectMemberRepository;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedMembershipFixture(string $ownerEmail = 'owner@example.com', string $projectName = 'P'): array
{
    $owner = UserModel::query()->create([
        'name' => 'Owner',
        'email' => $ownerEmail,
        'password_hash' => 'hash',
    ]);
    $project = ProjectModel::query()->create([
        'owner_id' => $owner->id,
        'name' => $projectName,
        'description' => '',
    ]);

    return ['userId' => (int) $owner->id, 'projectId' => (int) $project->id];
}

function makeDomainMember(int $projectId, int $userId, int $id = 0): ProjectMember
{
    return new ProjectMember(
        id: $id,
        projectId: $projectId,
        userId: $userId,
        createdAt: new DateTimeImmutable('2026-04-09T10:00:00Z'),
    );
}

it('saves a new member and returns entity with assigned id', function (): void {
    /** @var ProjectMemberRepository $repo */
    $repo = app(ProjectMemberRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedMembershipFixture();

    $saved = $repo->save(makeDomainMember($projectId, $userId));

    expect($saved)->toBeInstanceOf(ProjectMember::class)
        ->and($saved->id)->toBeGreaterThan(0)
        ->and($saved->projectId)->toBe($projectId)
        ->and($saved->userId)->toBe($userId);
});

it('finds a member by project and user ids', function (): void {
    /** @var ProjectMemberRepository $repo */
    $repo = app(ProjectMemberRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedMembershipFixture();
    $repo->save(makeDomainMember($projectId, $userId));

    $found = $repo->findByProjectAndUser($projectId, $userId);

    expect($found)->toBeInstanceOf(ProjectMember::class)
        ->and($found->projectId)->toBe($projectId)
        ->and($found->userId)->toBe($userId);
});

it('returns null from findByProjectAndUser when no membership exists', function (): void {
    /** @var ProjectMemberRepository $repo */
    $repo = app(ProjectMemberRepository::class);
    ['projectId' => $projectId] = seedMembershipFixture();

    expect($repo->findByProjectAndUser($projectId, 9999))->toBeNull();
});

it('lists project ids for a user', function (): void {
    /** @var ProjectMemberRepository $repo */
    $repo = app(ProjectMemberRepository::class);
    ['userId' => $userId, 'projectId' => $p1] = seedMembershipFixture(ownerEmail: 'a@example.com', projectName: 'P1');
    ['projectId' => $p2] = seedMembershipFixture(ownerEmail: 'b@example.com', projectName: 'P2');

    $repo->save(makeDomainMember($p1, $userId));
    $repo->save(makeDomainMember($p2, $userId));

    $ids = $repo->projectIdsForUser($userId);

    expect($ids)->toEqualCanonicalizing([$p1, $p2]);
});

it('returns empty list from projectIdsForUser when user has no memberships', function (): void {
    /** @var ProjectMemberRepository $repo */
    $repo = app(ProjectMemberRepository::class);

    expect($repo->projectIdsForUser(9999))->toBe([]);
});

it('throws when saving a member with a positive id that does not exist', function (): void {
    /** @var ProjectMemberRepository $repo */
    $repo = app(ProjectMemberRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedMembershipFixture();

    $stale = makeDomainMember($projectId, $userId, id: 999);
    $repo->save($stale);
})->throws(RuntimeException::class, 'ProjectMember with id 999 not found for update');

it('preserves createdAt value and type on round-trip', function (): void {
    /** @var ProjectMemberRepository $repo */
    $repo = app(ProjectMemberRepository::class);
    ['userId' => $userId, 'projectId' => $projectId] = seedMembershipFixture();
    $saved = $repo->save(makeDomainMember($projectId, $userId));

    $loaded = $repo->findByProjectAndUser($projectId, $userId);

    expect($loaded->id)->toBe($saved->id)
        ->and($loaded->projectId)->toBe($projectId)
        ->and($loaded->userId)->toBe($userId)
        ->and($loaded->createdAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($loaded->createdAt->format('U'))->toBe($saved->createdAt->format('U'));
});
