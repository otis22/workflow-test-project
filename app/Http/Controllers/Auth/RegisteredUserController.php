<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request, RegisterUser $registerUser): RedirectResponse
    {
        $user = $registerUser(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        event(new Registered($user));

        $request->authenticate($user);

        return to_route('dashboard', status: Response::HTTP_FOUND);
    }
}
