<?php

declare(strict_types=1);

namespace App\Application\Dashboard;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

final class GetDashboardData
{
    /** @return array{my_tasks: Collection<int, Task>, upcoming: Collection<int, Task>, projects: Collection<int, Project>} */
    public function execute(int $userId): array
    {
        $myTasks = Task::where('assignee_id', $userId)
            ->whereIn('status', ['todo', 'in_progress'])
            ->with('project')
            ->latest()
            ->limit(10)
            ->get();

        $upcoming = Task::whereNotNull('due_date')
            ->whereIn('status', ['todo', 'in_progress'])
            ->whereHas('project', fn ($q) => $q->whereHas('members', fn ($q2) => $q2->where('user_id', $userId)))
            ->with('project')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $projects = Project::whereHas('members', fn ($q) => $q->where('user_id', $userId))->get();

        return [
            'my_tasks' => $myTasks,
            'upcoming' => $upcoming,
            'projects' => $projects,
        ];
    }
}
