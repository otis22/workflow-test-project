<?php

declare(strict_types=1);

namespace App\Application\Auth;

interface SessionGuard
{
    public function login(int $userId): void;

    public function logout(): void;

    public function currentUserId(): ?int;
}
