<?php

namespace Tests\Unit\Application\Tasks;

use App\Application\Tasks\TaskData;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskDataTest extends TestCase
{
    public function test_it_maps_persistence_attributes(): void
    {
        $assignee = User::factory()->make(['id' => 77]);

        $data = new TaskData(
            title: 'Prepare launch checklist',
            description: 'Capture all release blockers for the first cut.',
            status: Task::STATUS_IN_PROGRESS,
            priority: Task::PRIORITY_HIGH,
            dueDate: '2026-04-10 15:45:00',
            assignee: $assignee,
        );

        $this->assertSame([
            'title' => 'Prepare launch checklist',
            'description' => 'Capture all release blockers for the first cut.',
            'status' => Task::STATUS_IN_PROGRESS,
            'priority' => Task::PRIORITY_HIGH,
            'due_date' => '2026-04-10',
            'assignee_id' => 77,
        ], $data->toPersistenceAttributes());
    }

    public function test_it_maps_nullable_fields_without_an_assignee(): void
    {
        $data = new TaskData(
            title: 'Prepare launch checklist',
            description: null,
            status: Task::STATUS_TODO,
            priority: Task::PRIORITY_MEDIUM,
            dueDate: null,
            assignee: null,
        );

        $this->assertSame([
            'title' => 'Prepare launch checklist',
            'description' => null,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => null,
            'assignee_id' => null,
        ], $data->toPersistenceAttributes());
    }
}
