<?php

declare(strict_types=1);

use App\Application\Project\CreateProject;
use App\Application\Project\Exception\OwnerNotFoundException;
use App\Application\User\RegisterUser;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;
use Tests\Support\Fakes\InMemoryUserRepository;

function makeCreateProjectFixture(): array
{
    $users = new InMemoryUserRepository;
    $projects = new InMemoryProjectRepository;
    $members = new InMemoryProjectMemberRepository;
    $clock = new FakeClock(new DateTimeImmutable('2026-04-07T12:00:00Z'));

    $register = new RegisterUser($users, new FakePasswordHasher, $clock);
    $owner = $register->execute('Alice', 'alice@example.com', 'super-secret');

    $useCase = new CreateProject($projects, $members, $users, $clock);

    return [$useCase, $owner, $projects, $members];
}

it('creates a project and adds owner as member', function (): void {
    [$useCase, $owner, $projects, $members] = makeCreateProjectFixture();

    $project = $useCase->execute($owner->id, 'TaskFlow', 'MVP project');

    expect($project->id)->toBe(1)
        ->and($project->ownerId)->toBe($owner->id)
        ->and($project->name)->toBe('TaskFlow')
        ->and($project->description)->toBe('MVP project');

    expect($projects->findById($project->id))->not->toBeNull();
    expect($members->findByProjectAndUser($project->id, $owner->id))->not->toBeNull();
});

it('rejects unknown owner', function (): void {
    [$useCase] = makeCreateProjectFixture();

    $useCase->execute(999, 'TaskFlow', 'desc');
})->throws(OwnerNotFoundException::class);

it('rejects empty project name via domain validation', function (): void {
    [$useCase, $owner] = makeCreateProjectFixture();

    $useCase->execute($owner->id, '   ', 'desc');
})->throws(InvalidArgumentException::class, 'Project name must not be empty');

it('uses clock timestamps for project and membership', function (): void {
    [$useCase, $owner, $projects, $members] = makeCreateProjectFixture();
    $expectedAt = new DateTimeImmutable('2026-04-07T12:00:00Z');

    $project = $useCase->execute($owner->id, 'P', 'd');

    expect($project->createdAt)->toEqual($expectedAt)
        ->and($project->updatedAt)->toEqual($expectedAt);

    $member = $members->findByProjectAndUser($project->id, $owner->id);
    expect($member->createdAt)->toEqual($expectedAt);
});
