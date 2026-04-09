<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Model\CommentModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists and retrieves a user row via UserModel', function (): void {
    $user = UserModel::query()->create([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
    ]);

    $loaded = UserModel::query()->find($user->id);
    expect($loaded)->not->toBeNull()
        ->and($loaded->email)->toBe('a@example.com')
        ->and($loaded->password_hash)->toBe('hash')
        ->and($loaded->created_at)->not->toBeNull()
        ->and($loaded->updated_at)->not->toBeNull();
});

it('persists and retrieves a project row via ProjectModel', function (): void {
    $user = UserModel::query()->create([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
    ]);
    $project = ProjectModel::query()->create([
        'owner_id' => $user->id,
        'name' => 'TaskFlow',
        'description' => 'desc',
    ]);

    $loaded = ProjectModel::query()->find($project->id);
    expect($loaded->owner_id)->toBe($user->id)
        ->and($loaded->name)->toBe('TaskFlow')
        ->and($loaded->description)->toBe('desc');
});

it('persists and retrieves a project_member row via ProjectMemberModel', function (): void {
    $user = UserModel::query()->create([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
    ]);
    $project = ProjectModel::query()->create([
        'owner_id' => $user->id,
        'name' => 'P',
        'description' => '',
    ]);
    $member = ProjectMemberModel::query()->create([
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    $loaded = ProjectMemberModel::query()->find($member->id);
    expect($loaded->project_id)->toBe($project->id)
        ->and($loaded->user_id)->toBe($user->id)
        ->and($loaded->created_at)->not->toBeNull();
});

it('persists and retrieves a task row via TaskModel with nullable assignee and due date', function (): void {
    $user = UserModel::query()->create([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
    ]);
    $project = ProjectModel::query()->create([
        'owner_id' => $user->id,
        'name' => 'P',
        'description' => '',
    ]);
    $task = TaskModel::query()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'assignee_id' => null,
        'title' => 'T',
        'description' => '',
        'status' => 'todo',
        'priority' => 'low',
        'due_date' => null,
    ]);

    $loaded = TaskModel::query()->find($task->id);
    expect($loaded->status)->toBe('todo')
        ->and($loaded->priority)->toBe('low')
        ->and($loaded->assignee_id)->toBeNull()
        ->and($loaded->due_date)->toBeNull();
});

it('persists and retrieves a comment row via CommentModel (no updated_at)', function (): void {
    $user = UserModel::query()->create([
        'name' => 'Alice',
        'email' => 'a@example.com',
        'password_hash' => 'hash',
    ]);
    $project = ProjectModel::query()->create([
        'owner_id' => $user->id,
        'name' => 'P',
        'description' => '',
    ]);
    $task = TaskModel::query()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'assignee_id' => null,
        'title' => 'T',
        'description' => '',
        'status' => 'todo',
        'priority' => 'low',
        'due_date' => null,
    ]);
    $comment = CommentModel::query()->create([
        'task_id' => $task->id,
        'author_id' => $user->id,
        'body' => 'hello',
    ]);

    $loaded = CommentModel::query()->find($comment->id);
    expect($loaded->body)->toBe('hello')
        ->and($loaded->created_at)->not->toBeNull();
});
