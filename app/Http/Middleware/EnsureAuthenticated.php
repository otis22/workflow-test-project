<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Application\Auth\SessionGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureAuthenticated
{
    public function __construct(private SessionGuard $guard) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->guard->currentUserId() === null) {
            // redirect()->guest() stores the intended URL in the session so
            // LoginController can later bounce the user back here.
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
