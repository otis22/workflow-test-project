<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Comment\Comment;
use App\Domain\Project\Project;
use App\Domain\Shared\DomainRuleViolation;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function test_comment_author_must_be_project_member(): void
    {
        $project = Project::create(
            id: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
        );

        $this->expectException(DomainRuleViolation::class);

        Comment::add(
            id: 'comment-1',
            project: $project,
            taskId: 'task-1',
            authorId: 'stranger',
            body: 'Looks good',
            createdAt: new DateTimeImmutable('2026-03-29T12:00:00+00:00'),
        );
    }

    public function test_comment_can_be_added_by_project_member(): void
    {
        $project = Project::create(
            id: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
        );

        $comment = Comment::add(
            id: 'comment-1',
            project: $project,
            taskId: 'task-1',
            authorId: 'owner-1',
            body: 'Looks good',
            createdAt: new DateTimeImmutable('2026-03-29T12:00:00+00:00'),
        );

        $this->assertSame('comment-1', $comment->id);
        $this->assertSame('owner-1', $comment->authorId);
        $this->assertSame('Looks good', $comment->body);
    }
}
