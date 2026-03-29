<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\Comment\AddCommentService;
use App\Application\Project\CreateProjectService;
use App\Application\Task\CreateTaskService;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use App\Infrastructure\Persistence\InMemory\InMemoryCommentRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryProjectRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryTaskRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ApplicationServicesTest extends TestCase
{
    public function test_create_project_service_persists_project_with_owner_membership(): void
    {
        $projects = new InMemoryProjectRepository;
        $service = new CreateProjectService($projects);

        $project = $service->execute(
            projectId: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
            description: 'Workflow project',
        );

        $this->assertSame($project, $projects->getById('project-1'));
        $this->assertTrue($project->hasMember('owner-1'));
    }

    public function test_create_task_service_persists_task_for_existing_project(): void
    {
        $projects = new InMemoryProjectRepository;
        $tasks = new InMemoryTaskRepository;
        $project = (new CreateProjectService($projects))->execute(
            projectId: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
        );
        $project->addMember('user-2');

        $task = (new CreateTaskService($projects, $tasks))->execute(
            taskId: 'task-1',
            projectId: 'project-1',
            creatorId: 'owner-1',
            assigneeId: 'user-2',
            title: 'Implement domain',
            description: 'Stage 2',
            status: TaskStatus::Todo,
            priority: TaskPriority::High,
            dueDate: new DateTimeImmutable('2026-04-01T12:00:00+00:00'),
        );

        $this->assertSame($task, $tasks->getById('task-1'));
        $this->assertSame('user-2', $task->assigneeId);
    }

    public function test_add_comment_service_persists_comment_for_existing_project(): void
    {
        $projects = new InMemoryProjectRepository;
        $comments = new InMemoryCommentRepository;
        (new CreateProjectService($projects))->execute(
            projectId: 'project-1',
            ownerId: 'owner-1',
            name: 'Roadmap',
        );

        $comment = (new AddCommentService($projects, $comments))->execute(
            commentId: 'comment-1',
            projectId: 'project-1',
            taskId: 'task-1',
            authorId: 'owner-1',
            body: 'Ready for review',
            createdAt: new DateTimeImmutable('2026-03-29T12:00:00+00:00'),
        );

        $this->assertCount(1, $comments->all());
        $this->assertSame('comment-1', $comment->id);
    }
}
