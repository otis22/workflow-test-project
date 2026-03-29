<?php

namespace App\Application\Auth;

use App\Models\User;

class RegisterUser
{
    public function __invoke(string $name, string $email, string $password): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }
}
