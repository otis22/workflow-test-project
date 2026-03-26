<?php

declare(strict_types=1);

namespace App\Application\Auth;

use Illuminate\Support\Facades\Auth;

final class LogoutUser
{
    public function execute(): void
    {
        Auth::logout();
    }
}
