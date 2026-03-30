<?php

namespace Tests\Unit\Application\Tasks;

use App\Application\Tasks\CreateTask;
use App\Application\Tasks\Exceptions\InvalidTaskParticipant;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_task_for_a_project_member(): void
    {
        ['project' => $project, 'creator' => $creator, 'assignee' => $assignee] = $this->createTaskParticipantContext();

        $task = app(CreateTask::class)(
            project: $project,
            creator: $creator,
            title: 'Prepare launch checklist',
            description: 'Capture all release blockers for the first cut.',
            status: Task::STATUS_TODO,
            priority: Task::PRIORITY_HIGH,
            dueDate: '2026-04-10',
            assignee: $assignee,
        );

        $this->assertSame('Prepare launch checklist', $task->title);
        $this->assertTaskRelationships($task, $project, $creator, $assignee);
        $this->assertTaskDueDate($task, '2026-04-10');
    }

    public function test_it_rejects_a_non_member_assignee(): void
    {
        ['project' => $project, 'creator' => $creator, 'assignee' => $assignee] = $this->createTaskParticipantContext(false);

        $this->expectException(InvalidTaskParticipant::class);
        $this->expectExceptionMessage('The task assignee must belong to the project.');

        app(CreateTask::class)(
            project: $project,
            creator: $creator,
            title: 'Prepare launch checklist',
            description: 'Capture all release blockers for the first cut.',
            status: Task::STATUS_TODO,
            priority: Task::PRIORITY_HIGH,
            dueDate: null,
            assignee: $assignee,
        );
    }
}
