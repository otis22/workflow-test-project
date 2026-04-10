<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\SessionGuard;
use App\Application\Project\Exception\ProjectNotFoundException;
use App\Application\Project\ShowProject;
use App\Application\Task\CreateTask;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Domain\Task\DueDate;
use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use App\Http\Requests\CreateTaskRequest;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
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

    private function requireActor(SessionGuard $session): int
    {
        $actorId = $session->currentUserId();
        if ($actorId === null) {
            throw new RuntimeException('TasksController requires an authenticated actor');
        }

        return $actorId;
    }
}
