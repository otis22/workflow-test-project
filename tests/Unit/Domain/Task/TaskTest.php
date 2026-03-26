<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Task;

use App\Domain\Project\Project;
use App\Domain\Task\Task;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
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
    public function it_creates_task_with_valid_data(): void
    {
        $project = $this->makeProject(ownerId: 1);

        $task = Task::create(
            id: 1,
            project: $project,
            creatorId: 1,
            title: 'Fix bug',
            description: 'Fix the login bug',
        );

        $this->assertSame(1, $task->id);
        $this->assertSame(1, $task->projectId);
        $this->assertSame('Fix bug', $task->title);
        $this->assertSame(TaskStatus::Todo, $task->status);
        $this->assertSame(TaskPriority::Medium, $task->priority);
        $this->assertNull($task->assigneeId);
        $this->assertNull($task->dueDate);
    }

    #[Test]
    public function it_rejects_empty_title(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Task::create(
            id: 1,
            project: $this->makeProject(),
            creatorId: 1,
            title: '',
        );
    }

    #[Test]
    public function it_rejects_whitespace_only_title(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Task::create(
            id: 1,
            project: $this->makeProject(),
            creatorId: 1,
            title: '   ',
        );
    }

    #[Test]
    public function it_rejects_non_member_creator(): void
    {
        $this->expectException(DomainException::class);

        Task::create(
            id: 1,
            project: $this->makeProject(ownerId: 1),
            creatorId: 99,
            title: 'Task',
        );
    }

    #[Test]
    public function it_changes_status(): void
    {
        $task = Task::create(id: 1, project: $this->makeProject(), creatorId: 1, title: 'Task');
        $task->changeStatus(TaskStatus::InProgress);

        $this->assertSame(TaskStatus::InProgress, $task->status);
    }

    #[Test]
    public function it_assigns_to_project_member(): void
    {
        $project = $this->makeProject(ownerId: 1, members: [2]);
        $task = Task::create(id: 1, project: $project, creatorId: 1, title: 'Task');
        $task->assignTo(2, $project);

        $this->assertSame(2, $task->assigneeId);
    }

    #[Test]
    public function it_rejects_assign_to_non_member(): void
    {
        $this->expectException(DomainException::class);

        $project = $this->makeProject(ownerId: 1);
        $task = Task::create(id: 1, project: $project, creatorId: 1, title: 'Task');
        $task->assignTo(99, $project);
    }

    #[Test]
    public function it_sets_due_date(): void
    {
        $task = Task::create(id: 1, project: $this->makeProject(), creatorId: 1, title: 'Task');
        $date = new \DateTimeImmutable('2026-04-01');
        $task->setDueDate($date);

        $this->assertEquals($date, $task->dueDate);
    }

    #[Test]
    public function it_changes_priority(): void
    {
        $task = Task::create(id: 1, project: $this->makeProject(), creatorId: 1, title: 'Task');
        $task->changePriority(TaskPriority::High);

        $this->assertSame(TaskPriority::High, $task->priority);
    }
}
