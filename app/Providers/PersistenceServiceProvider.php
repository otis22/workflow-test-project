<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

final class PersistenceServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        // Domain repository → Eloquent implementation bindings are added
        // incrementally in subtasks 3.2.2–3.2.5.
    }
}
