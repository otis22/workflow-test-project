<?php

declare(strict_types=1);

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

it('runs the DatabaseSeeder and populates expected users', function (): void {
    $this->seed();

    expect(UserModel::query()->count())->toBe(3)
        ->and(UserModel::query()->where('email', 'alice@example.com')->exists())->toBeTrue()
        ->and(UserModel::query()->where('email', 'bob@example.com')->exists())->toBeTrue()
        ->and(UserModel::query()->where('email', 'charlie@example.com')->exists())->toBeTrue();
});

it('DatabaseSeeder is idempotent', function (): void {
    $this->seed();
    $this->seed();

    expect(UserModel::query()->count())->toBe(3);
});
