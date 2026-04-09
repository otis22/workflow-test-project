<?php

declare(strict_types=1);

use App\Application\Project\Exception\ProjectNotFoundException;
use App\Application\Project\ShowProject;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectMember;
use Tests\Support\Fakes\InMemoryProjectMemberRepository;
use Tests\Support\Fakes\InMemoryProjectRepository;

function makeShowProjectFixture(): array
{
    $projects = new InMemoryProjectRepository;
    $members = new InMemoryProjectMemberRepository;
    $now = new DateTimeImmutable('2026-04-09T10:00:00Z');

    $project = $projects->save(new Project(
        id: 0,
        ownerId: 1,
        name: 'TaskFlow',
        description: 'MVP',
        createdAt: $now,
        updatedAt: $now,
    ));
    $members->save(new ProjectMember(
        id: 0,
        projectId: $project->id,
        userId: 1,
        createdAt: $now,
    ));

    return [
        'useCase' => new ShowProject($projects, $members),
        'projects' => $projects,
        'members' => $members,
        'project' => $project,
    ];
}

it('returns the project when actor is a member', function (): void {
    $ctx = makeShowProjectFixture();

    $result = $ctx['useCase']->execute(actorId: 1, projectId: $ctx['project']->id);

    expect($result)->toBeInstanceOf(Project::class)
        ->and($result->id)->toBe($ctx['project']->id)
        ->and($result->name)->toBe('TaskFlow');
});

it('throws ProjectNotFoundException for unknown project id', function (): void {
    $ctx = makeShowProjectFixture();

    $ctx['useCase']->execute(actorId: 1, projectId: 9999);
})->throws(ProjectNotFoundException::class);

it('throws NotAProjectMemberException when actor is not a member of an existing project', function (): void {
    $ctx = makeShowProjectFixture();

    $ctx['useCase']->execute(actorId: 42, projectId: $ctx['project']->id);
})->throws(NotAProjectMemberException::class);

it('checks project existence before membership, matching the current 404-before-403 ordering', function (): void {
    // An actor who is not a member AND queries an unknown project id should
    // see ProjectNotFoundException, not NotAProjectMemberException.
    // This pins the documented 2.r3 information disclosure until it is fixed.
    $ctx = makeShowProjectFixture();
    $ctx['useCase']->execute(actorId: 42, projectId: 9999);
})->throws(ProjectNotFoundException::class);
