<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a deterministic dataset
     * for local development. Task and comment seeds follow in 3.3.3.
     */
    public function run(): void
    {
        $alice = UserModel::query()->updateOrCreate(
            ['email' => 'alice@example.com'],
            ['name' => 'Alice', 'password_hash' => 'hash:secret'],
        );
        $bob = UserModel::query()->updateOrCreate(
            ['email' => 'bob@example.com'],
            ['name' => 'Bob', 'password_hash' => 'hash:secret'],
        );
        UserModel::query()->updateOrCreate(
            ['email' => 'charlie@example.com'],
            ['name' => 'Charlie', 'password_hash' => 'hash:secret'],
        );

        $project = ProjectModel::query()->updateOrCreate(
            ['name' => 'TaskFlow MVP'],
            ['owner_id' => $alice->id, 'description' => 'Seed project for local development'],
        );

        ProjectMemberModel::query()->updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $alice->id],
        );
        ProjectMemberModel::query()->updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $bob->id],
        );
    }
}
