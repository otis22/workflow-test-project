<?php

namespace App\Http\Controllers;

use App\Application\Projects\CreateProject;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = $request->user()
            ->projects()
            ->with('owner')
            ->orderBy('name')
            ->get();

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function show(Request $request, Project $project): View
    {
        abort_unless($project->hasMember($request->user()), 403);

        return view('projects.show', [
            'project' => $project->load(['owner', 'members', 'tasks.assignee']),
        ]);
    }

    public function store(StoreProjectRequest $request, CreateProject $createProject): RedirectResponse
    {
        $project = $createProject(
            owner: $request->user(),
            name: $request->validated('name'),
            description: $request->validated('description'),
        );

        return to_route('projects.index', status: Response::HTTP_FOUND)
            ->with('status', "Project \"{$project->name}\" created.");
    }
}
