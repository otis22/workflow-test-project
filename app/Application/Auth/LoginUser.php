<?php

declare(strict_types=1);

namespace App\Application\Auth;

use Illuminate\Support\Facades\Auth;

final class LoginUser
{
    public function execute(string $email, string $password): bool
    {
        return Auth::attempt([
            'email' => $email,
            'password' => $password,
        ]);
    }
}
