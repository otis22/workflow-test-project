<?php

namespace Tests\Feature\Tasks;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CommentTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_can_add_a_comment_from_the_task_detail_page(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);

        $response = $this->postComment($creator, $task, 'Kickoff notes are ready for review.');

        $response->assertRedirect(route('projects.tasks.show', [$task->project, $task]));

        $this->assertDatabaseHas('comments', [
            'task_id' => $task->id,
            'author_id' => $creator->id,
            'body' => 'Kickoff notes are ready for review.',
        ]);

        $this
            ->actingAs($creator)
            ->get(route('projects.tasks.show', [$task->project, $task]))
            ->assertSee('Kickoff notes are ready for review.');
    }

    public function test_comment_submission_requires_a_body(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);

        $response = $this->postComment($creator, $task, '');

        $response
            ->assertRedirect(route('projects.tasks.show', [$task->project, $task]))
            ->assertSessionHasErrors('body');
    }

    public function test_non_member_cannot_add_a_comment(): void
    {
        ['task' => $task] = $this->createExistingProjectTask(false);

        $response = $this
            ->actingAs(User::factory()->create())
            ->withSession(['_token' => 'comment-token'])
            ->post(route('projects.tasks.comments.store', [$task->project, $task]), [
                '_token' => 'comment-token',
                'body' => 'This should be forbidden.',
            ]);

        $response->assertForbidden();
    }

    public function test_task_detail_page_shows_comments_in_chronological_order(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);

        Comment::query()->create([
            'task_id' => $task->id,
            'author_id' => $creator->id,
            'body' => 'First update',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        Comment::query()->create([
            'task_id' => $task->id,
            'author_id' => $creator->id,
            'body' => 'Second update',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($creator)
            ->get(route('projects.tasks.show', [$task->project, $task]));

        $response
            ->assertOk()
            ->assertSeeInOrder([
                'First update',
                'Second update',
            ]);
    }

    private function postComment(User $user, Task $task, string $body): TestResponse
    {
        return $this
            ->actingAs($user)
            ->from(route('projects.tasks.show', [$task->project, $task]))
            ->withSession(['_token' => 'comment-token'])
            ->post(route('projects.tasks.comments.store', [$task->project, $task]), [
                '_token' => 'comment-token',
                'body' => $body,
            ]);
    }
}
