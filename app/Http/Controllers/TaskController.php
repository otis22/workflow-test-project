<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Task\CreateTask;
use App\Application\Task\UpdateTask;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TaskController extends Controller
{
    public function create(Request $request, Project $project): View
    {
        $this->authorizeProjectMember($request, $project);

        $members = $project->members()->get();

        return view('tasks.create', [
            'project' => $project,
            'members' => $members,
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function store(Request $request, Project $project, CreateTask $createTask): RedirectResponse
    {
        $this->authorizeProjectMember($request, $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $task = $createTask->execute(
            $project->id,
            (int) $request->user()->id,
            $validated['title'],
            $validated['description'] ?? '',
            $validated['priority'],
        );

        if (! empty($validated['assignee_id'])) {
            $task->update(['assignee_id' => $validated['assignee_id']]);
        }

        if (! empty($validated['due_date'])) {
            $task->update(['due_date' => $validated['due_date']]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Task created.');
    }

    public function show(Request $request, Project $project, Task $task): View
    {
        $this->authorizeProjectMember($request, $project);

        $task->load(['creator', 'assignee', 'comments.author']);

        return view('tasks.show', ['project' => $project, 'task' => $task]);
    }

    public function edit(Request $request, Project $project, Task $task): View
    {
        $this->authorizeProjectMember($request, $project);

        $members = $project->members()->get();

        return view('tasks.edit', [
            'project' => $project,
            'task' => $task,
            'members' => $members,
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function update(Request $request, Project $project, Task $task, UpdateTask $updateTask): RedirectResponse
    {
        $this->authorizeProjectMember($request, $project);

        $memberIds = $project->members()->pluck('users.id')->toArray();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', 'in:todo,in_progress,done'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'assignee_id' => ['nullable', 'in:'.implode(',', $memberIds)],
            'due_date' => ['nullable', 'date'],
        ]);

        $updateTask->execute($task, $validated);

        return redirect()->route('tasks.show', [$project, $task])->with('success', 'Task updated.');
    }

    private function authorizeProjectMember(Request $request, Project $project): void
    {
        if (! $project->members()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }
    }
}
