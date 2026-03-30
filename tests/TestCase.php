<?php

namespace Tests;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function registerUser(string $name, string $email, string $password): TestResponse
    {
        return $this
            ->withSession(['_token' => 'register-token'])
            ->post(route('register.store'), [
                '_token' => 'register-token',
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ]);
    }

    protected function loginUser(string $email, string $password): TestResponse
    {
        return $this
            ->withSession(['_token' => 'login-token'])
            ->post(route('login.store'), [
                '_token' => 'login-token',
                'email' => $email,
                'password' => $password,
            ]);
    }

    protected function createProject(string $name, string $description): TestResponse
    {
        return $this
            ->withSession(['_token' => 'project-token'])
            ->post(route('projects.store'), [
                '_token' => 'project-token',
                'name' => $name,
                'description' => $description,
            ]);
    }

    protected function addProjectMembers(Project $project, User ...$users): void
    {
        foreach ($users as $user) {
            ProjectMember::factory()->for($project)->for($user)->create();
        }
    }

    /**
     * @return array{project: Project, creator: User, assignee: User}
     */
    protected function createTaskParticipantContext(bool $memberAssignee = true): array
    {
        $project = Project::factory()->create();
        $creator = $project->owner;
        $assignee = User::factory()->create();

        $this->addProjectMembers($project, $creator);

        if ($memberAssignee) {
            $this->addProjectMembers($project, $assignee);
        }

        return [
            'project' => $project,
            'creator' => $creator,
            'assignee' => $assignee,
        ];
    }

    protected function createProjectForMember(User $user, string $name, ?string $description = null): Project
    {
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'name' => $name,
            'description' => $description,
        ]);

        $this->addProjectMembers($project, $user);

        return $project;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function makeTaskPayload(array $overrides = []): array
    {
        return array_replace([
            '_token' => 'task-token',
            'title' => 'Prepare launch checklist',
            'description' => 'Capture all release blockers for the first cut.',
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
            'due_date' => '2026-04-10',
            'assignee_id' => null,
        ], $overrides);
    }

    protected function assertTaskRelationships(
        Task $task,
        Project $project,
        User $creator,
        ?User $assignee = null,
    ): void {
        $this->assertTrue($task->project->is($project));
        $this->assertTrue($task->creator->is($creator));
        $this->assertSame($assignee?->id, $task->assignee?->id);
    }

    protected function assertTaskDueDate(Task $task, string $expectedDate): void
    {
        $this->assertInstanceOf(CarbonImmutable::class, $task->due_date);
        $this->assertSame($expectedDate, $task->due_date->toDateString());
    }
}
