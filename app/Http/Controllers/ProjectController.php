<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Project\CreateProject;
use App\Application\Project\ListUserProjects;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProjectController extends Controller
{
    public function index(Request $request, ListUserProjects $listProjects): View
    {
        $projects = $listProjects->execute((int) $request->user()->id);

        return view('projects.index', ['projects' => $projects]);
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function show(Project $project): View
    {
        return view('projects.show', ['project' => $project]);
    }

    public function store(Request $request, CreateProject $createProject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $createProject->execute(
            (int) $request->user()->id,
            $validated['name'],
            $validated['description'] ?? '',
        );

        return redirect()->route('projects.index')->with('success', 'Project created.');
    }
}
