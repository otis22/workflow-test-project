<?php

declare(strict_types=1);

use App\Domain\Project\Project;

it('creates a project with valid data', function (): void {
    $now = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $project = new Project(
        id: 10,
        ownerId: 1,
        name: 'TaskFlow',
        description: 'MVP project',
        createdAt: $now,
        updatedAt: $now,
    );

    expect($project->id)->toBe(10)
        ->and($project->ownerId)->toBe(1)
        ->and($project->name)->toBe('TaskFlow')
        ->and($project->description)->toBe('MVP project')
        ->and($project->createdAt)->toEqual($now)
        ->and($project->updatedAt)->toEqual($now);
});

it('allows empty description', function (): void {
    $now = new DateTimeImmutable;
    $project = new Project(
        id: 1,
        ownerId: 1,
        name: 'Foo',
        description: '',
        createdAt: $now,
        updatedAt: $now,
    );

    expect($project->description)->toBe('');
});

it('rejects empty name', function (): void {
    $now = new DateTimeImmutable;
    new Project(
        id: 1,
        ownerId: 1,
        name: '',
        description: '',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'Project name must not be empty');

it('rejects whitespace-only name', function (): void {
    $now = new DateTimeImmutable;
    new Project(
        id: 1,
        ownerId: 1,
        name: '   ',
        description: '',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'Project name must not be empty');

it('rejects zero owner id', function (): void {
    $now = new DateTimeImmutable;
    new Project(
        id: 1,
        ownerId: 0,
        name: 'Foo',
        description: '',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'Project must have an owner');

it('rejects negative owner id', function (): void {
    $now = new DateTimeImmutable;
    new Project(
        id: 1,
        ownerId: -5,
        name: 'Foo',
        description: '',
        createdAt: $now,
        updatedAt: $now,
    );
})->throws(InvalidArgumentException::class, 'Project must have an owner');

it('returns a new instance with updated name and bumps updatedAt', function (): void {
    $created = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $updated = new DateTimeImmutable('2026-01-02T00:00:00Z');
    $project = new Project(
        id: 1,
        ownerId: 1,
        name: 'Foo',
        description: 'd',
        createdAt: $created,
        updatedAt: $created,
    );

    $renamed = $project->withName('Bar', $updated);

    expect($renamed)->not->toBe($project)
        ->and($renamed->name)->toBe('Bar')
        ->and($renamed->description)->toBe('d')
        ->and($renamed->createdAt)->toEqual($created)
        ->and($renamed->updatedAt)->toEqual($updated)
        ->and($project->name)->toBe('Foo')
        ->and($project->updatedAt)->toEqual($created);
});

it('returns a new instance with updated description and bumps updatedAt', function (): void {
    $created = new DateTimeImmutable('2026-01-01T00:00:00Z');
    $updated = new DateTimeImmutable('2026-01-02T00:00:00Z');
    $project = new Project(
        id: 1,
        ownerId: 1,
        name: 'Foo',
        description: 'old',
        createdAt: $created,
        updatedAt: $created,
    );

    $changed = $project->withDescription('new', $updated);

    expect($changed)->not->toBe($project)
        ->and($changed->description)->toBe('new')
        ->and($changed->name)->toBe('Foo')
        ->and($changed->createdAt)->toEqual($created)
        ->and($changed->updatedAt)->toEqual($updated)
        ->and($project->description)->toBe('old');
});

it('rejects empty name in withName', function (): void {
    $now = new DateTimeImmutable;
    $project = new Project(
        id: 1,
        ownerId: 1,
        name: 'Foo',
        description: '',
        createdAt: $now,
        updatedAt: $now,
    );

    $project->withName('   ', $now);
})->throws(InvalidArgumentException::class, 'Project name must not be empty');

it('rejects updatedAt earlier than createdAt', function (): void {
    $created = new DateTimeImmutable('2026-01-02T00:00:00Z');
    $updated = new DateTimeImmutable('2026-01-01T23:59:59Z');
    new Project(
        id: 1,
        ownerId: 1,
        name: 'Foo',
        description: '',
        createdAt: $created,
        updatedAt: $updated,
    );
})->throws(InvalidArgumentException::class, 'Project updatedAt must not be earlier than createdAt');
