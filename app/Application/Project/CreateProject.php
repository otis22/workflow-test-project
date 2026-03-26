<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Models\Project;

final class CreateProject
{
    public function execute(int $ownerId, string $name, string $description = ''): Project
    {
        $project = Project::create([
            'owner_id' => $ownerId,
            'name' => $name,
            'description' => $description,
        ]);

        $project->members()->attach($ownerId);

        return $project;
    }
}
