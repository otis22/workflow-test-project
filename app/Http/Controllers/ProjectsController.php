<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\Project\Exception\ProjectNotFoundException;
use App\Application\Project\ListUserProjects;
use App\Application\Project\ShowProject;
use App\Application\Task\Exception\NotAProjectMemberException;
use App\Application\Task\ListProjectTasks;
use App\Domain\Task\Status;
use App\Http\Requests\CreateProjectRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

final class ProjectsController extends Controller
{
    public function index(SessionGuard $session, ListUserProjects $useCase): View
    {
        $actorId = $this->requireActor($session);
        $projects = $useCase->execute($actorId);

        return view('projects.index', ['projects' => $projects]);
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(
        CreateProjectRequest $request,
        SessionGuard $session,
        CreateProject $useCase,
    ): RedirectResponse {
        $actorId = $this->requireActor($session);

        $useCase->execute(
            ownerId: $actorId,
            name: (string) $request->input('name'),
            description: (string) ($request->input('description') ?? ''),
        );

        return redirect()->route('projects.index');
    }

    public function show(
        int $project,
        Request $request,
        SessionGuard $session,
        ShowProject $showProject,
        ListProjectTasks $listTasks,
    ): View {
        $actorId = $this->requireActor($session);

        // Both "project not found" and "actor not a member" map to 404 at the
        // web layer to avoid the 404-vs-403 information disclosure leaked by
        // the underlying use cases (documented as review debt 2.r3).
        try {
            $projectEntity = $showProject->execute($actorId, $project);
            $tasks = $listTasks->execute($actorId, $project, $this->parseStatus($request));
        } catch (ProjectNotFoundException|NotAProjectMemberException) {
            abort(404);
        }

        return view('projects.show', [
            'project' => $projectEntity,
            'tasks' => $tasks,
            'activeStatus' => $this->parseStatus($request),
        ]);
    }

    private function parseStatus(Request $request): ?Status
    {
        $raw = $request->query('status');
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        return Status::tryFrom($raw);
    }

    private function requireActor(SessionGuard $session): int
    {
        $actorId = $session->currentUserId();
        if ($actorId === null) {
            // Middleware auth.session should have already redirected guests.
            // Defensive guard so a controller wired without middleware still
            // fails loudly instead of querying with a null id.
            throw new RuntimeException('ProjectsController requires an authenticated actor');
        }

        return $actorId;
    }
}
