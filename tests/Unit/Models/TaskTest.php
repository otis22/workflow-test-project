<?php

namespace Tests\Unit\Models;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exposes_project_creator_and_assignee_relationships(): void
    {
        ['project' => $project, 'creator' => $creator, 'assignee' => $assignee] = $this->createTaskParticipantContext();

        $task = $this->makeTask($project, $creator, $assignee);

        $this->assertTrue($task->project->is($project));
        $this->assertTrue($task->creator->is($creator));
        $this->assertTrue($task->assignee->is($assignee));
    }

    public function test_it_casts_due_date_as_an_immutable_date(): void
    {
        ['project' => $project, 'creator' => $creator] = $this->createTaskParticipantContext(false);

        $task = $this->makeTask($project, $creator, null, [
            'due_date' => '2026-04-15',
        ]);

        $this->assertInstanceOf(CarbonImmutable::class, $task->due_date);
        $this->assertSame('2026-04-15', $task->due_date->toDateString());
    }

    public function test_it_defines_the_mvp_statuses_and_priorities(): void
    {
        $this->assertSame([
            Task::STATUS_TODO,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_DONE,
        ], Task::STATUSES);

        $this->assertSame([
            Task::PRIORITY_LOW,
            Task::PRIORITY_MEDIUM,
            Task::PRIORITY_HIGH,
        ], Task::PRIORITIES);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeTask(Project $project, User $creator, ?User $assignee = null, array $attributes = []): Task
    {
        $factory = Task::factory()
            ->for($project)
            ->for($creator, 'creator');

        if ($assignee instanceof User) {
            $factory = $factory->for($assignee, 'assignee');
        }

        return $factory->create($attributes);
    }
}
