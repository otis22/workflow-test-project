<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Project\ProjectRepository;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProjectRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

final class PersistenceServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(ProjectRepository::class, EloquentProjectRepository::class);
    }
}
