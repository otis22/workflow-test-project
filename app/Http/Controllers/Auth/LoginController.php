<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Exception\InvalidCredentialsException;
use App\Application\Auth\Login;
use App\Application\Auth\SessionGuard;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class LoginController extends Controller
{
    public function show(SessionGuard $session): View|RedirectResponse
    {
        if ($session->currentUserId() !== null) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request, Login $useCase): RedirectResponse
    {
        try {
            $useCase->execute(
                email: (string) $request->input('email'),
                plainPassword: (string) $request->input('password'),
            );
        } catch (InvalidCredentialsException) {
            // Single generic error attached to the email field — no user enumeration.
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        // Login use case already calls SessionGuard::login() on success.
        return redirect('/');
    }
}
