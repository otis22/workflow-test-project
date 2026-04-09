<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Logout;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class LogoutController extends Controller
{
    public function store(Logout $useCase): RedirectResponse
    {
        // Idempotent: calling logout on an already-guest session is a no-op
        // in LaravelSessionGuard and still returns the redirect below.
        $useCase->execute();

        return redirect('/');
    }
}
