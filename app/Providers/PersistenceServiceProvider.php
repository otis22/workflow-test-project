<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Project\ProjectMemberRepository;
use App\Domain\Project\ProjectRepository;
use App\Domain\Task\CommentRepository;
use App\Domain\Task\TaskRepository;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCommentRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProjectMemberRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProjectRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentTaskRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

final class PersistenceServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(ProjectRepository::class, EloquentProjectRepository::class);
        $this->app->bind(ProjectMemberRepository::class, EloquentProjectMemberRepository::class);
        $this->app->bind(TaskRepository::class, EloquentTaskRepository::class);
        $this->app->bind(CommentRepository::class, EloquentCommentRepository::class);
    }
}
