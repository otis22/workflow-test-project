<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\SessionGuard;
use App\Application\User\Exception\EmailAlreadyTakenException;
use App\Application\User\Exception\WeakPasswordException;
use App\Application\User\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class RegisterController extends Controller
{
    public function show(SessionGuard $session): View|RedirectResponse
    {
        if ($session->currentUserId() !== null) {
            return redirect('/');
        }

        return view('auth.register');
    }

    public function store(
        RegisterRequest $request,
        RegisterUser $useCase,
        SessionGuard $session,
    ): RedirectResponse {
        try {
            $user = $useCase->execute(
                name: (string) $request->input('name'),
                email: (string) $request->input('email'),
                plainPassword: (string) $request->input('password'),
            );
        } catch (EmailAlreadyTakenException) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already registered.',
            ]);
        } catch (WeakPasswordException) {
            throw ValidationException::withMessages([
                'password' => 'Password must be at least '.RegisterUser::MIN_PASSWORD_LENGTH.' characters.',
            ]);
        }

        $session->login($user->id);

        return redirect()->route('dashboard');
    }
}
