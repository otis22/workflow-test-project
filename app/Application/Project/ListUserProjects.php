<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

final class ListUserProjects
{
    /** @return Collection<int, Project> */
    public function execute(int $userId): Collection
    {
        return Project::whereHas('members', fn ($q) => $q->where('user_id', $userId))->get();
    }
}
