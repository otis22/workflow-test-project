<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a deterministic dataset
     * for local development. Additional models are added by subtasks
     * 3.3.2 (projects/members) and 3.3.3 (tasks/comments).
     */
    public function run(): void
    {
        UserModel::query()->updateOrCreate(
            ['email' => 'alice@example.com'],
            ['name' => 'Alice', 'password_hash' => 'hash:secret'],
        );
        UserModel::query()->updateOrCreate(
            ['email' => 'bob@example.com'],
            ['name' => 'Bob', 'password_hash' => 'hash:secret'],
        );
        UserModel::query()->updateOrCreate(
            ['email' => 'charlie@example.com'],
            ['name' => 'Charlie', 'password_hash' => 'hash:secret'],
        );
    }
}
