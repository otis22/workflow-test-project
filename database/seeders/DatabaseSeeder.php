<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Model\CommentModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a deterministic dataset
     * for local development: 3 users (Alice, Bob, Charlie as outsider),
     * 1 project owned by Alice with Alice+Bob as members, 3 tasks across
     * statuses and priorities, 2 comments on the first task.
     *
     * Idempotent: re-running produces the same state via updateOrCreate
     * on natural keys.
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

        $writeSpec = TaskModel::query()->updateOrCreate(
            ['project_id' => $project->id, 'title' => 'Write product spec'],
            [
                'creator_id' => $alice->id,
                'assignee_id' => $alice->id,
                'description' => 'Draft the initial MVP specification',
                'status' => 'done',
                'priority' => 'high',
                'due_date' => null,
            ],
        );
        TaskModel::query()->updateOrCreate(
            ['project_id' => $project->id, 'title' => 'Set up CI pipeline'],
            [
                'creator_id' => $alice->id,
                'assignee_id' => $bob->id,
                'description' => 'Wire GitHub Actions workflow',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => null,
            ],
        );
        TaskModel::query()->updateOrCreate(
            ['project_id' => $project->id, 'title' => 'Prepare launch checklist'],
            [
                'creator_id' => $alice->id,
                'assignee_id' => null,
                'description' => 'Cover smoke tests, readme, release notes',
                'status' => 'todo',
                'priority' => 'low',
                'due_date' => null,
            ],
        );

        CommentModel::query()->updateOrCreate(
            ['task_id' => $writeSpec->id, 'author_id' => $alice->id, 'body' => 'Initial draft ready for review.'],
        );
        CommentModel::query()->updateOrCreate(
            ['task_id' => $writeSpec->id, 'author_id' => $bob->id, 'body' => 'Looks good — a few suggestions inline.'],
        );
    }
}
