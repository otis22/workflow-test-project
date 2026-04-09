<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\SessionGuard;
use App\Application\Project\ListUserProjects;
use Illuminate\View\View;
use RuntimeException;

final class ProjectsController extends Controller
{
    public function index(SessionGuard $session, ListUserProjects $useCase): View
    {
        $actorId = $session->currentUserId();
        if ($actorId === null) {
            // Middleware auth.session should have already redirected guests.
            // Defensive guard so a controller wired without middleware still
            // fails loudly instead of querying with a null id.
            throw new RuntimeException('ProjectsController::index requires an authenticated actor');
        }

        $projects = $useCase->execute($actorId);

        return view('projects.index', ['projects' => $projects]);
    }
}
