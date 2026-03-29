<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Project\Project;
use App\Domain\Shared\DomainRuleViolation;
use App\Domain\Task\Task;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function test_task_creator_must_be_project_member(): void
    {
        $project = Project::create(
            id: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
        );

        $this->expectException(DomainRuleViolation::class);

        Task::create(
            id: 'task-1',
            project: $project,
            creatorId: 'stranger',
            assigneeId: null,
            title: 'Bootstrap',
            description: null,
            status: TaskStatus::Todo,
            priority: TaskPriority::Medium,
        );
    }

    public function test_task_assignee_must_be_project_member_when_present(): void
    {
        $project = Project::create(
            id: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
        );

        $this->expectException(DomainRuleViolation::class);

        Task::create(
            id: 'task-1',
            project: $project,
            creatorId: 'owner-1',
            assigneeId: 'stranger',
            title: 'Bootstrap',
            description: null,
            status: TaskStatus::Todo,
            priority: TaskPriority::Medium,
        );
    }

    public function test_task_can_be_created_and_reassigned_between_project_members(): void
    {
        $project = Project::create(
            id: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
        );
        $project->addMember('user-2');

        $task = Task::create(
            id: 'task-1',
            project: $project,
            creatorId: 'owner-1',
            assigneeId: null,
            title: 'Bootstrap',
            description: 'Set up the project',
            status: TaskStatus::Todo,
            priority: TaskPriority::High,
        );

        $task->reassign($project, 'user-2');
        $task->changeStatus(TaskStatus::InProgress);

        $this->assertSame('user-2', $task->assigneeId);
        $this->assertSame(TaskStatus::InProgress, $task->status);
    }
}
