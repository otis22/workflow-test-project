<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Model\CommentModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a valid user via factory', function (): void {
    $user = UserModel::factory()->create();

    expect($user->id)->toBeGreaterThan(0)
        ->and($user->name)->toBeString()
        ->and($user->email)->toBeString()
        ->and($user->password_hash)->toBeString();
});

it('creates multiple users via factory with unique emails', function (): void {
    $users = UserModel::factory()->count(5)->create();

    $emails = $users->pluck('email')->all();
    expect($emails)->toHaveCount(5)
        ->and(array_unique($emails))->toHaveCount(5);
});

it('creates a valid project via factory with auto-created owner', function (): void {
    $project = ProjectModel::factory()->create();

    expect($project->id)->toBeGreaterThan(0)
        ->and($project->owner_id)->toBeGreaterThan(0)
        ->and(UserModel::query()->find($project->owner_id))->not->toBeNull();
});

it('creates a valid project member via factory', function (): void {
    $member = ProjectMemberModel::factory()->create();

    expect($member->id)->toBeGreaterThan(0)
        ->and(ProjectModel::query()->find($member->project_id))->not->toBeNull()
        ->and(UserModel::query()->find($member->user_id))->not->toBeNull();
});

it('creates a valid task via factory with auto-created project and creator', function (): void {
    $task = TaskModel::factory()->create();

    expect($task->id)->toBeGreaterThan(0)
        ->and(ProjectModel::query()->find($task->project_id))->not->toBeNull()
        ->and(UserModel::query()->find($task->creator_id))->not->toBeNull()
        ->and(in_array($task->status, ['todo', 'in_progress', 'done'], true))->toBeTrue()
        ->and(in_array($task->priority, ['low', 'medium', 'high'], true))->toBeTrue();
});

it('creates a valid comment via factory with auto-created task and author', function (): void {
    $comment = CommentModel::factory()->create();

    expect($comment->id)->toBeGreaterThan(0)
        ->and(TaskModel::query()->find($comment->task_id))->not->toBeNull()
        ->and(UserModel::query()->find($comment->author_id))->not->toBeNull()
        ->and($comment->body)->toBeString();
});

it('runs the DatabaseSeeder and populates expected users, project, members, tasks and comments', function (): void {
    $this->seed();

    expect(UserModel::query()->count())->toBe(3)
        ->and(ProjectModel::query()->where('name', 'TaskFlow MVP')->exists())->toBeTrue()
        ->and(ProjectMemberModel::query()->count())->toBe(2)
        ->and(TaskModel::query()->count())->toBe(3)
        ->and(CommentModel::query()->count())->toBe(2);
});

it('DatabaseSeeder is idempotent', function (): void {
    $this->seed();
    $this->seed();

    expect(UserModel::query()->count())->toBe(3)
        ->and(ProjectModel::query()->count())->toBe(1)
        ->and(ProjectMemberModel::query()->count())->toBe(2)
        ->and(TaskModel::query()->count())->toBe(3)
        ->and(CommentModel::query()->count())->toBe(2);
});
