<?php

namespace App\Http\Controllers;

use App\Application\Tasks\CreateTask;
use App\Application\Tasks\TaskData;
use App\Application\Tasks\UpdateTask;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\TaskDataRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function create(Request $request, Project $project): View
    {
        $this->ensureProjectMember($request, $project);

        return view('tasks.form', [
            'formAction' => route('projects.tasks.store', $project),
            'formMethod' => 'POST',
            'heading' => 'Create a task',
            'submitLabel' => 'Create task',
            'task' => new Task([
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_MEDIUM,
            ]),
            'project' => $project->load('members'),
        ]);
    }

    public function store(
        StoreTaskRequest $request,
        Project $project,
        CreateTask $createTask,
    ): RedirectResponse {
        $this->ensureProjectMember($request, $project);

        $task = $createTask(
            project: $project,
            creator: $request->user(),
            data: $this->buildTaskData($request),
        );

        return $this->redirectToProject($project, "Task \"{$task->title}\" created.");
    }

    public function edit(Request $request, Project $project, Task $task): View
    {
        $this->ensureProjectMember($request, $project);

        return view('tasks.form', [
            'formAction' => route('projects.tasks.update', [$project, $task]),
            'formMethod' => 'PATCH',
            'heading' => 'Edit task',
            'submitLabel' => 'Save changes',
            'task' => $task,
            'project' => $project->load('members'),
        ]);
    }

    public function update(
        UpdateTaskRequest $request,
        Project $project,
        Task $task,
        UpdateTask $updateTask,
    ): RedirectResponse {
        $this->ensureProjectMember($request, $project);

        $task = $updateTask(
            task: $task,
            actor: $request->user(),
            data: $this->buildTaskData($request),
        );

        return $this->redirectToProject($project, "Task \"{$task->title}\" updated.");
    }

    private function ensureProjectMember(Request $request, Project $project): void
    {
        abort_unless($project->hasMember($request->user()), Response::HTTP_FORBIDDEN);
    }

    private function buildTaskData(TaskDataRequest $request): TaskData
    {
        $validated = $request->validated();

        return new TaskData(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            status: $validated['status'],
            priority: $validated['priority'],
            dueDate: $validated['due_date'] ?? null,
            assignee: isset($validated['assignee_id'])
                ? User::query()->findOrFail($validated['assignee_id'])
                : null,
        );
    }

    private function redirectToProject(Project $project, string $message): RedirectResponse
    {
        return to_route('projects.show', $project, status: Response::HTTP_FOUND)
            ->with('status', $message);
    }
}
