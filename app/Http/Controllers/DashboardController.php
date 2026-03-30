<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $assignedTasks = $user->assignedTasks()
            ->with('project')
            ->where('status', '!=', Task::STATUS_DONE)
            ->orderByRaw('case when due_date is null then 1 else 0 end')
            ->orderBy('due_date')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $nearTermDeadlines = $user->assignedTasks()
            ->with(['project', 'assignee'])
            ->where('status', '!=', Task::STATUS_DONE)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now()->toDateString())
            ->whereDate('due_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('due_date')
            ->orderBy('title')
            ->limit(5)
            ->get();

        $projects = $user->projects()
            ->withCount([
                'tasks as open_tasks_count' => fn (Builder $query): Builder => $query->where('status', '!=', Task::STATUS_DONE),
            ])
            ->orderBy('name')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'assignedTasks' => $assignedTasks,
            'nearTermDeadlines' => $nearTermDeadlines,
            'projects' => $projects,
        ]);
    }
}
