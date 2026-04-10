<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\SessionGuard;
use App\Application\Project\Exception\ProjectNotFoundException;
use App\Application\Project\ShowProject;
use App\Application\Task\AddComment;
use App\Application\Task\CreateTask;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\Exception\TaskNotFoundException;
use App\Application\Task\ListTaskComments;
use App\Domain\Project\ProjectRepository;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepository;
use App\Http\Requests\CreateTaskRequest;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

final class TasksController extends Controller
{
    public function create(
        int $project,
        SessionGuard $session,
        ShowProject $showProject,
    ): View {
        $actorId = $this->requireActor($session);

        try {
            $projectEntity = $showProject->execute($actorId, $project);
        } catch (ProjectNotFoundException|NotAProjectMemberException) {
            abort(404);
        }

        return view('tasks.create', [
            'project' => $projectEntity,
            'statuses' => Status::cases(),
            'priorities' => Priority::cases(),
        ]);
    }

    public function store(
        int $project,
        CreateTaskRequest $request,
        SessionGuard $session,
        CreateTask $useCase,
    ): RedirectResponse {
        $actorId = $this->requireActor($session);

        $dueDateRaw = $request->input('due_date');
        $dueDate = is_string($dueDateRaw) && $dueDateRaw !== ''
            ? new DueDate(new DateTimeImmutable($dueDateRaw))
            : null;

        try {
            $useCase->execute(
                projectId: $project,
                creatorId: $actorId,
                title: (string) $request->input('title'),
                description: (string) ($request->input('description') ?? ''),
                status: Status::from((string) $request->input('status')),
                priority: Priority::from((string) $request->input('priority')),
                assigneeId: null,
                dueDate: $dueDate,
            );
        } catch (ProjectNotFoundException|NotAProjectMemberException) {
            abort(404);
        }

        return redirect()->route('projects.show', $project);
    }

    public function show(
        int $task,
        SessionGuard $session,
        TaskRepository $tasks,
        ProjectRepository $projects,
        ListTaskComments $listComments,
    ): View {
        $actorId = $this->requireActor($session);

        $taskEntity = $tasks->findById($task);
        if (! $taskEntity instanceof Task) {
            abort(404);
        }

        try {
            $comments = $listComments->execute($actorId, $task);
        } catch (TaskNotFoundException|NotAProjectMemberException) {
            abort(404);
        }

        $project = $projects->findById($taskEntity->projectId);

        return view('tasks.show', [
            'task' => $taskEntity,
            'project' => $project,
            'comments' => $comments,
        ]);
    }

    public function storeComment(
        int $task,
        Request $request,
        SessionGuard $session,
        AddComment $useCase,
    ): RedirectResponse {
        $actorId = $this->requireActor($session);

        $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $useCase->execute($task, $actorId, (string) $request->input('body'));
        } catch (TaskNotFoundException|NotAProjectMemberException) {
            abort(404);
        }

        return redirect()->route('tasks.show', $task);
    }

    private function requireActor(SessionGuard $session): int
    {
        $actorId = $session->currentUserId();
        if ($actorId === null) {
            throw new RuntimeException('TasksController requires an authenticated actor');
        }

        return $actorId;
    }
}
