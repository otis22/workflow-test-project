<?php

namespace Tests\Unit\Application\Tasks;

use App\Application\Tasks\EnsureTaskParticipantsBelongToProject;
use App\Application\Tasks\Exceptions\InvalidTaskParticipant;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureTaskParticipantsBelongToProjectTest extends TestCase
{
    use RefreshDatabase;

    private EnsureTaskParticipantsBelongToProject $guard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guard = new EnsureTaskParticipantsBelongToProject;
    }

    public function test_it_accepts_a_project_member_as_creator_and_assignee(): void
    {
        ['project' => $project, 'creator' => $creator, 'assignee' => $assignee] = $this->createTaskParticipantContext();

        ($this->guard)($project, $creator, $assignee);

        $this->addToAssertionCount(1);
    }

    public function test_it_rejects_a_creator_that_is_not_a_project_member(): void
    {
        $project = Project::factory()->create();
        $creator = User::factory()->create();

        $this->expectInvalidParticipant(
            'The task creator must belong to the project.',
            fn () => ($this->guard)($project, $creator),
        );
    }

    public function test_it_rejects_an_assignee_that_is_not_a_project_member(): void
    {
        ['project' => $project, 'creator' => $creator, 'assignee' => $assignee] = $this->createTaskParticipantContext(false);

        $this->expectInvalidParticipant(
            'The task assignee must belong to the project.',
            fn () => ($this->guard)($project, $creator, $assignee),
        );
    }

    /**
     * @param  \Closure(): mixed  $callback
     */
    private function expectInvalidParticipant(string $message, \Closure $callback): void
    {
        $this->expectException(InvalidTaskParticipant::class);
        $this->expectExceptionMessage($message);

        $callback();
    }
}
