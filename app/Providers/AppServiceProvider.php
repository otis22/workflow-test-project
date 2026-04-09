<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Auth\SessionGuard;
use App\Application\Clock\Clock;
use App\Application\Hashing\PasswordHasher;
use App\Infrastructure\Auth\LaravelSessionGuard;
use App\Infrastructure\Clock\SystemClock;
use App\Infrastructure\Hashing\LaravelPasswordHasher;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->bind(Clock::class, SystemClock::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(SessionGuard::class, LaravelSessionGuard::class);
    }

    public function boot(): void
    {
        // Custom Blade auth directives that read our SessionGuard port
        // instead of Laravel's default Auth facade. Laravel's @auth/@guest
        // use the Auth facade which is NOT wired to our custom guard.
        Blade::if('signedIn', fn (): bool => app(SessionGuard::class)->currentUserId() !== null);
        Blade::if('signedOut', fn (): bool => app(SessionGuard::class)->currentUserId() === null);
    }
}
