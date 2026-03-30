<?php

namespace App\Http\Controllers;

use App\Application\Comments\AddComment;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function store(
        StoreCommentRequest $request,
        Project $project,
        Task $task,
        AddComment $addComment,
    ): RedirectResponse {
        abort_unless($project->hasMember($request->user()), Response::HTTP_FORBIDDEN);

        $addComment(
            task: $task,
            author: $request->user(),
            body: $request->validated('body'),
        );

        return to_route('projects.tasks.show', [$project, $task], status: Response::HTTP_FOUND)
            ->with('status', 'Comment added.');
    }
}
