<?php

namespace Tests;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
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
}
