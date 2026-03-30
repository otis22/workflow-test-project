<?php

namespace App\Http\Controllers;

use App\Application\Projects\CreateProject;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
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

        $statusFilter = $request->string('status')->toString();
        $deadlineFilter = $request->string('deadline')->toString();
        $allowedStatuses = Task::STATUSES;
        $allowedDeadlines = ['overdue', 'today', 'upcoming', 'none'];

        $tasks = $project->tasks()
            ->with('assignee')
            ->when(
                in_array($statusFilter, $allowedStatuses, true),
                fn (Builder $query): Builder => $query->where('status', $statusFilter),
            )
            ->when(
                in_array($deadlineFilter, $allowedDeadlines, true),
                fn (Builder $query): Builder => $this->applyDeadlineFilter($query, $deadlineFilter),
            )
            ->latest()
            ->get();

        return view('projects.show', [
            'activeDeadlineFilter' => in_array($deadlineFilter, $allowedDeadlines, true) ? $deadlineFilter : '',
            'activeStatusFilter' => in_array($statusFilter, $allowedStatuses, true) ? $statusFilter : '',
            'deadlineFilters' => $allowedDeadlines,
            'project' => $project->load(['owner', 'members']),
            'tasks' => $tasks,
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

    private function applyDeadlineFilter(Builder $query, string $deadlineFilter): Builder
    {
        return match ($deadlineFilter) {
            'overdue' => $query->whereDate('due_date', '<', now()->toDateString()),
            'today' => $query->whereDate('due_date', now()->toDateString()),
            'upcoming' => $query->whereDate('due_date', '>', now()->toDateString()),
            'none' => $query->whereNull('due_date'),
            default => $query,
        };
    }
}
