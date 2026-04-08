<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates all five domain tables', function (): void {
    expect(Schema::hasTable('users'))->toBeTrue()
        ->and(Schema::hasTable('projects'))->toBeTrue()
        ->and(Schema::hasTable('project_members'))->toBeTrue()
        ->and(Schema::hasTable('tasks'))->toBeTrue()
        ->and(Schema::hasTable('comments'))->toBeTrue();
});

it('users table has domain-aligned columns only', function (): void {
    expect(Schema::hasColumns('users', ['id', 'name', 'email', 'password_hash', 'created_at', 'updated_at']))->toBeTrue()
        ->and(Schema::hasColumn('users', 'email_verified_at'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'remember_token'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'password'))->toBeFalse();
});

it('drops the password_reset_tokens table (not in MVP)', function (): void {
    expect(Schema::hasTable('password_reset_tokens'))->toBeFalse();
});

it('projects table has expected columns', function (): void {
    expect(Schema::hasColumns('projects', ['id', 'owner_id', 'name', 'description', 'created_at', 'updated_at']))->toBeTrue();
});

it('project_members table has expected columns', function (): void {
    expect(Schema::hasColumns('project_members', ['id', 'project_id', 'user_id', 'created_at']))->toBeTrue();
});

it('tasks table has expected columns', function (): void {
    expect(Schema::hasColumns('tasks', [
        'id', 'project_id', 'creator_id', 'assignee_id',
        'title', 'description', 'status', 'priority', 'due_date',
        'created_at', 'updated_at',
    ]))->toBeTrue();
});

it('comments table has expected columns and no updated_at', function (): void {
    expect(Schema::hasColumns('comments', ['id', 'task_id', 'author_id', 'body', 'created_at']))->toBeTrue()
        ->and(Schema::hasColumn('comments', 'updated_at'))->toBeFalse();
});

it('enforces unique email on users', function (): void {
    DB::table('users')->insert([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'name' => 'Alice2',
        'email' => 'a@example.com',
        'password_hash' => 'hash2',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('enforces unique (project_id, user_id) on project_members', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $projectId = DB::table('projects')->insertGetId([
        'owner_id' => $userId,
        'name' => 'P',
        'description' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_members')->insert([
        'project_id' => $projectId,
        'user_id' => $userId,
        'created_at' => now(),
    ]);
    DB::table('project_members')->insert([
        'project_id' => $projectId,
        'user_id' => $userId,
        'created_at' => now(),
    ]);
})->throws(QueryException::class);

it('rejects task insert with unknown project_id (foreign key enforced)', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tasks')->insert([
        'project_id' => 999,
        'creator_id' => $userId,
        'assignee_id' => null,
        'title' => 'T',
        'description' => '',
        'status' => 'todo',
        'priority' => 'low',
        'due_date' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('cascades deletion from projects to project_members and tasks and comments', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $projectId = DB::table('projects')->insertGetId([
        'owner_id' => $userId,
        'name' => 'P',
        'description' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_members')->insert([
        'project_id' => $projectId,
        'user_id' => $userId,
        'created_at' => now(),
    ]);
    $taskId = DB::table('tasks')->insertGetId([
        'project_id' => $projectId,
        'creator_id' => $userId,
        'assignee_id' => null,
        'title' => 'T',
        'description' => '',
        'status' => 'todo',
        'priority' => 'low',
        'due_date' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('comments')->insert([
        'task_id' => $taskId,
        'author_id' => $userId,
        'body' => 'hello',
        'created_at' => now(),
    ]);

    DB::table('projects')->where('id', $projectId)->delete();

    expect(DB::table('project_members')->where('project_id', $projectId)->count())->toBe(0)
        ->and(DB::table('tasks')->where('project_id', $projectId)->count())->toBe(0)
        ->and(DB::table('comments')->where('task_id', $taskId)->count())->toBe(0);
});

it('sets tasks.assignee_id to null when the assigned user is deleted', function (): void {
    $creatorId = DB::table('users')->insertGetId([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $assigneeId = DB::table('users')->insertGetId([
        'name' => 'Bob',
        'email' => 'b@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $projectId = DB::table('projects')->insertGetId([
        'owner_id' => $creatorId,
        'name' => 'P',
        'description' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_members')->insert([
        'project_id' => $projectId,
        'user_id' => $assigneeId,
        'created_at' => now(),
    ]);
    $taskId = DB::table('tasks')->insertGetId([
        'project_id' => $projectId,
        'creator_id' => $creatorId,
        'assignee_id' => $assigneeId,
        'title' => 'T',
        'description' => '',
        'status' => 'todo',
        'priority' => 'low',
        'due_date' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Remove project_members first (cascade) to avoid RESTRICT on user delete via other FKs.
    DB::table('project_members')->where('user_id', $assigneeId)->delete();
    DB::table('users')->where('id', $assigneeId)->delete();

    $task = DB::table('tasks')->where('id', $taskId)->first();
    expect($task->assignee_id)->toBeNull();
});

it('rejects task insert with invalid status', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $projectId = DB::table('projects')->insertGetId([
        'owner_id' => $userId,
        'name' => 'P',
        'description' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tasks')->insert([
        'project_id' => $projectId,
        'creator_id' => $userId,
        'assignee_id' => null,
        'title' => 'T',
        'description' => '',
        'status' => 'bogus',
        'priority' => 'low',
        'due_date' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('rejects task insert with invalid priority', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $projectId = DB::table('projects')->insertGetId([
        'owner_id' => $userId,
        'name' => 'P',
        'description' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tasks')->insert([
        'project_id' => $projectId,
        'creator_id' => $userId,
        'assignee_id' => null,
        'title' => 'T',
        'description' => '',
        'status' => 'todo',
        'priority' => 'urgent',
        'due_date' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('prevents deleting a user who owns a project (RESTRICT)', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('projects')->insert([
        'owner_id' => $userId,
        'name' => 'P',
        'description' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->where('id', $userId)->delete();
})->throws(QueryException::class);
