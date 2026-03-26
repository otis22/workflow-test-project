<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Comment\AddComment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CommentController extends Controller
{
    public function store(Request $request, Project $project, Task $task, AddComment $addComment): RedirectResponse
    {
        if (! $project->members()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $addComment->execute(
            $task->id,
            (int) $request->user()->id,
            $validated['body'],
            $project,
        );

        return redirect()->route('tasks.show', [$project, $task])->with('success', 'Comment added.');
    }
}
