<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\SessionGuard;
use App\Application\Project\ListUserProjects;
use App\Application\Task\ListUserTasks;
use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use Illuminate\View\View;
use RuntimeException;

final class DashboardController extends Controller
{
    public function index(
        SessionGuard $session,
        ListUserTasks $listTasks,
        ListUserProjects $listProjects,
    ): View {
        $actorId = $session->currentUserId();
        if ($actorId === null) {
            throw new RuntimeException('DashboardController requires an authenticated actor');
        }

        $myTasks = $listTasks->execute($actorId);
        $projects = $listProjects->execute($actorId);

        $upcomingDeadlines = array_filter(
            $myTasks,
            fn (Task $task): bool => $task->dueDate instanceof DueDate,
        );
        usort(
            $upcomingDeadlines,
            fn (Task $first, Task $second): int => $first->dueDate->value <=> $second->dueDate->value,
        );
        $upcomingDeadlines = array_slice($upcomingDeadlines, 0, 5);

        return view('dashboard', [
            'myTasks' => $myTasks,
            'upcomingDeadlines' => $upcomingDeadlines,
            'projects' => $projects,
        ]);
    }
}
