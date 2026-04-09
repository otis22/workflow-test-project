<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
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

it('runs the DatabaseSeeder and populates expected users, project and members', function (): void {
    $this->seed();

    expect(UserModel::query()->count())->toBe(3)
        ->and(ProjectModel::query()->where('name', 'TaskFlow MVP')->exists())->toBeTrue()
        ->and(ProjectMemberModel::query()->count())->toBe(2);
});

it('DatabaseSeeder is idempotent', function (): void {
    $this->seed();
    $this->seed();

    expect(UserModel::query()->count())->toBe(3)
        ->and(ProjectModel::query()->count())->toBe(1)
        ->and(ProjectMemberModel::query()->count())->toBe(2);
});
