<?php

namespace App\Application\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProject
{
    public function __invoke(User $owner, string $name, ?string $description = null): Project
    {
        return DB::transaction(function () use ($owner, $name, $description): Project {
            $project = Project::query()->create([
                'owner_id' => $owner->id,
                'name' => $name,
                'description' => $description,
            ]);

            $project->memberLinks()->create([
                'user_id' => $owner->id,
            ]);

            return $project;
        });
    }
}
