<?php

namespace Tests\Unit\Application\Comments;

use App\Application\Comments\AddComment;
use App\Application\Comments\Exceptions\InvalidCommentAuthor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_a_comment_for_a_project_member(): void
    {
        ['task' => $task, 'creator' => $creator] = $this->createExistingProjectTask(false);

        $comment = app(AddComment::class)(
            task: $task,
            author: $creator,
            body: 'Kickoff notes are ready for review.',
        );

        $this->assertSame($task->id, $comment->task_id);
        $this->assertSame($creator->id, $comment->author_id);
        $this->assertSame('Kickoff notes are ready for review.', $comment->body);
    }

    public function test_it_rejects_a_non_member_comment_author(): void
    {
        ['task' => $task] = $this->createExistingProjectTask(false);

        $this->expectException(InvalidCommentAuthor::class);

        app(AddComment::class)(
            task: $task,
            author: User::factory()->create(),
            body: 'I should not be able to post here.',
        );
    }
}
