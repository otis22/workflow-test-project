<?php

namespace Tests\Unit\Application\Tasks;

use App\Application\Tasks\Exceptions\InvalidTaskParticipant;
use App\Application\Tasks\TaskData;
use App\Application\Tasks\UpdateTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_a_task_for_a_project_member(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);
        $newAssignee = User::factory()->create();

        $this->addProjectMembers($task->project, $newAssignee);

        $task = app(UpdateTask::class)(
            task: $task,
            actor: $creator,
            data: new TaskData(
                title: 'Finalize launch checklist',
                description: 'Capture release blockers and ownership.',
                status: Task::STATUS_IN_PROGRESS,
                priority: Task::PRIORITY_MEDIUM,
                dueDate: '2026-04-12',
                assignee: $newAssignee,
            ),
        );

        $this->assertSame('Finalize launch checklist', $task->title);
        $this->assertSame(Task::STATUS_IN_PROGRESS, $task->status);
        $this->assertTaskRelationships($task, $task->project, $creator, $newAssignee);
        $this->assertTaskDueDate($task, '2026-04-12');
    }

    public function test_it_rejects_a_non_member_assignee_when_updating(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);
        $outsider = User::factory()->create();

        $this->expectException(InvalidTaskParticipant::class);
        $this->expectExceptionMessage('The task assignee must belong to the project.');

        app(UpdateTask::class)(
            task: $task,
            actor: $creator,
            data: new TaskData(
                title: 'Finalize launch checklist',
                description: 'Capture release blockers and ownership.',
                status: Task::STATUS_IN_PROGRESS,
                priority: Task::PRIORITY_MEDIUM,
                dueDate: null,
                assignee: $outsider,
            ),
        );
    }
}
