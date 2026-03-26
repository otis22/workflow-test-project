<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Comment;

use App\Domain\Comment\Comment;
use App\Domain\Project\Project;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    private function makeProject(int $ownerId = 1, array $members = []): Project
    {
        $project = new Project(id: 1, ownerId: $ownerId, name: 'Test', description: '');
        foreach ($members as $memberId) {
            $project->addMember($memberId);
        }

        return $project;
    }

    #[Test]
    public function it_creates_comment_by_project_member(): void
    {
        $project = $this->makeProject(ownerId: 1);

        $comment = Comment::create(
            id: 1,
            taskId: 10,
            authorId: 1,
            body: 'Looks good!',
            project: $project,
        );

        $this->assertSame(1, $comment->id);
        $this->assertSame(10, $comment->taskId);
        $this->assertSame(1, $comment->authorId);
        $this->assertSame('Looks good!', $comment->body);
    }

    #[Test]
    public function it_rejects_comment_by_non_member(): void
    {
        $this->expectException(DomainException::class);

        Comment::create(
            id: 1,
            taskId: 10,
            authorId: 99,
            body: 'I should not be here',
            project: $this->makeProject(ownerId: 1),
        );
    }

    #[Test]
    public function it_rejects_empty_body(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Comment::create(
            id: 1,
            taskId: 10,
            authorId: 1,
            body: '',
            project: $this->makeProject(ownerId: 1),
        );
    }

    #[Test]
    public function it_rejects_whitespace_only_body(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Comment::create(
            id: 1,
            taskId: 10,
            authorId: 1,
            body: '   ',
            project: $this->makeProject(ownerId: 1),
        );
    }
}
