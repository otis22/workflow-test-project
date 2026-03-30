<?php

namespace App\Http\Controllers;

use App\Application\Tasks\CreateTask;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function create(Request $request, Project $project): View
    {
        abort_unless($project->hasMember($request->user()), Response::HTTP_FORBIDDEN);

        return view('tasks.create', [
            'project' => $project->load('members'),
        ]);
    }

    public function store(
        StoreTaskRequest $request,
        Project $project,
        CreateTask $createTask,
    ): RedirectResponse {
        abort_unless($project->hasMember($request->user()), Response::HTTP_FORBIDDEN);

        $validated = $request->validated();
        $assignee = isset($validated['assignee_id'])
            ? User::query()->findOrFail($validated['assignee_id'])
            : null;

        $task = $createTask(
            project: $project,
            creator: $request->user(),
            title: $validated['title'],
            description: $validated['description'] ?? null,
            status: $validated['status'],
            priority: $validated['priority'],
            dueDate: $validated['due_date'] ?? null,
            assignee: $assignee,
        );

        return to_route('projects.show', $project, status: Response::HTTP_FOUND)
            ->with('status', "Task \"{$task->title}\" created.");
    }
}
