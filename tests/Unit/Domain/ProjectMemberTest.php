<?php

declare(strict_types=1);

use App\Domain\Project\ProjectMember;

it('creates a project member with valid data', function (): void {
    $now = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $member = new ProjectMember(
        id: 1,
        projectId: 10,
        userId: 5,
        createdAt: $now,
    );

    expect($member->id)->toBe(1)
        ->and($member->projectId)->toBe(10)
        ->and($member->userId)->toBe(5)
        ->and($member->createdAt)->toEqual($now);
});

it('rejects zero project id', function (): void {
    $now = new DateTimeImmutable;
    new ProjectMember(id: 1, projectId: 0, userId: 1, createdAt: $now);
})->throws(InvalidArgumentException::class, 'ProjectMember projectId must be positive');

it('rejects negative project id', function (): void {
    $now = new DateTimeImmutable;
    new ProjectMember(id: 1, projectId: -1, userId: 1, createdAt: $now);
})->throws(InvalidArgumentException::class, 'ProjectMember projectId must be positive');

it('rejects zero user id', function (): void {
    $now = new DateTimeImmutable;
    new ProjectMember(id: 1, projectId: 1, userId: 0, createdAt: $now);
})->throws(InvalidArgumentException::class, 'ProjectMember userId must be positive');

it('rejects negative user id', function (): void {
    $now = new DateTimeImmutable;
    new ProjectMember(id: 1, projectId: 1, userId: -3, createdAt: $now);
})->throws(InvalidArgumentException::class, 'ProjectMember userId must be positive');
