<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\SessionGuard;
use App\Application\Project\CreateProject;
use App\Application\Project\ListUserProjects;
use App\Http\Requests\CreateProjectRequest;
use Illuminate\Http\RedirectResponse;
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
